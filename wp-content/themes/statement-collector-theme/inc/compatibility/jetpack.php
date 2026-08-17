<?php
/**
 * Jetpack Compatibility Adapter.
 *
 * Configures Jetpack features gracefully without polluting public markup.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class Jetpack {
	/**
	 * Boot Jetpack compatibility.
	 */
	public static function boot(): void {
		add_theme_support( 'responsive-videos' );
	}
}
