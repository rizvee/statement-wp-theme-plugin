<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the global frontend assets.
 */
function enqueue_assets(): void {
	wp_enqueue_style(
		'statement-collector-base',
		get_theme_file_uri( 'assets/css/base.css' ),
		array(),
		STATEMENT_COLLECTOR_THEME_VERSION
	);

	wp_enqueue_style(
		'statement-collector-layout',
		get_theme_file_uri( 'assets/css/layout.css' ),
		array( 'statement-collector-base' ),
		STATEMENT_COLLECTOR_THEME_VERSION
	);

	wp_enqueue_style(
		'statement-collector-header',
		get_theme_file_uri( 'assets/css/header.css' ),
		array( 'statement-collector-base', 'statement-collector-layout' ),
		STATEMENT_COLLECTOR_THEME_VERSION
	);

	wp_enqueue_style(
		'statement-collector-footer',
		get_theme_file_uri( 'assets/css/footer.css' ),
		array( 'statement-collector-base', 'statement-collector-layout' ),
		STATEMENT_COLLECTOR_THEME_VERSION
	);

	wp_enqueue_script(
		'statement-collector-navigation',
		get_theme_file_uri( 'assets/js/navigation.js' ),
		array(),
		STATEMENT_COLLECTOR_THEME_VERSION,
		true
	);
	wp_script_add_data( 'statement-collector-navigation', 'strategy', 'defer' );
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
