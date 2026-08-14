<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions       = array();
$statement_filters       = array();
$statement_products      = array();
$statement_notices       = array();
$statement_assertions    = 0;
$statement_wc            = null;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
}

function WC() {
	global $statement_wc;
	return $statement_wc;
}

function is_admin(): bool {
	return false;
}

function wp_doing_cron(): bool {
	return false;
}

function wp_doing_ajax(): bool {
	return false;
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

final class Statement_M8_Product {
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

	public function get_parent_id(): int {
		return $this->parent_id;
	}

	public function get_type(): string {
		return $this->parent_id > 0 ? 'variation' : 'simple';
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->state : '';
	}

	public function update_meta_data(): void {
		throw new RuntimeException( 'Cart integrity must not mutate release metadata.' );
	}

	public function set_stock_quantity(): void {
		throw new RuntimeException( 'Cart integrity must not mutate stock.' );
	}
}

final class Statement_M8_Cart {
	public $items;
	public $removed = array();

	public function __construct( array $items ) {
		$this->items = $items;
	}

	public function get_cart(): array {
		return $this->items;
	}

	public function remove_cart_item( string $key ): bool {
		if ( ! array_key_exists( $key, $this->items ) ) {
			return false;
		}

		unset( $this->items[ $key ] );
		$this->removed[] = $key;
		return true;
	}
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Cart/Integrity.php';

use Statement\Collector\Core\Cart\Integrity;
use Statement\Collector\Core\Release\ReleaseState;

$live       = new Statement_M8_Product( 1, ReleaseState::LIVE );
$upcoming   = new Statement_M8_Product( 2, ReleaseState::UPCOMING );
$private    = new Statement_M8_Product( 3, ReleaseState::PRIVATE_ACCESS );
$sold       = new Statement_M8_Product( 4, ReleaseState::SOLD_OUT );
$archived   = new Statement_M8_Product( 5, ReleaseState::ARCHIVED );
$invalid    = new Statement_M8_Product( 6, 'INVALID' );
$variation  = new Statement_M8_Product( 101, '', 100 );

statement_assert_same( false, Integrity::filter_session_item( false, 'live', array(), $live ), 'Incoming false must remain false for LIVE.' );
statement_assert_same( true, Integrity::filter_session_item( true, 'live', array(), $live ), 'LIVE must preserve incoming true.' );
statement_assert_same( false, Integrity::filter_session_item( true, 'upcoming', array(), $upcoming ), 'UPCOMING must fail closed.' );
statement_assert_same( false, Integrity::filter_session_item( true, 'private', array(), $private ), 'PRIVATE_ACCESS must fail closed.' );
statement_assert_same( false, Integrity::filter_session_item( true, 'sold', array(), $sold ), 'SOLD_OUT must fail closed.' );
statement_assert_same( false, Integrity::filter_session_item( true, 'archived', array(), $archived ), 'ARCHIVED must fail closed.' );
statement_assert_same( false, Integrity::filter_session_item( true, 'invalid', array(), $invalid ), 'Missing or invalid state must fail closed.' );

$statement_products[100] = new Statement_M8_Product( 100, ReleaseState::LIVE );
statement_assert_same( true, Integrity::filter_session_item( true, 'variation', array(), $variation ), 'Variation with LIVE parent must remain eligible.' );
$statement_products[100] = new Statement_M8_Product( 100, ReleaseState::PRIVATE_ACCESS );
statement_assert_same( false, Integrity::filter_session_item( true, 'variation', array(), $variation ), 'Variation with PRIVATE_ACCESS parent must fail closed.' );

$variation_live     = new Statement_M8_Product( 201, '', 200 );
$variation_archived = new Statement_M8_Product( 301, '', 300 );
$statement_products[200] = new Statement_M8_Product( 200, ReleaseState::LIVE );
$statement_products[300] = new Statement_M8_Product( 300, ReleaseState::ARCHIVED );
$cart = new Statement_M8_Cart(
	array(
		'A' => array( 'data' => $live ),
		'B' => array( 'data' => $private ),
		'C' => array( 'data' => $sold ),
		'D' => array( 'data' => $variation_live ),
		'E' => array( 'data' => $variation_archived ),
	)
);
$statement_wc = (object) array( 'cart' => $cart );

Integrity::reconcile_cart();
statement_assert_same( array( 'A', 'D' ), array_keys( $cart->items ), 'Only canonical LIVE lines must remain.' );
statement_assert_same( array( 'B', 'C', 'E' ), $cart->removed, 'Exactly non-LIVE lines must be removed.' );
statement_assert_same( array( array( 'A piece in your bag is no longer available.', 'notice' ) ), $statement_notices, 'Reconciliation must add one lifecycle-neutral notice.' );

Integrity::reconcile_cart();
statement_assert_same( array( 'A', 'D' ), array_keys( $cart->items ), 'Repeated reconciliation must preserve LIVE lines.' );
statement_assert_same( array( 'B', 'C', 'E' ), $cart->removed, 'Repeated reconciliation must not remove lines twice.' );
statement_assert_same( 1, count( $statement_notices ), 'Repeated reconciliation must not duplicate the lifecycle notice.' );

Integrity::boot();
Integrity::boot();
statement_assert_same( 1, count( $statement_filters['woocommerce_cart_item_is_purchasable'] ?? array() ), 'Session eligibility filter must register once.' );
statement_assert_same( 4, $statement_filters['woocommerce_cart_item_is_purchasable'][0]['accepted_args'] ?? null, 'Session eligibility filter must receive the product.' );
statement_assert_same( 1, count( $statement_actions['woocommerce_check_cart_items'] ?? array() ), 'Current-cart reconciliation must register once.' );
statement_assert_same( 0, $statement_actions['woocommerce_check_cart_items'][0]['priority'] ?? null, 'Statement reconciliation must precede native Woo cart validation.' );
statement_assert_same( 'A piece in your bag is no longer available.', Integrity::filter_removed_message( 'Product-specific copy.', $private ), 'Session removal copy must not reveal product or lifecycle data.' );
statement_assert_same( 'Native Woo message.', Integrity::filter_removed_message( 'Native Woo message.', $live ), 'Unrelated native removal copy must remain intact.' );
statement_assert_same( 'Another Woo error.', Integrity::filter_error_notice( 'Another Woo error.' ), 'Unrelated Woo errors must remain intact.' );
statement_assert_same( '', Integrity::filter_error_notice( 'A piece in your bag is no longer available.' ), 'An existing lifecycle notice must suppress duplicate session errors.' );

fwrite( STDOUT, "PASS: M8 cart integrity passed ({$statement_assertions} assertions).\n" );
