<?php

namespace Statement\Collector\Core;

use Statement\Collector\Core\Drop\Taxonomy;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Read-only domain facts for presentation integrations.
 */
final class PublicApi {
	/**
	 * Read the canonical release state, including parent ownership for variations.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function get_release_state( $product ): string {
		return Metadata::get_release_state( $product );
	}

	/**
	 * Whether the product is eligible for normal public presentation.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function is_publicly_live( $product ): bool {
		return ReleaseState::LIVE === self::get_release_state( $product );
	}

	/**
	 * Return the canonical product owner's optional edition label.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function get_edition_label( $product ): string {
		$release_owner = Metadata::get_release_owner( $product );

		return Metadata::get_edition_label( $release_owner );
	}

	/**
	 * Return the canonical product owner's first valid Statement Drop.
	 *
	 * @param object $product WooCommerce product-like object.
	 * @return object|null
	 */
	public static function get_drop( $product ) {
		$release_owner = Metadata::get_release_owner( $product );
		if (
			! is_object( $release_owner )
			|| ! method_exists( $release_owner, 'get_id' )
			|| ! function_exists( 'get_the_terms' )
		) {
			return null;
		}

		$product_id = (int) $release_owner->get_id();
		if ( $product_id < 1 ) {
			return null;
		}

		$terms = get_the_terms( $product_id, Taxonomy::KEY );
		if (
			( function_exists( 'is_wp_error' ) && is_wp_error( $terms ) )
			|| ! is_array( $terms )
		) {
			return null;
		}

		foreach ( $terms as $term ) {
			if (
				is_object( $term )
				&& isset( $term->term_id, $term->taxonomy )
				&& (int) $term->term_id > 0
				&& Taxonomy::KEY === $term->taxonomy
			) {
				return $term;
			}
		}

		return null;
	}

	/**
	 * Whether the product is in terminal SOLD_OUT state.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function is_sold_out( $product ): bool {
		return ReleaseState::SOLD_OUT === self::get_release_state( $product );
	}

	/**
	 * Whether the product is in terminal ARCHIVED state.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function is_archived( $product ): bool {
		return ReleaseState::ARCHIVED === self::get_release_state( $product );
	}

	/**
	 * Retrieves products in ARCHIVED state for dedicated Archive presentation.
	 *
	 * @param int $limit Max items to return.
	 * @return array WooCommerce product objects.
	 */
	public static function get_archive_products( int $limit = 12 ): array {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		return wc_get_products(
			array(
				'limit'      => $limit,
				'status'     => 'publish',
				'meta_query' => array(
					array(
						'key'     => Metadata::RELEASE_STATE_KEY,
						'value'   => ReleaseState::ARCHIVED,
						'compare' => '=',
					),
				),
			)
		);
	}

	/**
	 * Retrieves past Drop taxonomy terms.
	 *
	 * @return array
	 */
	public static function get_past_drops(): array {
		if ( ! function_exists( 'get_terms' ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => Taxonomy::KEY,
				'hide_empty' => false,
			)
		);

		return is_array( $terms ) ? $terms : array();
	}
}
