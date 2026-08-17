<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Release;

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Admin\LifecycleV2Admin;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

/**
 * Privileged domain service for authorized admin lifecycle overrides and audit logging.
 *
 * Normal public transitions remain forward-only via Metadata::set_release_state().
 * This service governs privileged reverse transitions (e.g. SOLD_OUT -> LIVE, ARCHIVED -> LIVE)
 * under strict administrative verification, stock preconditions, and audit guarantees.
 */
final class LifecycleOverrideService {

	/**
	 * Executes a privileged lifecycle state override with precondition validation and audit logging.
	 *
	 * @param object $product WooCommerce product object or variation.
	 * @param string $target_state Desired canonical ReleaseState.
	 * @param int    $actor_id WordPress User ID performing the action.
	 * @param string $reason Mandatory reason / audit note.
	 * @return array<string, mixed> Result payload with 'success' boolean and details/error.
	 */
	public static function override_state( $product, string $target_state, int $actor_id, string $reason ): array {
		if ( ! is_object( $product ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid product object provided.', 'statement-collector-core' ),
			);
		}

		$owner = Metadata::get_release_owner( $product );
		if ( ! is_object( $owner ) || ! method_exists( $owner, 'update_meta_data' ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Unable to resolve canonical release owner for product.', 'statement-collector-core' ),
			);
		}

		if ( ! ReleaseState::is_valid( $target_state ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid target release state specified.', 'statement-collector-core' ),
			);
		}

		$from_state = Metadata::get_release_state( $owner );
		if ( $from_state === $target_state ) {
			return array(
				'success' => false,
				'error'   => __( 'Product is already in requested lifecycle state.', 'statement-collector-core' ),
			);
		}

		$trimmed_reason = trim( $reason );
		if ( '' === $trimmed_reason ) {
			return array(
				'success' => false,
				'error'   => __( 'Reason and audit note are strictly required for lifecycle overrides.', 'statement-collector-core' ),
			);
		}

		// Precondition: Reopening/activating releases (LIVE or PRIVATE_ACCESS) requires stock > 0
		if ( in_array( $target_state, array( ReleaseState::LIVE, ReleaseState::PRIVATE_ACCESS ), true ) ) {
			$available_stock = self::calculate_product_stock( $owner );
			if ( $available_stock <= 0 ) {
				return array(
					'success' => false,
					'error'   => __( 'Stock quantity must be greater than zero to reopen or activate release.', 'statement-collector-core' ),
				);
			}
		}

		// Precondition: Transitioning to PRIVATE_ACCESS requires valid assigned Drop with active DropConfig
		$drop_id = 0;
		if ( ReleaseState::PRIVATE_ACCESS === $target_state ) {
			$drop = class_exists( PublicApi::class ) ? PublicApi::get_drop( $owner ) : null;
			if ( ! is_object( $drop ) || empty( $drop->term_id ) ) {
				return array(
					'success' => false,
					'error'   => __( 'Product must be assigned to a Statement Drop for Private Access.', 'statement-collector-core' ),
				);
			}

			$drop_id     = (int) $drop->term_id;
			$drop_config = class_exists( DropConfig::class ) ? DropConfig::get_config( $drop_id ) : null;
			$now_ts      = time();

			if ( null === $drop_config || ! DropConfig::is_config_valid( $drop_config, $now_ts ) ) {
				return array(
					'success' => false,
					'error'   => __( 'Assigned Drop must have a valid Private Access configuration with a future close time.', 'statement-collector-core' ),
				);
			}
		} else {
			$drop    = class_exists( PublicApi::class ) ? PublicApi::get_drop( $owner ) : null;
			$drop_id = is_object( $drop ) && isset( $drop->term_id ) ? (int) $drop->term_id : 0;
		}

		$stock_before = self::calculate_product_stock( $owner );

		// Mutate release state on owner
		$owner->update_meta_data( Metadata::RELEASE_STATE_KEY, $target_state );
		$saved = method_exists( $owner, 'save' ) ? $owner->save() : true;

		// Re-read persisted state to strictly verify mutation before auditing
		$persisted_state = Metadata::get_release_state( $owner );
		if ( $persisted_state !== $target_state ) {
			return array(
				'success' => false,
				'error'   => __( 'State verification failed: release state was not updated.', 'statement-collector-core' ),
			);
		}

		$stock_after = self::calculate_product_stock( $owner );

		// Record structured audit log ONLY on verified success
		if ( class_exists( LifecycleV2Admin::class ) ) {
			$product_id       = (int) ( method_exists( $product, 'get_id' ) ? $product->get_id() : $owner->get_id() );
			$release_owner_id = (int) $owner->get_id();

			LifecycleV2Admin::record_audit_event(
				array(
					'product_id'       => $product_id,
					'release_owner_id' => $release_owner_id,
					'drop_id'          => $drop_id,
					'actor_id'         => $actor_id,
					'timestamp'        => gmdate( 'Y-m-d H:i:s' ),
					'from_state'       => $from_state,
					'to_state'         => $target_state,
					'reason'           => $trimmed_reason,
					'stock_before'     => $stock_before,
					'stock_after'      => $stock_after,
					'source'           => 'admin_lifecycle_override',
					'success'          => true,
				)
			);
		}

		return array(
			'success'          => true,
			'from_state'       => $from_state,
			'to_state'         => $target_state,
			'product_id'       => (int) ( method_exists( $product, 'get_id' ) ? $product->get_id() : $owner->get_id() ),
			'release_owner_id' => (int) $owner->get_id(),
		);
	}

	/**
	 * Calculates total available stock for a simple or variable product.
	 *
	 * @param object $owner Release owner product.
	 * @return int Total stock quantity.
	 */
	public static function calculate_product_stock( $owner ): int {
		if ( ! is_object( $owner ) ) {
			return 0;
		}

		$type = method_exists( $owner, 'get_type' ) ? $owner->get_type() : 'simple';

		if ( 'variable' === $type && method_exists( $owner, 'get_children' ) ) {
			$children    = (array) $owner->get_children();
			$total_stock = 0;
			$has_unmanaged_instock = false;

			foreach ( $children as $child_id ) {
				$var = function_exists( 'wc_get_product' ) ? wc_get_product( $child_id ) : null;
				if ( is_object( $var ) ) {
					$managing = method_exists( $var, 'managing_stock' ) ? $var->managing_stock() : true;
					$instock  = method_exists( $var, 'is_in_stock' ) ? $var->is_in_stock() : true;

					if ( ! $managing && $instock ) {
						$has_unmanaged_instock = true;
					}

					$qty = method_exists( $var, 'get_stock_quantity' ) ? (int) $var->get_stock_quantity() : 0;
					if ( $qty > 0 ) {
						$total_stock += $qty;
					}
				}
			}

			return ( $total_stock > 0 || $has_unmanaged_instock ) ? max( $total_stock, 1 ) : 0;
		}

		$managing = method_exists( $owner, 'managing_stock' ) ? $owner->managing_stock() : true;
		$instock  = method_exists( $owner, 'is_in_stock' ) ? $owner->is_in_stock() : true;
		$qty      = method_exists( $owner, 'get_stock_quantity' ) ? (int) $owner->get_stock_quantity() : 0;

		if ( ! $managing && $instock ) {
			return max( $qty, 1 );
		}

		return max( $qty, 0 );
	}
}
