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
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );
