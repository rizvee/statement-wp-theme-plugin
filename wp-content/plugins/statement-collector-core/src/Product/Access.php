<?php

namespace Statement\Collector\Core\Product;

use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Enforces public product visibility and purchase eligibility.
 */
final class Access {
	/** @var bool */
	private static $booted = false;

	/**
	 * Register public product access boundaries once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'template_redirect', array( self::class, 'guard_direct_product' ), 0 );
		add_filter( 'woocommerce_add_to_cart_validation', array( self::class, 'validate_add_to_cart' ), 10, 6 );
	}

	/**
	 * Whether a product can appear as a normal public product page.
	 *
	 * @param object $product            WooCommerce product-like object.
	 * @param bool   $authorized_preview Explicit editor preview context.
	 */
	public static function is_publicly_viewable( $product, bool $authorized_preview = false ): bool {
		$release_owner = Metadata::get_release_owner( $product );
		if ( ! is_object( $release_owner ) ) {
			return false;
		}

		if ( $authorized_preview ) {
			return true;
		}

		$state = Metadata::get_release_state( $release_owner );
		if ( in_array( $state, array( ReleaseState::LIVE, ReleaseState::SOLD_OUT, ReleaseState::ARCHIVED ), true ) ) {
			return true;
		}

		if ( ReleaseState::PRIVATE_ACCESS === $state ) {
			return \Statement\Collector\Core\Access\EligibilityService::is_commerce_eligible( $product );
		}

		return false;
	}

	/**
	 * Turn non-eligible direct product requests into ordinary uncached 404 responses.
	 */
	public static function guard_direct_product(): void {
		if ( self::is_non_public_request() || ! function_exists( 'is_singular' ) || ! is_singular( 'product' ) ) {
			return;
		}

		$product_id = function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0;
		$product    = $product_id > 0 && function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false;
		$preview    = function_exists( 'is_preview' )
			&& is_preview()
			&& function_exists( 'current_user_can' )
			&& current_user_can( 'edit_post', $product_id );

		if ( self::is_publicly_viewable( $product, $preview ) ) {
			return;
		}

		global $wp_query, $wp_the_query;
		if ( is_object( $wp_query ) && method_exists( $wp_query, 'set_404' ) ) {
			$wp_query->set_404();
			$wp_query->post           = null;
			$wp_query->posts          = array();
			$wp_query->queried_object = null;
			$wp_query->queried_object_id = 0;
			$wp_query->post_count      = 0;
			$wp_query->current_post    = -1;
			$wp_query->in_the_loop     = false;
			if ( method_exists( $wp_query, 'set' ) ) {
				$wp_query->set( 'p', 0 );
				$wp_query->set( 'name', '' );
				$wp_query->set( 'product', '' );
			}
			$wp_the_query = $wp_query;
		}

		global $post;
		$post = null;

		if ( function_exists( 'status_header' ) ) {
			status_header( 404 );
		}

		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}
	}

	/**
	 * Reject Add-to-Cart requests for items that are not commerce eligible.
	 *
	 * @param bool  $passed         Prior WooCommerce validation result.
	 * @param int   $product_id     Parent or simple product ID.
	 * @param int   $quantity       Requested quantity.
	 * @param int   $variation_id   Variation ID when supplied.
	 * @param array $variations     Submitted variation attributes.
	 * @param array $cart_item_data Submitted cart item data.
	 */
	public static function validate_add_to_cart(
		$passed,
		$product_id,
		$quantity = 1,
		$variation_id = 0,
		$variations = array(),
		$cart_item_data = array()
	): bool {
		unset( $quantity, $variations, $cart_item_data );

		if ( ! $passed ) {
			return false;
		}

		$requested_id = (int) $variation_id > 0 ? (int) $variation_id : (int) $product_id;
		$product      = $requested_id > 0 && function_exists( 'wc_get_product' ) ? wc_get_product( $requested_id ) : false;
		$release_owner = Metadata::get_release_owner( $product );
		$is_live       = is_object( $release_owner )
			&& ReleaseState::LIVE === Metadata::get_release_state( $release_owner );

		if ( $is_live || \Statement\Collector\Core\Access\EligibilityService::is_commerce_eligible( $product ) ) {
			do_action( 'statement_private_access_added_to_cart', $product );
			return true;
		}

		self::add_unavailable_notice();

		return false;
	}

	/**
	 * Whether this request must remain outside the public template gate.
	 */
	private static function is_non_public_request(): bool {
		return ( function_exists( 'is_admin' ) && is_admin() )
			|| ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() )
			|| ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST );
	}

	/**
	 * Add one lifecycle-neutral WooCommerce error notice when available.
	 */
	private static function add_unavailable_notice(): void {
		if ( ! function_exists( 'wc_add_notice' ) ) {
			return;
		}

		$message = function_exists( '__' )
			? __( 'This piece is not currently available for purchase.', 'statement-collector-core' )
			: 'This piece is not currently available for purchase.';
		if ( function_exists( 'wc_has_notice' ) && wc_has_notice( $message, 'error' ) ) {
			return;
		}

		wc_add_notice( $message, 'error' );
	}
}
