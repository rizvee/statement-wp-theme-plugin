<?php

namespace Statement\Collector\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Coordinates core integrations without owning feature implementations.
 */
final class Plugin {
	/** @var bool */
	private static $booted = false;

	/**
	 * Register the bootstrap once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'plugins_loaded', array( self::class, 'register_integrations' ) );
	}

	/**
	 * Future integrations attach here after WordPress plugins load.
	 */
	public static function register_integrations(): void {
		// Intentionally empty until an approved domain milestone.
	}
}
