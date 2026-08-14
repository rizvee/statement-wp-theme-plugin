<?php

namespace Statement\Collector\Core\Release;

use Statement\Collector\Core\Product\Metadata;

defined( 'ABSPATH' ) || exit;

/**
 * Makes terminal Statement state authoritative over normal purchasability.
 */
final class Purchasability {
	/** @var bool */
	private static $booted = false;

	/**
	 * Register the WooCommerce purchasability boundary once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_filter( 'woocommerce_is_purchasable', array( self::class, 'filter_purchasable' ), 10, 2 );
	}

	/**
	 * Preserve WooCommerce policy except for permanent terminal locks.
	 *
	 * @param bool   $purchasable Incoming WooCommerce result.
	 * @param object $product     WooCommerce product-like object.
	 */
	public static function filter_purchasable( bool $purchasable, $product ): bool {
		if ( ! $purchasable ) {
			return false;
		}

		return ! ReleaseState::is_terminal( Metadata::get_release_state( $product ) );
	}
}
