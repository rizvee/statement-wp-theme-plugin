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
		add_filter( 'woocommerce_structured_data_product_offer', array( self::class, 'filter_structured_data_offer' ), 10, 2 );
		add_filter( 'rest_product_query', array( self::class, 'filter_public_rest_query' ), 10, 2 );
		add_filter( 'woocommerce_rest_product_object_query', array( self::class, 'filter_public_rest_query' ), 10, 2 );
		add_filter( 'woocommerce_store_api_product_query_args', array( self::class, 'filter_public_store_api_query' ), 10, 2 );
		add_filter( 'rest_pre_dispatch', array( self::class, 'prepare_store_api_boundary' ), 9, 3 );
		add_filter( 'rest_post_dispatch', array( self::class, 'filter_store_api_response' ), 10, 3 );
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

		$target_states = array( ReleaseState::LIVE, ReleaseState::SOLD_OUT );

		if ( method_exists( $query, 'is_tax' ) && $query->is_tax( Taxonomy::KEY ) ) {
			$queried = method_exists( $query, 'get_queried_object' ) ? $query->get_queried_object() : null;
			$term_id = is_object( $queried ) && isset( $queried->term_id ) ? (int) $queried->term_id : 0;
			if ( $term_id > 0 && class_exists( '\Statement\Collector\Core\PublicApi' ) && \Statement\Collector\Core\PublicApi::is_past_drop( $term_id ) ) {
				$target_states = array( ReleaseState::ARCHIVED );
			}
		}

		$meta_query[] = array(
			'key'     => Metadata::RELEASE_STATE_KEY,
			'value'   => $target_states,
			'compare' => 'IN',
		);

		$query->set( 'meta_query', $meta_query );

		if ( in_array( ReleaseState::LIVE, $target_states, true ) && in_array( ReleaseState::SOLD_OUT, $target_states, true ) ) {
			$query->set( 'meta_key', Metadata::RELEASE_STATE_KEY );
			$query->set(
				'orderby',
				array(
					'meta_value' => 'ASC',
					'date'       => 'DESC',
				)
			);
		}
	}

	/**
	 * Ensure WooCommerce product structured data never advertises terminal items as in-stock.
	 *
	 * @param array  $offer   Incoming schema offer array.
	 * @param object $product WooCommerce product object.
	 * @return array
	 */
	public static function filter_structured_data_offer( array $offer, $product ): array {
		$owner = Metadata::get_release_owner( $product );
		if ( is_object( $owner ) ) {
			$state = Metadata::get_release_state( $owner );
			if ( ReleaseState::is_terminal( $state ) ) {
				$offer['availability'] = 'https://schema.org/OutOfStock';
			}
		}

		return $offer;
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
				&& (
					ReleaseState::LIVE === ( $clause['value'] ?? null )
					|| array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ) === ( $clause['value'] ?? null )
				)
				&& in_array( strtoupper( (string) ( $clause['compare'] ?? '=' ) ), array( '=', '==', 'IN' ), true )
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

	/**
	 * Install the Store API query boundary before WooCommerce dispatches product routes.
	 *
	 * WooCommerce 11's Store API builds a WP_Query directly and does not apply the
	 * legacy product-query-args filter above. The request-scoped pre_get_posts hook
	 * therefore enforces the same release-state boundary on current Store API routes.
	 *
	 * @param mixed  $result  Pre-dispatch result.
	 * @param object $server  REST server.
	 * @param object $request REST request.
	 * @return mixed
	 */
	public static function prepare_store_api_boundary( $result, $server, $request ) {
		unset( $server );

		if ( function_exists( 'current_user_can' ) && current_user_can( 'edit_products' ) ) {
			return $result;
		}

		$route = is_object( $request ) && method_exists( $request, 'get_route' )
			? (string) $request->get_route()
			: '';
		if ( ! preg_match( '#^/wc/store/v1/products(?:/\d+)?$#', $route ) ) {
			return $result;
		}

		if ( function_exists( 'add_action' ) ) {
			add_action( 'pre_get_posts', array( self::class, 'apply_store_api_release_constraint' ), 0 );
		}

		return $result;
	}

	/**
	 * Restrict Store API product queries to publicly presentable lifecycle states.
	 *
	 * @param object $query WP_Query-like object.
	 */
	public static function apply_store_api_release_constraint( $query ): void {
		if ( ! is_object( $query ) || ! method_exists( $query, 'get' ) || ! method_exists( $query, 'set' ) ) {
			return;
		}

		$post_types = (array) $query->get( 'post_type' );
		if ( ! array_intersect( array( 'product', 'product_variation' ), $post_types ) ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );
		$meta_query = is_array( $meta_query ) ? $meta_query : array();
		$meta_query[] = array(
			'key'     => Metadata::RELEASE_STATE_KEY,
			'value'   => array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ),
			'compare' => 'IN',
		);
		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Fail closed at the Store API response boundary if a host/WooCommerce query
	 * path bypasses the earlier query filters.
	 *
	 * @param object $response REST response.
	 * @param object $server   REST server.
	 * @param object $request  REST request.
	 * @return object
	 */
	public static function filter_store_api_response( $response, $server, $request ) {
		unset( $server );

		if ( function_exists( 'current_user_can' ) && current_user_can( 'edit_products' ) ) {
			return $response;
		}

		$route = is_object( $request ) && method_exists( $request, 'get_route' )
			? (string) $request->get_route()
			: '';
		if (
			! preg_match( '#^/wc/store/v1/products(?:/\d+)?$#', $route )
			|| ! is_object( $response )
			|| ! method_exists( $response, 'get_data' )
			|| ! method_exists( $response, 'set_data' )
		) {
			return $response;
		}

		$data = $response->get_data();
		if ( ! is_array( $data ) ) {
			return $response;
		}

		if ( array_is_list( $data ) ) {
			$filtered = array_values(
				array_filter(
					$data,
					static function ( $item ): bool {
						return is_array( $item )
							&& isset( $item['id'] )
							&& self::is_public_store_api_product_id( (int) $item['id'] );
					}
				)
			);
			$response->set_data( $filtered );

			return $response;
		}

		if ( isset( $data['id'] ) && ! self::is_public_store_api_product_id( (int) $data['id'] ) ) {
			$response->set_data(
				array(
					'code'    => 'woocommerce_rest_product_not_found',
					'message' => 'Product not found.',
					'data'    => array( 'status' => 404 ),
				)
			);
			if ( method_exists( $response, 'set_status' ) ) {
				$response->set_status( 404 );
			}
		}

		return $response;
	}

	/**
	 * Whether a product ID is safe for the anonymous Store API.
	 */
	private static function is_public_store_api_product_id( int $product_id ): bool {
		$product = $product_id > 0 && function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false;
		$owner   = Metadata::get_release_owner( $product );

		return is_object( $owner )
			&& in_array( Metadata::get_release_state( $owner ), array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ), true );
	}
}
