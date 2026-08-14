<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Handles checkout billing email authorization check and attaches audit line item metadata.
 */
final class OrderAudit {
	public const META_GRANT_ID          = '_statement_private_access_grant_id';
	public const META_DROP_ID           = '_statement_private_access_drop_id';
	public const META_AUTHORIZED_AT     = '_statement_private_access_authorized_at';
	public const META_CONTEXT_VERSION   = '_statement_private_access_context_version';

	public static function boot(): void {
		add_action( 'woocommerce_checkout_process', array( self::class, 'validate_checkout_private_access' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( self::class, 'attach_line_item_audit_meta' ), 10, 4 );
	}

	/**
	 * Validates billing email match and multi-Drop same-identity invariant during checkout.
	 */
	public static function validate_checkout_private_access(): void {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$cart = WC()->cart;
		$billing_email = sanitize_email( wp_unslash( $_POST['billing_email'] ?? '' ) );
		$billing_hash  = ! empty( $billing_email ) ? Crypto::hash_email( $billing_email ) : null;
		$now_ts        = time();
		global $wpdb;

		$authorizing_email_hashes = array();

		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'] ?? null;
			if ( ! $product ) {
				continue;
			}

			$owner = Metadata::get_release_owner( $product );
			$state = Metadata::get_release_state( $owner );

			if ( ReleaseState::PRIVATE_ACCESS === $state ) {
				$product_id = $owner->get_id();
				$terms = wp_get_object_terms( $product_id, 'statement_drop' );
				if ( empty( $terms ) || is_wp_error( $terms ) ) {
					wc_add_notice( __( 'A private release item in your cart is no longer available.', 'statement-collector-core' ), 'error' );
					return;
				}

				$drop_id = (int) $terms[0]->term_id;
				$token   = $_COOKIE[ SessionService::get_cookie_name( $drop_id ) ] ?? '';
				$config  = DropConfig::get_config( $drop_id );
				$validation = SessionService::validate_session( $wpdb, $drop_id, $token, $now_ts, $config['closes_at_ts'] ?? 0 );

				if ( null === $validation ) {
					wc_add_notice( __( 'Your session for a private release piece has expired. Please re-authenticate.', 'statement-collector-core' ), 'error' );
					return;
				}

				$grant_email_hash = (string) $validation['grant']['email_hash'];
				$authorizing_email_hashes[ $drop_id ] = $grant_email_hash;

				// Verify checkout billing email matches authorizing grant email_hash
				if ( null === $billing_hash || $billing_hash !== $grant_email_hash ) {
					wc_add_notice( __( 'Your billing email address must match the email authorized for this private release.', 'statement-collector-core' ), 'error' );
					return;
				}
			}
		}

		// Verify multi-drop items resolve to the SAME normalized email identity
		if ( count( array_unique( $authorizing_email_hashes ) ) > 1 ) {
			wc_add_notice( __( 'Items from multiple private releases must belong to the same authorized account. Please place separate orders.', 'statement-collector-core' ), 'error' );
		}
	}

	/**
	 * Attaches minimal audit metadata to order line items for private access purchases.
	 *
	 * @param object $item          WooCommerce order line item object.
	 * @param string $cart_item_key Cart item key.
	 * @param array  $values        Cart item values.
	 * @param object $order         WooCommerce order object.
	 */
	public static function attach_line_item_audit_meta( $item, string $cart_item_key, array $values, $order ): void {
		unset( $cart_item_key, $order );

		$product = $values['data'] ?? null;
		if ( ! $product ) {
			return;
		}

		$owner = Metadata::get_release_owner( $product );
		$state = Metadata::get_release_state( $owner );

		if ( ReleaseState::PRIVATE_ACCESS === $state ) {
			$product_id = $owner->get_id();
			$terms = wp_get_object_terms( $product_id, 'statement_drop' );
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return;
			}

			$drop_id = (int) $terms[0]->term_id;
			$token   = $_COOKIE[ SessionService::get_cookie_name( $drop_id ) ] ?? '';
			$now_ts  = time();
			global $wpdb;

			$config     = DropConfig::get_config( $drop_id );
			$validation = SessionService::validate_session( $wpdb, $drop_id, $token, $now_ts, $config['closes_at_ts'] ?? 0 );

			if ( null !== $validation ) {
				$grant_id = (int) $validation['grant']['id'];
				$item->add_meta_data( self::META_GRANT_ID, $grant_id, true );
				$item->add_meta_data( self::META_DROP_ID, $drop_id, true );
				$item->add_meta_data( self::META_AUTHORIZED_AT, date( 'Y-m-d H:i:s', $now_ts ), true );
				$item->add_meta_data( self::META_CONTEXT_VERSION, '1.0', true );
			}
		}
	}
}
