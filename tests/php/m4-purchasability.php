<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_filters    = array();
$statement_products   = array();
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

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
}

final class Statement_Test_Product {
	private $id;
	private $parent_id;
	private $release_state;

	public function __construct( string $release_state, int $id = 0, int $parent_id = 0 ) {
		$this->release_state = $release_state;
		$this->id            = $id;
		$this->parent_id     = $parent_id;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_parent_id(): int {
		return $this->parent_id;
	}

	public function get_type(): string {
		return $this->parent_id > 0 ? 'variation' : 'simple';
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->release_state : '';
	}

	public function update_meta_data( string $key, $value ): void {
		if ( '_statement_release_state' === $key ) {
			$this->release_state = (string) $value;
		}
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

$variation = new Statement_Test_Product( '', 101, 100 );
statement_assert_same( 12, $variation->get_stock_quantity(), 'Variation invariant fixture must have positive stock.' );
statement_assert_same( true, $variation->is_in_stock(), 'Variation invariant fixture must be in stock.' );

$statement_products[100] = new Statement_Test_Product( ReleaseState::SOLD_OUT, 100 );
statement_assert_same(
	false,
	Purchasability::filter_purchasable( true, $variation ),
	'Terminal parent release state prevents purchasing its variations when the parent is SOLD_OUT.'
);

$statement_products[100] = new Statement_Test_Product( ReleaseState::ARCHIVED, 100 );
statement_assert_same(
	false,
	Purchasability::filter_purchasable( true, $variation ),
	'Terminal parent release state prevents purchasing its variations when the parent is ARCHIVED.'
);

$statement_products[100] = new Statement_Test_Product( ReleaseState::LIVE, 100 );
statement_assert_same(
	true,
	Purchasability::filter_purchasable( true, $variation ),
	'LIVE parent state must preserve incoming variation purchasability.'
);

$release_owner_test_variation = new Statement_Test_Product( '', 102, 100 );
$statement_products[100]      = new Statement_Test_Product( ReleaseState::UPCOMING, 100 );
statement_assert_same( true, \Statement\Collector\Core\Product\Metadata::set_release_state( $release_owner_test_variation, ReleaseState::LIVE ), 'Variation release writes must resolve to the canonical parent owner.' );
statement_assert_same( ReleaseState::LIVE, $statement_products[100]->get_meta( '_statement_release_state', true ), 'Canonical parent must receive the release-state write.' );
statement_assert_same( '', $release_owner_test_variation->get_meta( '_statement_release_state', true ), 'Variation must not receive duplicate release-state metadata.' );

Purchasability::boot();
statement_assert_same( 1, count( $statement_filters['woocommerce_is_purchasable'] ?? array() ), 'Purchasability filter must register once.' );
statement_assert_same( 2, $statement_filters['woocommerce_is_purchasable'][0]['accepted_args'] ?? null, 'Purchasability filter must receive the product object.' );

fwrite( STDOUT, "PASS: M4 terminal purchasability invariant passed ({$statement_assertions} assertions).\n" );
