<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Declare the presentation boundary for WooCommerce templates.
 */
function setup_woocommerce(): void {
	add_theme_support( 'woocommerce' );
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup_woocommerce' );
