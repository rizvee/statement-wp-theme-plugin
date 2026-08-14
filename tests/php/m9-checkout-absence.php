<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions       = array();
$statement_filters       = array();
$statement_removed_hooks = array();
$statement_assertions    = 0;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function remove_action( string $hook, $callback, int $priority = 10 ): bool {
	global $statement_removed_hooks;
	$statement_removed_hooks[] = compact( 'hook', 'callback', 'priority' );
	return true;
}

function __( string $text, string $domain = '' ): string {
	return $text;
}

function statement_run_action( string $hook ): void {
	global $statement_actions;
	foreach ( $statement_actions[ $hook ] ?? array() as $registration ) {
		call_user_func( $registration['callback'] );
	}
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

$root = dirname( __DIR__, 2 );

statement_assert_same( false, class_exists( 'WooCommerce', false ), 'WooCommerce must be absent for this fixture.' );
statement_assert_same( false, function_exists( 'WC' ), 'Woo singleton must be absent for this fixture.' );
statement_assert_same( false, class_exists( 'WC_Checkout', false ), 'WC_Checkout must not be fabricated.' );

require $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php';
require $root . '/wp-content/themes/statement-collector-theme/inc/checkout.php';

statement_assert_same( true, class_exists( 'Statement\Collector\Core\Cart\Integrity' ), 'Cart Integrity must load without WooCommerce.' );
statement_assert_same( false, \Statement\Collector\Theme\is_statement_checkout(), 'Checkout helper must fail safely without WooCommerce conditionals.' );

statement_run_action( 'plugins_loaded' );
statement_run_action( 'wp' );

statement_assert_same( 0, count( $statement_actions['woocommerce_check_cart_items'] ?? array() ), 'Cart Integrity must not boot while WooCommerce is absent.' );
statement_assert_same( array(), $statement_removed_hooks, 'Checkout presentation hooks must remain untouched outside a normal checkout.' );
statement_assert_same( 1, count( $statement_filters['woocommerce_order_button_text'] ?? array() ), 'Order button filter may register safely without executing WooCommerce.' );

fwrite( STDOUT, "PASS: M9 checkout absence safety passed ({$statement_assertions} assertions).\n" );
