<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions    = array();
$statement_filters    = array();
$statement_assertions = 0;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
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
require $root . '/wp-content/themes/statement-collector-theme/inc/product.php';

statement_assert_same( true, class_exists( 'Statement\Collector\Core\Product\Access' ), 'Access class must load without WooCommerce.' );
statement_assert_same( 1, count( $statement_actions['plugins_loaded'] ?? array() ), 'Core plugin bootstrap must remain registered once.' );

statement_run_hook( 'plugins_loaded' );
statement_assert_same( 0, count( $statement_actions['template_redirect'] ?? array() ), 'Direct-product gate must not boot while WooCommerce is absent.' );
statement_assert_same( 0, count( $statement_filters['woocommerce_add_to_cart_validation'] ?? array() ), 'Cart guard must not boot while WooCommerce is absent.' );
statement_assert_same( false, \Statement\Collector\Theme\is_statement_product(), 'Theme product context must degrade safely without WooCommerce.' );
statement_assert_same( false, class_exists( 'WC_Product', false ), 'Absence safety must not instantiate or require WC_Product.' );

fwrite( STDOUT, "PASS: M7 product absence safety passed ({$statement_assertions} assertions).\n" );
