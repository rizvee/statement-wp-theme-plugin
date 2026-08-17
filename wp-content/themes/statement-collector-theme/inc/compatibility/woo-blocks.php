<?php
/**
 * WooCommerce Blocks Compatibility Adapter.
 *
 * Ensures modern WooCommerce Block Cart, Block Checkout, and Product Collection
 * render with Statement luxury typography and palette without interfering with
 * payment processor iframes or token fields.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class WooBlocks {
	/**
	 * Boot WooCommerce Blocks compatibility.
	 */
	public static function boot(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_block_styles' ), 20 );
	}

	/**
	 * Enqueue scoped WooCommerce Blocks compatibility CSS.
	 */
	public static function enqueue_block_styles(): void {
		if ( ! function_exists( 'is_cart' ) || ! function_exists( 'is_checkout' ) ) {
			return;
		}

		if ( is_cart() || is_checkout() || ( function_exists( 'has_block' ) && ( has_block( 'woocommerce/cart' ) || has_block( 'woocommerce/checkout' ) ) ) ) {
			$uri = function_exists( 'get_theme_file_uri' ) ? get_theme_file_uri( 'assets/css/woo-blocks.css' ) : get_template_directory_uri() . '/assets/css/woo-blocks.css';
			wp_enqueue_style(
				'statement-woo-blocks',
				$uri,
				array(),
				STATEMENT_COLLECTOR_THEME_VERSION
			);
		}
	}
}
