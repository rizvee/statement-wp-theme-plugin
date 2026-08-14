<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Handles SEO noindex headers, sitemap exclusion, and response header hardening for private access contexts.
 */
final class CacheHardening {
	public static function boot(): void {
		add_action( 'send_headers', array( self::class, 'apply_response_headers' ) );
		add_filter( 'wp_robots', array( self::class, 'apply_robots_noindex' ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( self::class, 'exclude_private_products_from_sitemap' ) );
	}

	/**
	 * Adds noindex, nofollow directives for token / private requests.
	 */
	public static function apply_robots_noindex( array $robots ): array {
		$is_token_request = isset( $_GET['statement_token'] ) || isset( $_GET['token'] ) || ( false !== strpos( $_SERVER['REQUEST_URI'] ?? '', '/statement-unsubscribe' ) );
		if ( $is_token_request ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}

	/**
	 * Sets Cache-Control: private, no-store, no-cache, max-age=0 on token endpoints & private access pages.
	 */
	public static function apply_response_headers(): void {
		if ( headers_sent() ) {
			return;
		}

		$is_token_request = isset( $_GET['statement_token'] ) || isset( $_GET['token'] ) || ( false !== strpos( $_SERVER['REQUEST_URI'] ?? '', '/statement-unsubscribe' ) );
		if ( $is_token_request ) {
			header( 'Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			header( 'Vary: Cookie' );
		}
	}

	/**
	 * Excludes PRIVATE_ACCESS, UPCOMING, SOLD_OUT, and ARCHIVED products from native WordPress XML sitemaps.
	 */
	public static function exclude_private_products_from_sitemap( array $args ): array {
		if ( isset( $args['post_type'] ) && 'product' === $args['post_type'] ) {
			$args['meta_query'][] = array(
				'key'     => Metadata::RELEASE_STATE_KEY,
				'value'   => ReleaseState::LIVE,
				'compare' => '=',
			);
		}
		return $args;
	}
}
