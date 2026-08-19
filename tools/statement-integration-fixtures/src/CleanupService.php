<?php

namespace Statement\Integration\Fixtures;

defined( 'ABSPATH' ) || exit;

class CleanupService {
	public static function cleanup(): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return array( 'success' => false, 'message' => 'Unauthorized capability check failed.' );
		}

		$manifest = get_option( FixtureService::MANIFEST_OPTION, false );
		if ( ! $manifest || ! is_array( $manifest ) ) {
			return array( 'success' => true, 'message' => 'No manifest found. Nothing to clean up.' );
		}

		$deleted_items = array();

		// Delete variations recorded in manifest
		$variation_ids = $manifest['variation_ids'] ?? array();
		foreach ( $variation_ids as $vid ) {
			if ( get_post( $vid ) ) {
				wp_delete_post( $vid, true );
				$deleted_items[] = "Variation ID {$vid}";
			}
		}

		// Delete products recorded in manifest
		$product_ids = $manifest['product_ids'] ?? array();
		foreach ( $product_ids as $pid ) {
			if ( get_post( $pid ) ) {
				wp_delete_post( $pid, true );
				$deleted_items[] = "Product ID {$pid}";
			}
		}

		// Delete Drop term if recorded in manifest and matches test slug
		if ( ! empty( $manifest['drop_id'] ) ) {
			$term = get_term_by( 'id', $manifest['drop_id'], 'statement_drop' );
			if ( $term && 'test-live-drop-01' === $term->slug ) {
				wp_delete_term( $term->term_id, 'statement_drop' );
				$deleted_items[] = "Statement Drop term '{$term->name}'";
			}
		}

		// Delete Category term if recorded in manifest and matches test slug
		if ( ! empty( $manifest['category_id'] ) ) {
			$term = get_term_by( 'id', $manifest['category_id'], 'product_cat' );
			if ( $term && 'test-outerwear' === $term->slug ) {
				wp_delete_term( $term->term_id, 'product_cat' );
				$deleted_items[] = "Product Category term '{$term->name}'";
			}
		}

		// Delete Tag term if recorded in manifest and matches test slug
		if ( ! empty( $manifest['product_tag_id'] ) ) {
			$term = get_term_by( 'id', $manifest['product_tag_id'], 'product_tag' );
			if ( $term && 'test-integration' === $term->slug ) {
				wp_delete_term( $term->term_id, 'product_tag' );
				$deleted_items[] = "Product Tag term '{$term->name}'";
			}
		}

		delete_option( FixtureService::MANIFEST_OPTION );

		return array(
			'success' => true,
			'message' => 'Cleaned up manifest fixtures: ' . ( empty( $deleted_items ) ? 'none remaining' : implode( ', ', $deleted_items ) ),
		);
	}

	public static function restore_currency(): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return array( 'success' => false, 'message' => 'Unauthorized capability check failed.' );
		}

		$manifest = get_option( FixtureService::MANIFEST_OPTION, false );
		$previous = is_array( $manifest ) ? ( $manifest['previous_currency'] ?? 'USD' ) : 'USD';

		update_option( 'woocommerce_currency', $previous );

		return array(
			'success' => true,
			'message' => "Restored WooCommerce currency to '{$previous}'.",
		);
	}

	/**
	 * Perform structured read-only audit of all QA entities across all tables and entity types.
	 *
	 * @return array<string, mixed>
	 */
	public static function dry_run(): array {
		return FinalCleanupService::dry_run();
	}

	/**
	 * Execute hardened, deterministic production cleanup.
	 *
	 * @return array<string, mixed>
	 */
	public static function final_cleanup(): array {
		return FinalCleanupService::execute_cleanup();
	}
}
