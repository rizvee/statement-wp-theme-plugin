<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request is the public Shop or Statement Drop catalog.
 */
function is_statement_catalog(): bool {
	$is_shop = function_exists( 'is_shop' ) && is_shop();
	$is_drop = function_exists( 'is_tax' ) && is_tax( 'statement_drop' );

	return $is_shop || $is_drop;
}

/**
 * Remove generic catalog chrome while preserving notices and pagination.
 */
function configure_catalog(): void {
	if ( ! is_statement_catalog() ) {
		return;
	}

	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
	add_action( 'woocommerce_no_products_found', __NAMESPACE__ . '\\render_catalog_empty_state', 10 );
}

/**
 * Render the restrained public empty state without revealing hidden products.
 */
function render_catalog_empty_state(): void {
	?>
	<section class="statement-catalog-empty" aria-labelledby="statement-catalog-empty-title">
		<h2 id="statement-catalog-empty-title"><?php esc_html_e( 'NO CURRENT RELEASE', 'statement-collector-theme' ); ?></h2>
	</section>
	<?php
}

add_action( 'wp', __NAMESPACE__ . '\\configure_catalog' );
