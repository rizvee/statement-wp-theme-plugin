<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions    = array();
$statement_filters    = array();
$statement_assertions = 0;
$statement_wc         = null;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function WC() {
	global $statement_wc;
	return $statement_wc;
}

function __( string $text, string $domain = '' ): string {
	return $text;
}

function is_cart(): bool {
	return false;
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

final class Statement_M8_Count_Cart {
	private $count;

	public function __construct( $count ) {
		$this->count = $count;
	}

	public function get_cart_contents_count() {
		return $this->count;
	}
}

$root = dirname( __DIR__, 2 );
require $root . '/wp-content/themes/statement-collector-theme/inc/cart.php';

use function Statement\Collector\Theme\get_bag_count;
use function Statement\Collector\Theme\get_bag_label;

statement_assert_same( 0, get_bag_count(), 'Woo object unavailable must return zero.' );

$statement_wc = (object) array( 'cart' => null );
statement_assert_same( 0, get_bag_count(), 'Woo object without cart must return zero.' );

foreach ( array( 0 => 0, 1 => 1, 4 => 4, -3 => 0, 'invalid' => 0 ) as $input => $expected ) {
	$statement_wc = (object) array( 'cart' => new Statement_M8_Count_Cart( $input ) );
	statement_assert_same( $expected, get_bag_count(), "Unexpected safe Bag count for {$input}." );
}

$statement_wc = (object) array( 'cart' => new Statement_M8_Count_Cart( 0 ) );
statement_assert_same( 'BAG', get_bag_label(), 'Zero count must remain hidden.' );
$statement_wc = (object) array( 'cart' => new Statement_M8_Count_Cart( 4 ) );
statement_assert_same( 'BAG (4)', get_bag_label(), 'Positive count must be shown.' );

fwrite( STDOUT, "PASS: M8 Bag count passed ({$statement_assertions} assertions).\n" );
