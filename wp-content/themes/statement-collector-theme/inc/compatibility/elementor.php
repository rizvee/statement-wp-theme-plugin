<?php
/**
 * Elementor Theme Locations Compatibility Adapter.
 *
 * Implements official Elementor Pro Theme Builder locations support.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class Elementor {
	/**
	 * Boot Elementor compatibility.
	 */
	public static function boot(): void {
		add_action( 'elementor/theme/register_locations', array( self::class, 'register_locations' ) );
	}

	/**
	 * Register official Elementor theme locations.
	 *
	 * @param object $manager Elementor theme locations manager.
	 */
	public static function register_locations( $manager ): void {
		if ( ! is_object( $manager ) || ! method_exists( $manager, 'register_location' ) ) {
			return;
		}

		$manager->register_location(
			'header',
			array(
				'hook'         => 'statement_theme_header',
				'remove_hooks' => array( 'Statement\Collector\Theme\Hooks::before_header' ),
				'is_core'      => true,
			)
		);

		$manager->register_location(
			'footer',
			array(
				'hook'         => 'statement_theme_footer',
				'remove_hooks' => array( 'Statement\Collector\Theme\Hooks::before_footer' ),
				'is_core'      => true,
			)
		);

		$manager->register_location(
			'single',
			array(
				'is_core' => true,
			)
		);

		$manager->register_location(
			'archive',
			array(
				'is_core' => true,
			)
		);
	}

	/**
	 * Render header location or fallback.
	 *
	 * @return bool True if location was rendered by Elementor.
	 */
	public static function do_header(): bool {
		if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Render footer location or fallback.
	 *
	 * @return bool True if location was rendered by Elementor.
	 */
	public static function do_footer(): bool {
		if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
			return true;
		}

		return false;
	}
}
