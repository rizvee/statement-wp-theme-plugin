<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

// Require Modular Components
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/design-tokens.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/hooks.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/page-meta.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/woocommerce.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/woo-blocks.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/elementor.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/gutenberg.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/seo.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/jetpack.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/forms.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/compatibility/caching.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/admin/health.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/admin/options-export.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/admin/setup-screen.php';

/**
 * Register the theme's foundational WordPress support.
 */
function setup(): void {
	load_theme_textdomain( 'statement-collector-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
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

	// Boot Modular Framework Subsystems
	Hooks::boot();
	PageMeta::boot();
	Compatibility\WooCommerce::boot();
	Compatibility\WooBlocks::boot();
	Compatibility\Elementor::boot();
	Compatibility\Gutenberg::boot();
	Compatibility\Jetpack::boot();
	Compatibility\Forms::boot();
	Compatibility\Caching::boot();

	if ( function_exists( 'is_admin' ) && is_admin() ) {
		Admin\SetupScreen::boot();
	}
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );

/**
 * Route front page template based on the configured front page renderer.
 *
 * Modes:
 * - 'statement': Curated Statement Collector Piece editorial homepage (default).
 * - 'content': Standard static page content renderer, enabling Elementor / Gutenberg builder workflows.
 *
 * @param string $template Current resolved template path.
 * @return string Filtered template path.
 */
function protect_front_page_template( string $template ): string {
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		$renderer = (string) get_theme_mod( 'statement_front_page_renderer', 'statement' );
		if ( 'content' === $renderer ) {
			$page_template = locate_template( array( 'page.php', 'index.php' ) );
			if ( $page_template && is_string( $page_template ) && '' !== $page_template ) {
				return $page_template;
			}
		} else {
			$theme_front = locate_template( array( 'front-page.php' ) );
			if ( $theme_front && is_string( $theme_front ) && '' !== $theme_front ) {
				return $theme_front;
			}
		}
	}
	return $template;
}
add_filter( 'template_include', __NAMESPACE__ . '\\protect_front_page_template', 9999 );
