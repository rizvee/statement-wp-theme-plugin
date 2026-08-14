<?php

namespace Statement\Integration\Fixtures;

defined( 'ABSPATH' ) || exit;

class VerificationService {
	public static function verify(): array {
		$manifest = get_option( FixtureService::MANIFEST_OPTION, false );
		if ( ! $manifest || ! is_array( $manifest ) ) {
			return array(
				'seeded'  => false,
				'message' => 'No integration fixture manifest found. Fixtures not seeded.',
			);
		}

		$current_currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'N/A';
		$cat_term         = get_term_by( 'id', $manifest['category_id'] ?? 0, 'product_cat' );
		$tag_term         = get_term_by( 'id', $manifest['product_tag_id'] ?? 0, 'product_tag' );
		$drop_term        = get_term_by( 'id', $manifest['drop_id'] ?? 0, 'statement_drop' );

		$products_data = array();
		$product_ids   = $manifest['product_ids'] ?? array();

		foreach ( $product_ids as $pid ) {
			$product = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
			if ( ! $product ) {
				$products_data[] = array(
					'id'      => $pid,
					'name'    => 'MISSING ENTITY',
					'status'  => 'ERROR',
					'details' => 'Product post ID not found in WooCommerce.',
				);
				continue;
			}

			$release_state = class_exists( '\Statement\Collector\Core\Product\Metadata' )
				? \Statement\Collector\Core\Product\Metadata::get_release_state( $product )
				: get_post_meta( $pid, '_statement_release_state', true );

			$edition_label = class_exists( '\Statement\Collector\Core\Product\Metadata' )
				? \Statement\Collector\Core\Product\Metadata::get_edition_label( $product )
				: get_post_meta( $pid, '_statement_edition_label', true );

			$drop_terms = wp_get_post_terms( $pid, 'statement_drop', array( 'fields' => 'names' ) );
			$drop_name  = ! empty( $drop_terms ) && ! is_wp_error( $drop_terms ) ? implode( ', ', $drop_terms ) : 'NONE';

			$purchasable = $product->is_purchasable();
			if ( class_exists( '\Statement\Collector\Core\Release\Purchasability' ) ) {
				$purchasable = \Statement\Collector\Core\Release\Purchasability::is_purchasable( $product );
			}

			$variations_summary = array();
			if ( 'variable' === $product->get_type() ) {
				$children = $product->get_children();
				foreach ( $children as $cid ) {
					$cvar = wc_get_product( $cid );
					if ( $cvar ) {
						$variations_summary[] = sprintf(
							'%s (SKU %s, AUD %s, Stock: %d)',
							implode( ' ', $cvar->get_variation_attributes() ),
							$cvar->get_sku(),
							$cvar->get_price(),
							$cvar->get_stock_quantity()
						);
					}
				}
			}

			$products_data[] = array(
				'id'            => $pid,
				'name'          => $product->get_name(),
				'sku'           => $product->get_sku(),
				'type'          => $product->get_type(),
				'release_state' => $release_state,
				'edition_label' => $edition_label,
				'drop_name'     => $drop_name,
				'price'         => $product->get_price(),
				'stock_qty'     => $product->get_stock_quantity(),
				'stock_status'  => $product->get_stock_status(),
				'purchasable'   => $purchasable ? 'YES' : 'NO (BLOCKED)',
				'variations'    => $variations_summary,
			);
		}

		return array(
			'seeded'            => true,
			'manifest'          => $manifest,
			'current_currency'  => $current_currency,
			'previous_currency' => $manifest['previous_currency'] ?? 'USD',
			'category_name'     => $cat_term ? $cat_term->name : 'MISSING',
			'product_tag_name'  => $tag_term ? $tag_term->name : 'MISSING',
			'drop_name'         => $drop_term ? $drop_term->name : 'MISSING',
			'products'          => $products_data,
		);
	}
}
