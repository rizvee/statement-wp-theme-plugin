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
