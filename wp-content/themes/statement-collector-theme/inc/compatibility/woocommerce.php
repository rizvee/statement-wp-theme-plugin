<?php
/**
 * WooCommerce Theme Compatibility Adapter.
 *
 * Configures WooCommerce core theme support, image sizes, and hook integrations.
 * Note: High-Performance Order Storage is declared and owned by Statement Collector Core plugin.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class WooCommerce {
	/**
	 * Boot WooCommerce theme presentation compatibility.
	 */
	public static function boot(): void {
		self::setup_woocommerce_support();
		add_filter( 'woocommerce_enqueue_styles', array( self::class, 'filter_woocommerce_styles' ) );
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
		if ( isset( $styles['woocommerce-general'] ) ) {
			unset( $styles['woocommerce-general'] );
		}
		if ( isset( $styles['woocommerce-layout'] ) ) {
			unset( $styles['woocommerce-layout'] );
		}
		if ( isset( $styles['woocommerce-smallscreen'] ) ) {
			unset( $styles['woocommerce-smallscreen'] );
		}
		return $styles;
	}
}
