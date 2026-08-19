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
	 * Retrieves products in ARCHIVED state for dedicated Archive presentation, excluding QA fixtures.
	 *
	 * @param int $limit Max items to return.
	 * @return array WooCommerce product objects.
	 */
	public static function get_archive_products( int $limit = 12 ): array {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$products = wc_get_products(
			array(
				'limit'      => -1,
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

		if ( empty( $products ) || ! is_array( $products ) ) {
			return array();
		}

		$filtered = array();
		foreach ( $products as $product ) {
			if ( ! is_object( $product ) ) {
				continue;
			}

			// Strictly exclude internal QA test fixtures
			if ( class_exists( '\Statement\Collector\Core\Catalog\Visibility' ) && \Statement\Collector\Core\Catalog\Visibility::is_fixture_product( $product ) ) {
				continue;
			}

			$filtered[] = $product;
			if ( count( $filtered ) >= $limit ) {
				break;
			}
		}

		return $filtered;
	}

	/**
	 * Whether a Drop taxonomy term is a Past Drop (contains ONLY ARCHIVED pieces and at least one ARCHIVED piece, excluding QA fixtures).
	 *
	 * @param int|\WP_Term|object|mixed $term_or_id Drop term ID or term object.
	 */
	public static function is_past_drop( $term_or_id ): bool {
		$term_id = is_object( $term_or_id ) && isset( $term_or_id->term_id ) ? (int) $term_or_id->term_id : (int) $term_or_id;
		if ( $term_id < 1 || ! function_exists( 'get_posts' ) ) {
			return false;
		}

		if ( function_exists( 'get_term' ) ) {
			$term = get_term( $term_id, Taxonomy::KEY );
			if ( is_object( $term ) && ! is_wp_error( $term ) ) {
				if ( 0 === stripos( (string) $term->name, 'TEST' ) || 0 === stripos( (string) $term->slug, 'test-' ) ) {
					return false;
				}
			}
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
			if ( class_exists( '\Statement\Collector\Core\Catalog\Visibility' ) && \Statement\Collector\Core\Catalog\Visibility::is_fixture_product( $pid ) ) {
				continue;
			}

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
	 * Retrieves past Drop taxonomy terms (drops with ONLY ARCHIVED pieces, excluding QA fixtures).
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
			if ( is_object( $term ) && isset( $term->term_id ) ) {
				if ( 0 === stripos( (string) $term->name, 'TEST' ) || 0 === stripos( (string) $term->slug, 'test-' ) ) {
					continue;
				}
				if ( self::is_past_drop( (int) $term->term_id ) ) {
					$past_drops[] = $term;
				}
			}
		}

		return $past_drops;
	}

	/**
	 * Return the currently active Statement Drop (LIVE or PRIVATE_ACCESS, excluding QA fixtures).
	 *
	 * @return object|null
	 */
	public static function get_current_drop() {
		if ( ! function_exists( 'get_terms' ) ) {
			return null;
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
			return null;
		}

		// First preference: Drop with LIVE products
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $term->term_id ) ) {
				if ( 0 === stripos( (string) $term->name, 'TEST' ) || 0 === stripos( (string) $term->slug, 'test-' ) ) {
					continue;
				}
				$state = self::get_drop_state( $term );
				if ( ReleaseState::LIVE === $state ) {
					return $term;
				}
			}
		}

		// Second preference: Drop with PRIVATE_ACCESS products
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $term->term_id ) ) {
				if ( 0 === stripos( (string) $term->name, 'TEST' ) || 0 === stripos( (string) $term->slug, 'test-' ) ) {
					continue;
				}
				$state = self::get_drop_state( $term );
				if ( ReleaseState::PRIVATE_ACCESS === $state ) {
					return $term;
				}
			}
		}

		return null;
	}

	/**
	 * Read the canonical release state of a Drop based on its products or configuration.
	 *
	 * @param object|int $term Drop term object or ID.
	 * @return string
	 */
	public static function get_drop_state( $term ): string {
		$term_id = is_object( $term ) && isset( $term->term_id ) ? (int) $term->term_id : (int) $term;
		if ( $term_id < 1 || ! function_exists( 'get_posts' ) ) {
			return ReleaseState::UPCOMING;
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
			return ReleaseState::UPCOMING;
		}

		$states = array();
		foreach ( $product_ids as $pid ) {
			if ( class_exists( '\Statement\Collector\Core\Catalog\Visibility' ) && \Statement\Collector\Core\Catalog\Visibility::is_fixture_product( $pid ) ) {
				continue;
			}

			$product  = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
			$state    = Metadata::get_release_state( $product );
			$states[] = $state;
		}

		if ( in_array( ReleaseState::LIVE, $states, true ) ) {
			return ReleaseState::LIVE;
		}
		if ( in_array( ReleaseState::PRIVATE_ACCESS, $states, true ) ) {
			return ReleaseState::PRIVATE_ACCESS;
		}
		if ( in_array( ReleaseState::SOLD_OUT, $states, true ) ) {
			return ReleaseState::SOLD_OUT;
		}
		if ( in_array( ReleaseState::ARCHIVED, $states, true ) ) {
			return ReleaseState::ARCHIVED;
		}

		return ReleaseState::UPCOMING;
	}

	/**
	 * Retrieve publicly live products belonging to a specific Drop.
	 *
	 * @param int $drop_id Drop term ID.
	 * @param int $limit Max products to return.
	 * @return array
	 */
	public static function get_live_products_for_drop( int $drop_id, int $limit = 12 ): array {
		if ( $drop_id < 1 || ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$products = wc_get_products(
			array(
				'limit'      => -1,
				'status'     => 'publish',
				'tax_query'  => array(
					array(
						'taxonomy' => Taxonomy::KEY,
						'field'    => 'term_id',
						'terms'    => $drop_id,
					),
				),
				'meta_query' => array(
					array(
						'key'     => Metadata::RELEASE_STATE_KEY,
						'value'   => ReleaseState::LIVE,
						'compare' => '=',
					),
				),
			)
		);

		if ( empty( $products ) || ! is_array( $products ) ) {
			return array();
		}

		$filtered = array();
		foreach ( $products as $product ) {
			if ( ! is_object( $product ) ) {
				continue;
			}

			if ( class_exists( '\Statement\Collector\Core\Catalog\Visibility' ) && \Statement\Collector\Core\Catalog\Visibility::is_fixture_product( $product ) ) {
				continue;
			}

			$filtered[] = $product;
			if ( count( $filtered ) >= $limit ) {
				break;
			}
		}

		return $filtered;
	}
}
