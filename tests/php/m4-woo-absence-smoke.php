<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_hooks      = array();
$statement_filters    = array();
$statement_assertions = 0;

function add_action( string $hook, $callback ): void {
	global $statement_hooks;
	$statement_hooks[ $hook ][] = $callback;
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
	global $statement_hooks;
	foreach ( $statement_hooks[ $hook ] ?? array() as $callback ) {
		call_user_func( $callback );
	}
}

$root = dirname( __DIR__, 2 );

statement_assert_same( false, class_exists( 'WooCommerce', false ), 'WooCommerce must be absent for this smoke test.' );

require $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php';

statement_assert_same( true, class_exists( 'Statement\Collector\Core\Release\ReleaseState' ), 'ReleaseState class must load without WooCommerce.' );
statement_assert_same( true, class_exists( 'Statement\Collector\Core\Product\Metadata' ), 'Metadata class must load without WooCommerce.' );
statement_assert_same( true, class_exists( 'Statement\Collector\Core\Product\Admin' ), 'Admin class must load without WooCommerce.' );
statement_assert_same( true, class_exists( 'Statement\Collector\Core\Release\Purchasability' ), 'Purchasability class must load without WooCommerce.' );
statement_assert_same( 1, count( $statement_hooks['plugins_loaded'] ?? array() ), 'Plugin bootstrap must register once.' );

\Statement\Collector\Core\Plugin::boot();
statement_assert_same( 1, count( $statement_hooks['plugins_loaded'] ?? array() ), 'Repeated bootstrap must remain idempotent.' );

statement_run_hook( 'plugins_loaded' );

statement_assert_same( 1, count( $statement_hooks['init'] ?? array() ), 'Drop taxonomy registration must remain safe without WooCommerce.' );
statement_assert_same( 0, count( $statement_hooks['woocommerce_product_options_general_product_data'] ?? array() ), 'WooCommerce admin hooks must not register while WooCommerce is absent.' );
statement_assert_same( 0, count( $statement_hooks['woocommerce_admin_process_product_object'] ?? array() ), 'WooCommerce save hooks must not register while WooCommerce is absent.' );
statement_assert_same( 0, count( $statement_filters['woocommerce_is_purchasable'] ?? array() ), 'WooCommerce filters must not register while WooCommerce is absent.' );
statement_assert_same( false, class_exists( 'WC_Product', false ), 'Bootstrap must not instantiate or require WC_Product.' );

fwrite( STDOUT, "PASS: M4 WooCommerce-absence bootstrap passed ({$statement_assertions} assertions).\n" );
