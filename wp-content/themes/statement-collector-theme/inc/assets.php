<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the global frontend stylesheets.
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
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
