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

function __( string $text, string $domain = '' ): string {
	return $text;
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
statement_assert_same( false, function_exists( 'WC' ), 'Woo cart singleton must be absent for this fixture.' );

require $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php';
require $root . '/wp-content/themes/statement-collector-theme/inc/cart.php';

statement_assert_same( true, class_exists( 'Statement\Collector\Core\Cart\Integrity' ), 'Cart Integrity must load without WooCommerce.' );
statement_assert_same( 0, \Statement\Collector\Theme\get_bag_count(), 'Bag count must fail safely without WooCommerce.' );
statement_assert_same( 'BAG', \Statement\Collector\Theme\get_bag_label(), 'Bag label must fail safely without WooCommerce.' );

statement_run_hook( 'plugins_loaded' );
statement_assert_same( 0, count( $statement_actions['woocommerce_check_cart_items'] ?? array() ), 'Cart Integrity must not boot while WooCommerce is absent.' );
statement_assert_same( 0, count( $statement_filters['woocommerce_cart_item_is_purchasable'] ?? array() ), 'Session validation must not boot while WooCommerce is absent.' );

fwrite( STDOUT, "PASS: M8 cart absence safety passed ({$statement_assertions} assertions).\n" );
