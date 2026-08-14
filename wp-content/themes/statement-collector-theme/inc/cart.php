<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Return a safe server-rendered WooCommerce cart quantity.
 */
function get_bag_count(): int {
	if ( ! function_exists( 'WC' ) ) {
		return 0;
	}

	$woocommerce = WC();
	$cart        = is_object( $woocommerce ) && isset( $woocommerce->cart ) ? $woocommerce->cart : null;
	if ( ! is_object( $cart ) || ! method_exists( $cart, 'get_cart_contents_count' ) ) {
		return 0;
	}

	$count = $cart->get_cart_contents_count();
	if ( ! is_numeric( $count ) ) {
		return 0;
	}

	return max( 0, (int) $count );
}

/**
 * Return the accessible Bag label, hiding a zero quantity.
 */
function get_bag_label(): string {
	$count = get_bag_count();
	if ( $count < 1 ) {
		return __( 'BAG', 'statement-collector-theme' );
	}

	/* translators: %d is the total item quantity in the customer's Bag. */
	return sprintf( __( 'BAG (%d)', 'statement-collector-theme' ), $count );
}

/**
 * Whether the current request is WooCommerce's Cart page.
 */
function is_statement_cart(): bool {
	return function_exists( 'is_cart' ) && is_cart();
}

/**
 * Keep the classic Cart composition restrained without altering stored data.
 */
function configure_cart_presentation(): void {
	if ( ! is_statement_cart() ) {
		return;
	}

	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
	add_action( 'woocommerce_cart_is_empty', __NAMESPACE__ . '\\render_empty_bag_message', 10 );
}

/**
 * Render the restrained empty Bag heading through the native empty-cart hook.
 */
function render_empty_bag_message(): void {
	?>
	<section class="statement-cart-empty" aria-labelledby="statement-cart-empty-title">
		<h1 id="statement-cart-empty-title"><?php esc_html_e( 'YOUR BAG IS EMPTY', 'statement-collector-theme' ); ?></h1>
	</section>
	<?php
}

/**
 * Rename only the native empty-cart Shop action.
 */
function filter_return_to_shop_text( string $text ): string {
	return is_statement_cart() ? __( 'CONTINUE SHOPPING', 'statement-collector-theme' ) : $text;
}

add_action( 'wp', __NAMESPACE__ . '\\configure_cart_presentation' );
add_filter( 'woocommerce_return_to_shop_text', __NAMESPACE__ . '\\filter_return_to_shop_text' );
