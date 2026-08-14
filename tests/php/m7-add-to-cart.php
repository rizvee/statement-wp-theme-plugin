<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_assertions = 0;
$statement_products   = array();
$statement_notices    = array();

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function wc_has_notice( string $message, string $type = 'success' ): bool {
	global $statement_notices;
	return in_array( array( $message, $type ), $statement_notices, true );
}

function wc_add_notice( string $message, string $type = 'success' ): void {
	global $statement_notices;
	$statement_notices[] = array( $message, $type );
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

final class Statement_M7_Cart_Product {
	private $id;
	private $state;
	private $parent_id;

	public function __construct( int $id, string $state, int $parent_id = 0 ) {
		$this->id        = $id;
		$this->state     = $state;
		$this->parent_id = $parent_id;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return $this->parent_id > 0 ? 'variation' : 'simple';
	}

	public function get_parent_id(): int {
		return $this->parent_id;
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->state : '';
	}

	public function get_stock_quantity(): int {
		return 8;
	}
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Access.php';

use Statement\Collector\Core\Product\Access;
use Statement\Collector\Core\Release\ReleaseState;

$statement_products = array(
	1   => new Statement_M7_Cart_Product( 1, ReleaseState::LIVE ),
	2   => new Statement_M7_Cart_Product( 2, ReleaseState::UPCOMING ),
	3   => new Statement_M7_Cart_Product( 3, ReleaseState::PRIVATE_ACCESS ),
	4   => new Statement_M7_Cart_Product( 4, ReleaseState::SOLD_OUT ),
	5   => new Statement_M7_Cart_Product( 5, ReleaseState::ARCHIVED ),
	100 => new Statement_M7_Cart_Product( 100, ReleaseState::PRIVATE_ACCESS ),
	101 => new Statement_M7_Cart_Product( 101, '', 100 ),
	200 => new Statement_M7_Cart_Product( 200, ReleaseState::LIVE ),
	201 => new Statement_M7_Cart_Product( 201, '', 200 ),
);

statement_assert_same( true, Access::validate_add_to_cart( true, 1 ), 'LIVE simple product must preserve valid Woo validation.' );
statement_assert_same( false, Access::validate_add_to_cart( false, 1 ), 'LIVE must never convert false Woo validation to true.' );
statement_assert_same( false, Access::validate_add_to_cart( true, 2 ), 'UPCOMING simple product must be rejected.' );
statement_assert_same( false, Access::validate_add_to_cart( true, 3 ), 'PRIVATE_ACCESS simple product must be rejected.' );
statement_assert_same( false, Access::validate_add_to_cart( true, 4 ), 'SOLD_OUT simple product must be rejected.' );
statement_assert_same( false, Access::validate_add_to_cart( true, 5 ), 'ARCHIVED simple product must be rejected.' );

statement_assert_same( 8, $statement_products[101]->get_stock_quantity(), 'Variation fixture must retain positive stock.' );
statement_assert_same( false, Access::validate_add_to_cart( true, 100, 1, 101, array() ), 'PRIVATE_ACCESS parent must reject its variation.' );
statement_assert_same( true, Access::validate_add_to_cart( true, 200, 1, 201, array() ), 'LIVE parent must preserve valid variation validation.' );
statement_assert_same( false, Access::validate_add_to_cart( true, 999 ), 'Unknown crafted product request must fail closed.' );

statement_assert_same( 1, count( $statement_notices ), 'Repeated rejection must add one generic error notice.' );
statement_assert_same( 'This piece is not currently available for purchase.', $statement_notices[0][0] ?? null, 'Rejection notice must remain lifecycle-neutral.' );
statement_assert_same( 'error', $statement_notices[0][1] ?? null, 'Rejection notice must use WooCommerce error semantics.' );

fwrite( STDOUT, "PASS: M7 Add-to-Cart protection passed ({$statement_assertions} assertions).\n" );
