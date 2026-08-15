<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Handles preflight summary and atomic transition of PRIVATE_ACCESS products to LIVE.
 */
final class MakeDropLive {
	/**
	 * Returns preflight summary for Make Drop Live operation.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_preflight_summary( int $drop_term_id, int $now_ts ): array {
		global $wpdb;

		$product_ids = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'statement_drop',
						'field'    => 'term_id',
						'terms'    => $drop_term_id,
					),
				),
			)
		);

		$private_access_products = array();
		$upcoming_products       = array();
		$other_products          = array();

		foreach ( $product_ids as $pid ) {
			$product = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $pid ) : null;
			if ( ! is_object( $product ) ) {
				continue;
			}
			$state = Metadata::get_release_state( $product );
			if ( ReleaseState::PRIVATE_ACCESS === $state ) {
				$private_access_products[] = (int) $pid;
			} elseif ( ReleaseState::UPCOMING === $state ) {
				$upcoming_products[] = (int) $pid;
			} else {
				$other_products[] = (int) $pid;
			}
		}

		$grants_count = 0;
		$sessions_count = 0;
		$pending_reminders_count = 0;

		if ( isset( $wpdb ) ) {
			$grants_table   = $wpdb->prefix . 'statement_access_grants';
			$sessions_table = $wpdb->prefix . 'statement_access_sessions';
			$now_str        = date( 'Y-m-d H:i:s', $now_ts );

			$grants_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$grants_table} WHERE drop_term_id = %d AND revoked_at IS NULL AND grant_expires_at > %s",
					$drop_term_id,
					$now_str
				)
			);

			$sessions_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$sessions_table} WHERE drop_term_id = %d AND revoked_at IS NULL AND session_expires_at > %s",
					$drop_term_id,
					$now_str
				)
			);

			$reminders_table = $wpdb->prefix . 'statement_access_reminders';
			$pending_reminders_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$reminders_table} WHERE drop_term_id = %d AND status = 'pending'",
					$drop_term_id
				)
			);
		}

		return array(
			'drop_term_id'            => $drop_term_id,
			'private_access_products' => $private_access_products,
			'upcoming_products'       => $upcoming_products,
			'other_products'          => $other_products,
			'grants_count'            => $grants_count,
			'sessions_count'          => $sessions_count,
			'pending_reminders_count' => $pending_reminders_count,
		);
	}

	/**
	 * Transitions all PRIVATE_ACCESS products in the drop to LIVE atomically.
	 *
	 * @param int $drop_term_id Drop term ID.
	 * @param int $now_ts       Current timestamp.
	 * @return array Result summary with status and counts.
	 */
	public static function execute_transition( int $drop_term_id, int $now_ts = 0 ): array {
		if ( function_exists( 'current_user_can' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			return array(
				'ok'                 => false,
				'transitioned_count' => 0,
				'message'            => 'Insufficient capability: manage_woocommerce required.',
			);
		}

		if ( $now_ts <= 0 ) {
			$now_ts = time();
		}

		$summary = self::get_preflight_summary( $drop_term_id, $now_ts );
		$private_access_pids = $summary['private_access_products'];

		if ( empty( $private_access_pids ) ) {
			return array(
				'ok'                 => false,
				'transitioned_count' => 0,
				'message'            => 'No PRIVATE_ACCESS products found in this Drop.',
			);
		}

		$transitioned_products = array();
		try {
			foreach ( $private_access_pids as $pid ) {
				$product = wc_get_product( $pid );
				if ( ! $product ) {
					throw new \RuntimeException( "Product {$pid} could not be loaded." );
				}
				Metadata::set_release_state( $product, ReleaseState::LIVE );
				$saved = $product->save();
				if ( false === $saved ) {
					throw new \RuntimeException( "Product {$pid} save failed." );
				}
				$transitioned_products[] = $product;
			}
		} catch ( \Throwable $e ) {
			// Roll back any products transitioned before failure
			foreach ( $transitioned_products as $p ) {
				Metadata::set_release_state( $p, ReleaseState::PRIVATE_ACCESS );
				$p->save();
			}

			return array(
				'ok'                 => false,
				'transitioned_count' => 0,
				'message'            => 'Make Drop Live failed during mutation: ' . $e->getMessage() . ' All changes rolled back.',
			);
		}

		$transitioned = count( $transitioned_products );

		// Invalidate access return tokens & cancel pending reminders for drop ONLY AFTER 100% successful transitions
		global $wpdb;
		if ( isset( $wpdb ) ) {
			$now_str = date( 'Y-m-d H:i:s', $now_ts );
			$grants_table = $wpdb->prefix . 'statement_access_grants';
			$tokens_table = $wpdb->prefix . 'statement_access_tokens';

			// Revoke return tokens for grants in this drop
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$tokens_table} t
					INNER JOIN {$grants_table} g ON t.grant_id = g.id
					SET t.revoked_at = %s
					WHERE g.drop_term_id = %d AND t.revoked_at IS NULL",
					$now_str,
					$drop_term_id
				)
			);

			// Cancel pending reminders for this drop
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$grants_table}
					SET reminder_cancelled_at = %s, reminder_cancel_reason = 'drop_made_live'
					WHERE drop_term_id = %d AND reminder_scheduled_at IS NOT NULL AND reminder_sent_at IS NULL AND reminder_cancelled_at IS NULL",
					$now_str,
					$drop_term_id
				)
			);
		}

		return array(
			'ok'                 => true,
			'transitioned_count' => $transitioned,
			'message'            => "Successfully transitioned {$transitioned} products to LIVE.",
		);
	}
}
