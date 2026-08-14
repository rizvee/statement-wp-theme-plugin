<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Return the WooCommerce account URL when its public API is available.
 */
function get_account_url(): ?string {
	if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'wc_get_page_permalink' ) ) {
		return null;
	}

	if ( wc_get_page_id( 'myaccount' ) < 1 ) {
		return null;
	}

	$url = wc_get_page_permalink( 'myaccount' );

	return is_string( $url ) && '' !== $url ? $url : null;
}

/**
 * Return the WooCommerce cart URL when its public API is available.
 */
function get_cart_url(): ?string {
	if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'wc_get_cart_url' ) ) {
		return null;
	}

	if ( wc_get_page_id( 'cart' ) < 1 ) {
		return null;
	}

	$url = wc_get_cart_url();

	return is_string( $url ) && '' !== $url ? $url : null;
}

/**
 * Render the native custom logo or an escaped site-name fallback.
 */
function render_site_brand(): void {
	if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="statement-brand__fallback" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
	</a>
	<?php
}
