<?php
/**
 * SEO Plugins Compatibility Adapter.
 *
 * Ensures clean coexistence with Yoast SEO, Rank Math, and All in One SEO
 * by delegating metadata, OpenGraph, and title generation when an active SEO plugin is detected.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class Seo {
	/**
	 * Check whether a dedicated SEO plugin is currently active.
	 *
	 * @return bool True if an SEO plugin manages document meta.
	 */
	public static function has_seo_plugin(): bool {
		return defined( 'WPSEO_VERSION' ) // Yoast SEO
			|| defined( 'RANK_MATH_VERSION' ) // Rank Math
			|| defined( 'AIOSEO_VERSION' ) // All in One SEO
			|| class_exists( 'The_SEO_Framework\Bootstrap' ); // The SEO Framework
	}

	/**
	 * Output fallback meta tags ONLY when no dedicated SEO plugin is active.
	 */
	public static function output_fallback_meta(): void {
		if ( self::has_seo_plugin() ) {
			return;
		}

		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			$description = get_bloginfo( 'description' );
			if ( ! empty( $description ) ) {
				echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
			}
		}
	}
}
