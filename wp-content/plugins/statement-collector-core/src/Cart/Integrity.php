<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Cart;

use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps restored and current cart lines within the public LIVE lifecycle.
 */
final class Integrity {
	private const REMOVAL_NOTICE = 'A piece in your bag is no longer available.';

	/** @var bool */
	private static $booted = false;

	/** @var bool */
	private static $session_notice_seen = false;

	/**
	 * Register cart-session and current-cart validation once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_filter( 'woocommerce_cart_item_is_purchasable', array( self::class, 'filter_session_item' ), 10, 4 );
		add_filter( 'woocommerce_cart_item_removed_message', array( self::class, 'filter_removed_message' ), 10, 2 );
		add_filter( 'woocommerce_add_error', array( self::class, 'filter_error_notice' ) );
		add_action( 'woocommerce_check_cart_items', array( self::class, 'reconcile_cart' ), 0 );
	}

	/**
	 * Reject non-LIVE products while WooCommerce restores a cart session.
	 *
	 * @param bool   $is_purchasable Incoming WooCommerce decision.
	 * @param string $cart_item_key  Cart item key.
	 * @param array  $cart_item      Stored cart item data.
	 * @param object $product        WooCommerce product-like object.
	 */
	public static function filter_session_item( bool $is_purchasable, string $cart_item_key, array $cart_item, $product ): bool {
		unset( $cart_item_key, $cart_item );

		return $is_purchasable && self::is_cart_product_eligible( $product );
	}

	/**
	 * Replace lifecycle-revealing restored-item copy with one generic message.
	 *
	 * @param string $message WooCommerce removal message.
	 * @param object $product WooCommerce product-like object.
	 */
	public static function filter_removed_message( string $message, $product ): string {
		return self::is_cart_product_eligible( $product ) ? $message : self::REMOVAL_NOTICE;
	}

	/**
	 * Suppress duplicate generic session-restoration errors in one request.
	 */
	public static function filter_error_notice( string $message ): string {
		if ( self::REMOVAL_NOTICE !== $message ) {
			return $message;
		}

		if ( self::$session_notice_seen || self::has_removal_notice( self::get_removal_notice_type() ) ) {
			return '';
		}

		self::$session_notice_seen = true;

		return $message;
	}

	/**
	 * Remove stale non-LIVE lines before native cart/checkout validation.
	 */
	public static function reconcile_cart(): void {
		if ( ! self::is_frontend_cart_request() || ! function_exists( 'WC' ) ) {
			return;
		}

		$woocommerce = WC();
		$cart        = is_object( $woocommerce ) && isset( $woocommerce->cart ) ? $woocommerce->cart : null;
		if ( ! is_object( $cart ) || ! method_exists( $cart, 'get_cart' ) || ! method_exists( $cart, 'remove_cart_item' ) ) {
			return;
		}

		$removed = false;
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = is_array( $cart_item ) && isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( self::is_cart_product_eligible( $product ) ) {
				continue;
			}

			$removed = (bool) $cart->remove_cart_item( (string) $cart_item_key ) || $removed;
		}

		if ( $removed ) {
			self::add_removal_notice_once();
		}
	}

	/**
	 * Canonical cart eligibility: only the release owner in LIVE may remain.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function is_cart_product_eligible( $product ): bool {
		$owner = Metadata::get_release_owner( $product );
		if ( ! is_object( $owner ) ) {
			return false;
		}

		$state = Metadata::get_release_state( $owner );
		if ( ReleaseState::LIVE === $state ) {
			return true;
		}

		return class_exists( '\Statement\Collector\Core\Access\EligibilityService' )
			&& \Statement\Collector\Core\Access\EligibilityService::is_commerce_eligible( $product );
	}

	/**
	 * Keep reconciliation out of non-interactive maintenance requests.
	 */
	private static function is_frontend_cart_request(): bool {
		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return false;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		return ! function_exists( 'is_admin' ) || ! is_admin() || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() );
	}

	/**
	 * Add one lifecycle-neutral notice while preserving native Woo notice storage.
	 */
	private static function add_removal_notice_once(): void {
		$notice_type = self::get_removal_notice_type();
		if ( ! function_exists( 'wc_add_notice' ) || self::has_removal_notice( $notice_type ) ) {
			return;
		}

		wc_add_notice( self::REMOVAL_NOTICE, $notice_type );
	}

	/**
	 * Checkout processing only stops order creation for error notices.
	 */
	private static function get_removal_notice_type(): string {
		return defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ? 'error' : 'notice';
	}

	private static function has_removal_notice( string $notice_type ): bool {
		if ( ! function_exists( 'wc_has_notice' ) ) {
			return false;
		}

		return wc_has_notice( self::REMOVAL_NOTICE, $notice_type );
	}
}
