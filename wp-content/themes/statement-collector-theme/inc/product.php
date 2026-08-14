<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current frontend request is a WooCommerce product page.
 */
function is_statement_product(): bool {
	return function_exists( 'is_product' ) && is_product();
}

/**
 * Use Statement purchase language for supported native product forms.
 *
 * @param string $label   WooCommerce's default label.
 * @param object $product WooCommerce product-like object.
 */
function product_add_to_cart_label( string $label, $product ): string {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_type' ) ) {
		return $label;
	}

	if ( ! in_array( $product->get_type(), array( 'simple', 'variable' ), true ) ) {
		return $label;
	}

	return __( 'ADD TO BAG', 'statement-collector-theme' );
}

/**
 * Remove generic WooCommerce chrome only from the focused product context.
 */
function configure_product_page(): void {
	if ( ! is_statement_product() ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}

add_filter( 'woocommerce_product_single_add_to_cart_text', __NAMESPACE__ . '\\product_add_to_cart_label', 10, 2 );
add_action( 'wp', __NAMESPACE__ . '\\configure_product_page' );
