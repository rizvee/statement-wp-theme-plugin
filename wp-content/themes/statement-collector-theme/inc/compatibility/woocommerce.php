<?php
/**
 * WooCommerce Theme Compatibility Adapter & HPOS Declaration.
 *
 * Configures WooCommerce core theme support, image sizes, and hook integrations.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class WooCommerce {
	/**
	 * Boot WooCommerce compatibility.
	 */
	public static function boot(): void {
		add_action( 'after_setup_theme', array( self::class, 'setup_woocommerce_support' ) );
		add_action( 'before_woocommerce_init', array( self::class, 'declare_hpos_compatibility' ) );
		add_filter( 'woocommerce_enqueue_styles', array( self::class, 'filter_woocommerce_styles' ) );
	}

	/**
	 * Declare High-Performance Order Storage (HPOS) and theme supports.
	 */
	public static function declare_hpos_compatibility(): void {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', STATEMENT_COLLECTOR_THEME_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', STATEMENT_COLLECTOR_THEME_FILE, true );
		}
	}

	/**
	 * Configure WooCommerce theme supports.
	 */
	public static function setup_woocommerce_support(): void {
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width'         => 600,
				'single_image_width'            => 1200,
				'product_grid'                  => array(
					'default_rows'    => 3,
					'min_rows'        => 1,
					'max_rows'        => 6,
					'default_columns' => 3,
					'min_columns'     => 2,
					'max_columns'     => 4,
				),
			)
		);

		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	/**
	 * Filter default WooCommerce stylesheets.
	 *
	 * @param array $styles Enqueued styles.
	 * @return array
	 */
	public static function filter_woocommerce_styles( array $styles ): array {
		return $styles;
	}
}
