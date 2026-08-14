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
	 * Whether a Drop taxonomy term is a Past Drop (contains ONLY ARCHIVED pieces and at least one ARCHIVED piece).
	 *
	 * @param int $term_id Drop term ID.
	 */
	public static function is_past_drop( int $term_id ): bool {
		if ( $term_id < 1 || ! function_exists( 'get_posts' ) ) {
			return false;
		}

		$product_ids = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => Taxonomy::KEY,
						'field'    => 'term_id',
						'terms'    => $term_id,
					),
				),
			)
		);

		if ( empty( $product_ids ) || ! is_array( $product_ids ) ) {
			return false;
		}

		$has_archived = false;

		foreach ( $product_ids as $pid ) {
			$product = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
			$state   = Metadata::get_release_state( $product );

			if ( in_array( $state, array( ReleaseState::LIVE, ReleaseState::SOLD_OUT, ReleaseState::PRIVATE_ACCESS, ReleaseState::UPCOMING ), true ) ) {
				return false;
			}

			if ( ReleaseState::ARCHIVED === $state ) {
				$has_archived = true;
			}
		}

		return $has_archived;
	}

	/**
	 * Retrieves past Drop taxonomy terms (drops with ONLY ARCHIVED pieces).
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
				'orderby'    => 'term_id',
				'order'      => 'DESC',
			)
		);

		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return array();
		}

		$past_drops = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $term->term_id ) && self::is_past_drop( (int) $term->term_id ) ) {
				$past_drops[] = $term;
			}
		}

		return $past_drops;
	}
}
