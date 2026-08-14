<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_filters    = array();
$statement_assertions = 0;

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = array(
		'callback'      => $callback,
		'priority'      => $priority,
		'accepted_args' => $accepted_args,
	);
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

final class Statement_Test_Product {
	private $release_state;

	public function __construct( string $release_state ) {
		$this->release_state = $release_state;
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->release_state : '';
	}

	public function get_stock_quantity(): int {
		return 12;
	}

	public function is_in_stock(): bool {
		return true;
	}
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Release/Purchasability.php';

use Statement\Collector\Core\Release\Purchasability;
use Statement\Collector\Core\Release\ReleaseState;

$sold_out = new Statement_Test_Product( ReleaseState::SOLD_OUT );

statement_assert_same( 12, $sold_out->get_stock_quantity(), 'Invariant fixture must have positive stock.' );
statement_assert_same( true, $sold_out->is_in_stock(), 'Invariant fixture must be in stock.' );
statement_assert_same( false, Purchasability::filter_purchasable( true, $sold_out ), 'Terminal Statement release state overrides WooCommerce purchasability for SOLD_OUT.' );
statement_assert_same( false, Purchasability::filter_purchasable( true, new Statement_Test_Product( ReleaseState::ARCHIVED ) ), 'Terminal Statement release state overrides WooCommerce purchasability for ARCHIVED.' );
statement_assert_same( true, Purchasability::filter_purchasable( true, new Statement_Test_Product( ReleaseState::LIVE ) ), 'LIVE must preserve WooCommerce purchasability.' );
statement_assert_same( true, Purchasability::filter_purchasable( true, new Statement_Test_Product( ReleaseState::UPCOMING ) ), 'UPCOMING must preserve WooCommerce purchasability.' );
statement_assert_same( true, Purchasability::filter_purchasable( true, new Statement_Test_Product( ReleaseState::PRIVATE_ACCESS ) ), 'PRIVATE_ACCESS policy remains out of scope and must preserve the incoming result.' );
statement_assert_same( false, Purchasability::filter_purchasable( false, new Statement_Test_Product( ReleaseState::LIVE ) ), 'M4 must not override a false WooCommerce result.' );
statement_assert_same( true, Purchasability::filter_purchasable( true, new Statement_Test_Product( 'INVALID' ) ), 'Invalid persisted state must resolve safely without inventing a commerce lock.' );

Purchasability::boot();
statement_assert_same( 1, count( $statement_filters['woocommerce_is_purchasable'] ?? array() ), 'Purchasability filter must register once.' );
statement_assert_same( 2, $statement_filters['woocommerce_is_purchasable'][0]['accepted_args'] ?? null, 'Purchasability filter must receive the product object.' );

fwrite( STDOUT, "PASS: M4 terminal purchasability invariant passed ({$statement_assertions} assertions).\n" );
