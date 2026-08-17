<?php
/**
 * Caching & Optimization Plugins Compatibility Adapter.
 *
 * Ensures compatibility with WP Rocket, LiteSpeed Cache, Page Optimize, and Cloudflare.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class Caching {
	/**
	 * Boot caching compatibility.
	 */
	public static function boot(): void {
		add_filter( 'rocket_excluded_inline_js_content', array( self::class, 'exclude_inline_scripts' ) );
	}

	/**
	 * Exclude critical inline tokens from minification/deferral conflicts.
	 *
	 * @param array $excluded Excluded JS strings.
	 * @return array
	 */
	public static function exclude_inline_scripts( array $excluded ): array {
		$excluded[] = 'statement-design-tokens';
		return $excluded;
	}
}
