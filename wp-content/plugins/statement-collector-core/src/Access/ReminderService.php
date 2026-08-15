<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Manages Action Scheduler scheduling, send revalidation, and Add-to-Bag auto-cancellation for marketing reminders.
 */
final class ReminderService {
	public const ACTION_HOOK = 'statement_private_access_reminder_action';

	public static function boot(): void {
		add_action( 'statement_schedule_private_access_reminder', array( self::class, 'schedule_reminder' ), 10, 4 );
		add_action( self::ACTION_HOOK, array( self::class, 'handle_reminder_action' ) );
		add_action( 'statement_private_access_added_to_cart', array( self::class, 'cancel_reminder_on_add_to_bag' ) );
		add_action( 'woocommerce_checkout_order_processed', array( self::class, 'cancel_reminder_on_purchase' ), 10, 3 );
	}

	/**
	 * Schedules single reminder via Action Scheduler.
	 */
	public static function schedule_reminder( int $grant_id, string $email_hash, int $drop_id, int $scheduled_ts ): void {
		global $wpdb;
		$now_ts = time();

		// Ensure max 1 reminder per grant
		if ( isset( $wpdb ) ) {
			$grants_table = $wpdb->prefix . 'statement_access_grants';
			$grant = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$grants_table} WHERE id = %d", $grant_id ),
				ARRAY_A
			);

			if ( ! is_array( $grant ) || ! empty( $grant['reminder_scheduled_at'] ) || ! empty( $grant['revoked_at'] ) ) {
				return;
			}

			// Validate scheduled time < grant_expires_at and < drop_close_at
			$grant_exp_ts = strtotime( (string) $grant['grant_expires_at'] . ' UTC' );
			$config       = DropConfig::get_config( $drop_id );
			$drop_close_ts = $config['closes_at_ts'] ?? 0;

			if ( $scheduled_ts >= $grant_exp_ts || $scheduled_ts >= $drop_close_ts ) {
				return;
			}

			$wpdb->update(
				$grants_table,
				array( 'reminder_scheduled_at' => date( 'Y-m-d H:i:s', $scheduled_ts ) ),
				array( 'id' => $grant_id )
			);
		}

		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( $scheduled_ts, self::ACTION_HOOK, array( 'grant_id' => $grant_id ), 'statement' );
		} elseif ( function_exists( 'wp_schedule_single_event' ) ) {
			wp_schedule_single_event( $scheduled_ts, self::ACTION_HOOK, array( $grant_id ) );
		}
	}

	/**
	 * Callback handler: revalidates all conditions at send time.
	 */
	public static function handle_reminder_action( int $grant_id ): void {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return;
		}

		$now_ts = time();
		$grants_table = $wpdb->prefix . 'statement_access_grants';
		$grant = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$grants_table} WHERE id = %d", $grant_id ),
			ARRAY_A
		);

		if ( ! is_array( $grant ) ) {
			return;
		}

		// Revalidation checks:
		// 1. Grant active & not revoked
		if ( ! empty( $grant['revoked_at'] ) ) {
			return;
		}

		// 2. Grant not expired
		$grant_exp_ts = strtotime( (string) $grant['grant_expires_at'] . ' UTC' );
		if ( $now_ts >= $grant_exp_ts ) {
			return;
		}

		// 3. Drop close not passed
		$drop_id = (int) $grant['drop_term_id'];
		$config = DropConfig::get_config( $drop_id );
		if ( empty( $config['closes_at_ts'] ) || $now_ts >= $config['closes_at_ts'] ) {
			return;
		}

		// 4. Not already sent or cancelled
		if ( ! empty( $grant['reminder_sent_at'] ) || ! empty( $grant['reminder_cancelled_at'] ) ) {
			return;
		}

		// 5. Active marketing consent check
		if ( ! ConsentService::has_active_consent( $wpdb, $grant['email_hash'] ) ) {
			return;
		}

		// 6. Check at least one product in Drop remains PRIVATE_ACCESS
		$drop_products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'statement_drop',
						'field'    => 'term_id',
						'terms'    => $drop_id,
					),
				),
			)
		);

		$has_private = false;
		foreach ( $drop_products as $pid ) {
			$product = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $pid ) : null;
			if ( ! is_object( $product ) ) {
				continue;
			}
			if ( ReleaseState::PRIVATE_ACCESS === Metadata::get_release_state( $product ) ) {
				$has_private = true;
				break;
			}
		}

		if ( ! $has_private ) {
			return;
		}

		// Decrypt email & send
		$decrypted_email = Crypto::decrypt_email( $grant['encrypted_email'] );
		if ( ! $decrypted_email ) {
			return;
		}

		EmailAccessReminder::send_reminder( $grant_id, $decrypted_email, $drop_id );
	}

	/**
	 * Permanently cancels reminder when a qualifying private product is added to cart.
	 */
	public static function cancel_reminder_on_add_to_bag( $product ): void {
		$owner = Metadata::get_release_owner( $product );
		if ( ! $owner || ReleaseState::PRIVATE_ACCESS !== Metadata::get_release_state( $owner ) ) {
			return;
		}

		$product_id = $owner->get_id();
		$terms = wp_get_object_terms( $product_id, 'statement_drop' );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$drop_id = (int) $terms[0]->term_id;
		$token = $_COOKIE[ SessionService::get_cookie_name( $drop_id ) ] ?? '';
		if ( '' === $token ) {
			return;
		}

		global $wpdb;
		$now_ts = time();
		$config = DropConfig::get_config( $drop_id );
		$validation = SessionService::validate_session( $wpdb, $drop_id, $token, $now_ts, $config['closes_at_ts'] ?? 0 );

		if ( null !== $validation ) {
			$grant_id = (int) $validation['grant']['id'];
			$wpdb->update(
				$wpdb->prefix . 'statement_access_grants',
				array(
					'reminder_cancelled_at' => date( 'Y-m-d H:i:s', $now_ts ),
					'reminder_cancel_reason' => 'add_to_cart',
				),
				array( 'id' => $grant_id )
			);
		}
	}

	/**
	 * Permanently cancels pending reminders when an order is created.
	 */
	public static function cancel_reminder_on_purchase( int $order_id, array $posted_data = array(), $order = null ): void {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return;
		}

		$billing_email = '';
		if ( $order && method_exists( $order, 'get_billing_email' ) ) {
			$billing_email = (string) $order->get_billing_email();
		} elseif ( isset( $posted_data['billing_email'] ) ) {
			$billing_email = sanitize_email( wp_unslash( $posted_data['billing_email'] ) );
		}

		$email_hash = Crypto::hash_email( $billing_email );
		if ( ! $email_hash ) {
			return;
		}

		$now_str = date( 'Y-m-d H:i:s' );
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}statement_access_grants
				SET reminder_cancelled_at = %s, reminder_cancel_reason = 'purchased'
				WHERE email_hash = %s AND reminder_scheduled_at IS NOT NULL AND reminder_sent_at IS NULL AND reminder_cancelled_at IS NULL",
				$now_str,
				$email_hash
			)
		);
	}
}
