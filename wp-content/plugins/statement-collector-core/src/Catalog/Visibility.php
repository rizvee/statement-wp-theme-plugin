<?php

namespace Statement\Collector\Core\Catalog;

use Statement\Collector\Core\Drop\Taxonomy;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Restricts normal public catalog queries to canonical LIVE products.
 */
final class Visibility {
	/** @var bool */
	private static $booted = false;

	/**
	 * Register the public catalog query boundary once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'woocommerce_product_query', array( self::class, 'apply_live_constraint' ), 10, 2 );
		add_filter( 'rest_product_query', array( self::class, 'filter_public_rest_query' ), 10, 2 );
		add_filter( 'woocommerce_rest_product_object_query', array( self::class, 'filter_public_rest_query' ), 10, 2 );
		add_filter( 'woocommerce_store_api_product_query_args', array( self::class, 'filter_public_store_api_query' ), 10, 2 );
	}

	/**
	 * Append the Statement constraint before WooCommerce executes the main query.
	 *
	 * @param object      $query    Main WooCommerce product query.
	 * @param object|null $wc_query WooCommerce query coordinator.
	 */
	public static function apply_live_constraint( $query, $wc_query = null ): void {
		unset( $wc_query );

		if ( ! self::is_public_catalog_query( $query ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );
		$meta_query = is_array( $meta_query ) ? $meta_query : array();

		if ( self::contains_live_clause( $meta_query ) ) {
			return;
		}

		if ( isset( $meta_query['key'] ) ) {
			$meta_query = array( $meta_query );
		}

		$meta_query[] = array(
			'key'     => Metadata::RELEASE_STATE_KEY,
			'value'   => array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ),
			'compare' => 'IN',
		);

		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Whether this is the normal public main Shop or Statement Drop query.
	 *
	 * @param object $query Query-like object.
	 */
	private static function is_public_catalog_query( $query ): bool {
		if (
			! is_object( $query )
			|| ! method_exists( $query, 'is_main_query' )
			|| ! method_exists( $query, 'get' )
			|| ! method_exists( $query, 'set' )
			|| ! $query->is_main_query()
			|| self::is_non_public_request()
		) {
			return false;
		}

		$is_shop = method_exists( $query, 'is_post_type_archive' )
			&& $query->is_post_type_archive( 'product' );
		$is_drop = method_exists( $query, 'is_tax' )
			&& $query->is_tax( Taxonomy::KEY );

		return $is_shop || $is_drop;
	}

	/**
	 * Exclude admin, REST, cron, and AJAX execution contexts.
	 */
	private static function is_non_public_request(): bool {
		return ( function_exists( 'is_admin' ) && is_admin() )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() )
			|| ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() );
	}

	/**
	 * Avoid appending the same canonical constraint more than once.
	 */
	private static function contains_live_clause( array $meta_query ): bool {
		foreach ( $meta_query as $clause ) {
			if ( ! is_array( $clause ) ) {
				continue;
			}

			if (
				Metadata::RELEASE_STATE_KEY === ( $clause['key'] ?? null )
				&& ReleaseState::LIVE === ( $clause['value'] ?? null )
				&& in_array( strtoupper( (string) ( $clause['compare'] ?? '=' ) ), array( '=', '==' ), true )
			) {
				return true;
			}

			if ( self::contains_live_clause( $clause ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filters public WP REST API and WooCommerce REST API product queries to exclude non-LIVE products for unauthenticated users.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function filter_public_rest_query( array $args, $request = null ): array {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'edit_products' ) ) {
			return $args;
		}

		$meta_query = $args['meta_query'] ?? array();
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		$meta_query[] = array(
			'key'     => Metadata::RELEASE_STATE_KEY,
			'value'   => array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ),
			'compare' => 'IN',
		);

		$args['meta_query'] = $meta_query;
		return $args;
	}

	/**
	 * Filters WooCommerce Store API product collection query args to exclude non-LIVE products.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function filter_public_store_api_query( array $args, $request = null ): array {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'edit_products' ) ) {
			return $args;
		}

		$meta_query = $args['meta_query'] ?? array();
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		$meta_query[] = array(
			'key'     => Metadata::RELEASE_STATE_KEY,
			'value'   => array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ),
			'compare' => 'IN',
		);

		$args['meta_query'] = $meta_query;
		return $args;
	}
}
