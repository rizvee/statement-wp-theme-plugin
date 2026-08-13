<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_test_hooks        = array();
$statement_test_supports     = array();
$statement_test_text_domains = array();

function add_action( string $hook, $callback ): void {
	global $statement_test_hooks;
	$statement_test_hooks[ $hook ][] = $callback;
}

function add_theme_support( string $feature, ...$arguments ): void {
	global $statement_test_supports;
	$statement_test_supports[ $feature ] = $arguments;
}

function load_theme_textdomain( string $domain, string $path ): void {
	global $statement_test_text_domains;
	$statement_test_text_domains[ $domain ] = $path;
}

function get_template_directory(): string {
	return dirname( __DIR__, 2 ) . '/wp-content/themes/statement-collector-theme';
}

function trailingslashit( string $path ): string {
	return rtrim( $path, '/\\' ) . '/';
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

\Statement\Collector\Core\Plugin::boot();
statement_test_assert( 1 === count( $statement_test_hooks['plugins_loaded'] ), 'Repeated plugin bootstrap registered duplicate hooks.' );

statement_test_run_hook( 'after_setup_theme' );
statement_test_run_hook( 'plugins_loaded' );

statement_test_assert( isset( $statement_test_supports['title-tag'] ), 'Title support was not registered.' );
statement_test_assert( isset( $statement_test_supports['post-thumbnails'] ), 'Featured-image support was not registered.' );
statement_test_assert( isset( $statement_test_supports['html5'] ), 'HTML5 support was not registered.' );
statement_test_assert( isset( $statement_test_supports['woocommerce'] ), 'WooCommerce presentation support was not registered.' );
statement_test_assert( isset( $statement_test_text_domains['statement-collector-theme'] ), 'Theme text domain was not loaded.' );
statement_test_assert( ! class_exists( 'WooCommerce', false ), 'Bootstrap must not require or create WooCommerce.' );

fwrite( STDOUT, "PASS: M1 bootstrap smoke passed.\n" );
