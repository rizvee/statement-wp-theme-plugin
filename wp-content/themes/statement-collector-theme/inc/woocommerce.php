<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Declare the presentation boundary for WooCommerce templates.
 */
function setup_woocommerce(): void {
	add_theme_support( 'woocommerce' );

	if ( function_exists( 'remove_action' ) && function_exists( 'add_action' ) ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', __NAMESPACE__ . '\\output_woocommerce_wrapper_start', 10 );
		add_action( 'woocommerce_after_main_content', __NAMESPACE__ . '\\output_woocommerce_wrapper_end', 10 );
	}
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup_woocommerce' );

/**
 * Output customized WooCommerce open content wrapper.
 */
function output_woocommerce_wrapper_start(): void {
	$classes = array( 'content-area' );
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$classes[] = 'statement-catalog';
		$classes[] = 'statement-container--wide';
	}
	echo '<div id="primary" class="' . esc_attr( implode( ' ', $classes ) ) . '"><main id="main" class="site-main" role="main">';
}

/**
 * Output customized WooCommerce close content wrapper.
 */
function output_woocommerce_wrapper_end(): void {
	echo '</main></div>';
}
