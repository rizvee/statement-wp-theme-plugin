<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_test_hooks        = array();
$statement_test_filters      = array();
$statement_test_supports     = array();
$statement_test_text_domains = array();
$statement_test_styles       = array();
$statement_test_scripts      = array();
$statement_test_script_data  = array();
$statement_test_menus        = array();

function add_action( string $hook, $callback ): void {
	global $statement_test_hooks;
	$statement_test_hooks[ $hook ][] = $callback;
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_test_filters;
	$statement_test_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_theme_support( string $feature, ...$arguments ): void {
	global $statement_test_supports;
	$statement_test_supports[ $feature ] = $arguments;
}

function load_theme_textdomain( string $domain, string $path ): void {
	global $statement_test_text_domains;
	$statement_test_text_domains[ $domain ] = $path;
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function register_nav_menus( array $locations ): void {
	global $statement_test_menus;
	$statement_test_menus = array_merge( $statement_test_menus, $locations );
}

function get_template_directory(): string {
	return dirname( __DIR__, 2 ) . '/wp-content/themes/statement-collector-theme';
}

function trailingslashit( string $path ): string {
	return rtrim( $path, '/\\' ) . '/';
}

function get_theme_file_uri( string $path = '' ): string {
	return 'https://example.test/wp-content/themes/statement-collector-theme/' . ltrim( $path, '/' );
}

function wp_enqueue_style( string $handle, string $source, array $dependencies = array(), $version = false ): void {
	global $statement_test_styles;
	$statement_test_styles[ $handle ] = array(
		'source'       => $source,
		'dependencies' => $dependencies,
		'version'      => $version,
	);
}

function wp_enqueue_script( string $handle, string $source, array $dependencies = array(), $version = false, $in_footer = false ): void {
	global $statement_test_scripts;
	$statement_test_scripts[ $handle ] = array(
		'source'       => $source,
		'dependencies' => $dependencies,
		'version'      => $version,
		'in_footer'    => $in_footer,
	);
}

function wp_script_add_data( string $handle, string $key, $value ): void {
	global $statement_test_script_data;
	$statement_test_script_data[ $handle ][ $key ] = $value;
}

function statement_test_assert( bool $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function statement_test_run_hook( string $hook ): void {
	global $statement_test_hooks;
	foreach ( $statement_test_hooks[ $hook ] ?? array() as $callback ) {
		call_user_func( $callback );
	}
}

$root = dirname( __DIR__, 2 );

statement_test_assert( ! class_exists( 'WooCommerce', false ), 'WooCommerce must be absent before bootstrap.' );

require $root . '/wp-content/themes/statement-collector-theme/functions.php';
require $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php';

statement_test_assert( isset( $statement_test_hooks['after_setup_theme'] ), 'Theme setup hook was not registered.' );
statement_test_assert( 2 === count( $statement_test_hooks['after_setup_theme'] ), 'Expected two theme setup callbacks.' );
statement_test_assert( isset( $statement_test_hooks['plugins_loaded'] ), 'Plugin integration hook was not registered.' );
statement_test_assert( 1 === count( $statement_test_hooks['plugins_loaded'] ), 'Plugin must bootstrap once.' );
statement_test_assert( isset( $statement_test_hooks['wp_enqueue_scripts'] ), 'Theme asset hook was not registered.' );
statement_test_assert( 1 === count( $statement_test_hooks['wp_enqueue_scripts'] ), 'Expected one theme asset callback.' );

\Statement\Collector\Core\Plugin::boot();
statement_test_assert( 1 === count( $statement_test_hooks['plugins_loaded'] ), 'Repeated plugin bootstrap registered duplicate hooks.' );

statement_test_run_hook( 'after_setup_theme' );
statement_test_run_hook( 'wp_enqueue_scripts' );
statement_test_run_hook( 'plugins_loaded' );

statement_test_assert( isset( $statement_test_supports['title-tag'] ), 'Title support was not registered.' );
statement_test_assert( isset( $statement_test_supports['post-thumbnails'] ), 'Featured-image support was not registered.' );
statement_test_assert( isset( $statement_test_supports['custom-logo'] ), 'Custom-logo support was not registered.' );
statement_test_assert( isset( $statement_test_supports['html5'] ), 'HTML5 support was not registered.' );
statement_test_assert( isset( $statement_test_supports['woocommerce'] ), 'WooCommerce presentation support was not registered.' );
statement_test_assert( isset( $statement_test_menus['primary'] ), 'Primary navigation location was not registered.' );
statement_test_assert( isset( $statement_test_menus['footer'] ), 'Footer navigation location was not registered.' );
statement_test_assert( isset( $statement_test_text_domains['statement-collector-theme'] ), 'Theme text domain was not loaded.' );
statement_test_assert( isset( $statement_test_styles['statement-collector-base'] ), 'Base stylesheet was not enqueued.' );
statement_test_assert( isset( $statement_test_styles['statement-collector-layout'] ), 'Layout stylesheet was not enqueued.' );
statement_test_assert( isset( $statement_test_styles['statement-collector-header'] ), 'Header stylesheet was not enqueued.' );
statement_test_assert( isset( $statement_test_styles['statement-collector-footer'] ), 'Footer stylesheet was not enqueued.' );
statement_test_assert( isset( $statement_test_scripts['statement-collector-navigation'] ), 'Navigation script was not enqueued.' );
statement_test_assert( array( 'statement-collector-base' ) === $statement_test_styles['statement-collector-layout']['dependencies'], 'Layout stylesheet dependency is incorrect.' );
statement_test_assert( STATEMENT_COLLECTOR_THEME_VERSION === $statement_test_styles['statement-collector-base']['version'], 'Stylesheet version must use the theme version.' );
statement_test_assert( true === $statement_test_scripts['statement-collector-navigation']['in_footer'], 'Navigation script must load in the footer.' );
statement_test_assert( 'defer' === $statement_test_script_data['statement-collector-navigation']['strategy'], 'Navigation script must use the defer strategy.' );
statement_test_assert( null === \Statement\Collector\Theme\get_account_url(), 'Account helper must be unavailable without WooCommerce.' );
statement_test_assert( null === \Statement\Collector\Theme\get_cart_url(), 'Cart helper must be unavailable without WooCommerce.' );
statement_test_assert( ! class_exists( 'WooCommerce', false ), 'Bootstrap must not require or create WooCommerce.' );

fwrite( STDOUT, "PASS: M1 bootstrap smoke passed.\n" );
