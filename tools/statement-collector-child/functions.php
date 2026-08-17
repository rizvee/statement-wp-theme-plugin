<?php
/**
 * Statement Collector Child Theme Functions.
 *
 * Enqueues parent theme styles and provides a clean hook foundation for customizations.
 *
 * @package Statement_Collector_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue child theme styles after parent styles.
 */
function statement_child_enqueue_styles(): void {
	wp_enqueue_style(
		'statement-collector-child-style',
		get_stylesheet_uri(),
		array( 'statement-collector-base' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'statement_child_enqueue_styles', 30 );

/*
 * Example: Custom hook into Statement layout:
 *
 * add_action( 'statement_theme_after_header', function() {
 *     // Custom campaign banner
 * } );
 */
