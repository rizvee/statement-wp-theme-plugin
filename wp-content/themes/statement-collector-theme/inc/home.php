<?php

namespace Statement\Collector\Theme;

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

const HOME_CANDIDATE_LIMIT = 24;
const HOME_PRODUCT_LIMIT   = 4;

/**
 * Select one deterministic public Drop and its first eligible products.
 *
 * @return array{drop: object|null, products: array}
 */
function get_home_release_data(): array {
	$empty = array(
		'drop'     => null,
		'products' => array(),
	);

	if (
		! class_exists( 'Statement\Collector\Core\PublicApi' )
		|| ! function_exists( 'wc_get_products' )
	) {
		return $empty;
	}

	$candidates = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'visible',
			'limit'      => HOME_CANDIDATE_LIMIT,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'return'     => 'objects',
		)
	);
	if ( ! is_array( $candidates ) ) {
		return $empty;
	}

	$selected_drop = null;
	$products      = array();

	foreach ( $candidates as $product ) {
		if ( ! is_object( $product ) || ! PublicApi::is_publicly_live( $product ) ) {
			continue;
		}

		$sku  = is_callable( array( $product, 'get_sku' ) ) ? (string) $product->get_sku() : '';
		$name = is_callable( array( $product, 'get_name' ) ) ? (string) $product->get_name() : '';
		if ( 0 === strpos( $sku, 'TEST-' ) || 0 === strpos( $name, 'TEST-' ) ) {
			continue;
		}

		$drop = PublicApi::get_drop( $product );
		if ( ! is_object( $drop ) || ! isset( $drop->term_id ) || (int) $drop->term_id < 1 ) {
			continue;
		}

		if ( isset( $drop->slug ) && 0 === strpos( (string) $drop->slug, 'test-' ) ) {
			continue;
		}

		if ( null === $selected_drop ) {
			$selected_drop = $drop;
		}

		if ( (int) $selected_drop->term_id !== (int) $drop->term_id ) {
			continue;
		}

		$products[] = $product;
		if ( HOME_PRODUCT_LIMIT === count( $products ) ) {
			break;
		}
	}

	return array(
		'drop'     => $selected_drop,
		'products' => $products,
	);
}

/**
 * Resolve a public Drop URL without manufacturing a fallback destination.
 *
 * @param object|null $drop Statement Drop term.
 */
function get_home_drop_url( $drop ): ?string {
	if ( ! is_object( $drop ) || ! function_exists( 'get_term_link' ) ) {
		return null;
	}

	$url = get_term_link( $drop );
	if ( ( function_exists( 'is_wp_error' ) && is_wp_error( $url ) ) || ! is_string( $url ) || '' === $url ) {
		return null;
	}

	return $url;
}

/**
 * Resolve the explicitly recognized published Archive page when present.
 */
function get_home_archive_url(): ?string {
	if (
		! function_exists( 'get_page_by_path' )
		|| ! function_exists( 'get_post_status' )
		|| ! function_exists( 'get_permalink' )
	) {
		return null;
	}

	$page = get_page_by_path( 'archive', OBJECT, array( 'page' ) );
	if ( ! is_object( $page ) || ! isset( $page->ID ) || 'publish' !== get_post_status( (int) $page->ID ) ) {
		return null;
	}

	$url = get_permalink( (int) $page->ID );

	return is_string( $url ) && '' !== $url ? $url : null;
}

/**
 * Whether the static page contains native editorial content.
 */
function has_home_editorial_content( int $page_id ): bool {
	if ( $page_id < 1 || ! function_exists( 'get_post_field' ) ) {
		return false;
	}

	$content = get_post_field( 'post_content', $page_id );
	if ( ! is_string( $content ) ) {
		return false;
	}

	$content = preg_replace( '/<!--.*?-->/s', '', $content );
	if ( ! is_string( $content ) || '' === trim( $content ) ) {
		return false;
	}

	$visible_text = function_exists( 'wp_strip_all_tags' )
		? wp_strip_all_tags( $content )
		: strip_tags( $content );

	if ( '' !== trim( $visible_text ) ) {
		return true;
	}

	return 1 === preg_match( '/<(?:img|picture|video|audio|iframe|embed|object|svg|canvas)\b/i', $content );
}
