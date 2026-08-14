<?php

declare(strict_types=1);

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Whether the request is the normal cart-backed classic Checkout screen.
 */
function is_statement_checkout(): bool {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return false;
	}

	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		return false;
	}

	if ( function_exists( 'is_wc_endpoint_url' ) ) {
		if ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Remove only the native coupon-entry prompt from Statement Checkout.
 */
function configure_checkout_presentation(): void {
	if ( ! is_statement_checkout() ) {
		return;
	}

	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}
add_action( 'wp', __NAMESPACE__ . '\\configure_checkout_presentation' );

/**
 * Keep the native WooCommerce order control while using Statement UI copy.
 */
function get_checkout_order_button_text( string $button_text ): string {
	return is_statement_checkout() ? __( 'PLACE ORDER', 'statement-collector-theme' ) : $button_text;
}
add_filter( 'woocommerce_order_button_text', __NAMESPACE__ . '\\get_checkout_order_button_text' );
