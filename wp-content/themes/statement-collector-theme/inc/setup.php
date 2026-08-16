<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Register the theme's foundational WordPress support.
 */
function setup(): void {
	load_theme_textdomain( 'statement-collector-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'statement-collector-theme' ),
			'footer'  => __( 'Footer Navigation', 'statement-collector-theme' ),
		)
	);
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );

/**
 * Protect Statement theme's ownership of the front page.
 * Prevents third-party plugins or page builders from hijacking front-page.php.
 *
 * @param string $template Current resolved template path.
 * @return string Filtered template path.
 */
function protect_front_page_template( string $template ): string {
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		$theme_front = locate_template( array( 'front-page.php' ) );
		if ( $theme_front && is_string( $theme_front ) && '' !== $theme_front ) {
			return $theme_front;
		}
	}
	return $template;
}
add_filter( 'template_include', __NAMESPACE__ . '\\protect_front_page_template', 9999 );
