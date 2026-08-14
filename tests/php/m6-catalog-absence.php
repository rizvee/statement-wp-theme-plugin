<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions    = array();
$statement_filters    = array();
$statement_removals   = array();
$statement_assertions = 0;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function remove_action( string $hook, $callback, int $priority = 10 ): void {
	global $statement_removals;
	$statement_removals[] = compact( 'hook', 'callback', 'priority' );
}

function statement_assert_same( $expected, $actual, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

function statement_run_hook( string $hook ): void {
	global $statement_actions;
	foreach ( $statement_actions[ $hook ] ?? array() as $registration ) {
		call_user_func( $registration['callback'] );
	}
}

$root = dirname( __DIR__, 2 );

statement_assert_same( false, class_exists( 'WooCommerce', false ), 'WooCommerce must be absent for this fixture.' );

require $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php';
require $root . '/wp-content/themes/statement-collector-theme/inc/catalog.php';

statement_assert_same( true, class_exists( 'Statement\Collector\Core\Catalog\Visibility' ), 'Visibility class must load without WooCommerce.' );
statement_assert_same( 1, count( $statement_actions['plugins_loaded'] ?? array() ), 'Core plugin must retain one bootstrap hook.' );
statement_assert_same( 1, count( $statement_actions['wp'] ?? array() ), 'Theme catalog helper must register one contextual setup hook.' );

statement_run_hook( 'plugins_loaded' );
statement_assert_same( 0, count( $statement_actions['woocommerce_product_query'] ?? array() ), 'Woo query hook must not register while WooCommerce is absent.' );

statement_run_hook( 'wp' );
statement_assert_same( array(), $statement_removals, 'Missing WooCommerce/catalog conditionals must not trigger UI hook mutations.' );
statement_assert_same( false, class_exists( 'WC_Query', false ), 'Absence safety must not instantiate or require WC_Query.' );

fwrite( STDOUT, "PASS: M6 catalog absence safety passed ({$statement_assertions} assertions).\n" );
