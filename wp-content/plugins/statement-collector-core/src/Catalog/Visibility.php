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
			'value'   => ReleaseState::LIVE,
			'compare' => '=',
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
}
