<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );
define( 'WOOCOMMERCE_CHECKOUT', true );

$statement_actions    = array();
$statement_filters    = array();
$statement_products   = array();
$statement_notices    = array();
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

function __( string $text, string $domain = '' ): string {
	return $text;
}

function WC() {
	global $statement_wc;
	return $statement_wc;
}

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
}

function wc_has_notice( string $message, string $type = 'success' ): bool {
	global $statement_notices;
	return in_array( array( $message, $type ), $statement_notices, true );
}

function wc_add_notice( string $message, string $type = 'success' ): void {
	global $statement_notices;
	$statement_notices[] = array( $message, $type );
}

function wc_notice_count( string $type = '' ): int {
	global $statement_notices;
	if ( '' === $type ) {
		return count( $statement_notices );
	}

	return count( array_filter( $statement_notices, static fn( array $notice ): bool => $type === $notice[1] ) );
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

function statement_run_action( string $hook ): void {
	global $statement_actions;
	$registrations = $statement_actions[ $hook ] ?? array();
	usort( $registrations, static fn( array $left, array $right ): int => $left['priority'] <=> $right['priority'] );
	foreach ( $registrations as $registration ) {
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

final class WooCommerce {}

final class Statement_M9_Product {
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

	public function set_fixture_state( string $state ): void {
		$this->state = $state;
	}

	public function update_meta_data(): void {
		throw new RuntimeException( 'Checkout validation must not mutate lifecycle metadata.' );
	}

	public function set_stock_quantity(): void {
		throw new RuntimeException( 'Checkout validation must not mutate stock.' );
	}
}

final class Statement_M9_Cart {
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
require $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php';

use Statement\Collector\Core\Release\ReleaseState;

statement_run_action( 'plugins_loaded' );

statement_assert_same( 1, count( $statement_actions['woocommerce_check_cart_items'] ?? array() ), 'Cart Integrity must own one final checkout cart-check hook.' );
statement_assert_same( 0, $statement_actions['woocommerce_check_cart_items'][0]['priority'] ?? null, 'Statement validation must precede native Woo cart checks.' );

$live                         = new Statement_M9_Product( 1, ReleaseState::LIVE );
$sold                         = new Statement_M9_Product( 2, ReleaseState::LIVE );
$private_variation            = new Statement_M9_Product( 101, '', 100 );
$statement_products[100]      = new Statement_M9_Product( 100, ReleaseState::PRIVATE_ACCESS );
$all_live_variation           = new Statement_M9_Product( 201, '', 200 );
$statement_products[200]      = new Statement_M9_Product( 200, ReleaseState::LIVE );

$cart = new Statement_M9_Cart(
	array(
		'A' => array( 'data' => $live ),
		'V' => array( 'data' => $all_live_variation ),
	)
);
$statement_wc = (object) array( 'cart' => $cart );
statement_run_action( 'woocommerce_check_cart_items' );
statement_assert_same( array( 'A', 'V' ), array_keys( $cart->items ), 'All-LIVE checkout lines must remain untouched.' );
statement_assert_same( array(), $cart->removed, 'All-LIVE checkout must remove nothing.' );
statement_assert_same( 0, wc_notice_count( 'error' ), 'All-LIVE checkout must add no Statement error.' );

$cart = new Statement_M9_Cart(
	array(
		'A' => array( 'data' => $live ),
		'B' => array( 'data' => $sold ),
		'V' => array( 'data' => $private_variation ),
	)
);
$statement_wc      = (object) array( 'cart' => $cart );
$statement_notices = array();
$sold->set_fixture_state( ReleaseState::SOLD_OUT );
statement_run_action( 'woocommerce_check_cart_items' );

statement_assert_same( array( 'A' ), array_keys( $cart->items ), 'Final checkout validation must preserve only LIVE lines.' );
statement_assert_same( array( 'B', 'V' ), $cart->removed, 'SOLD_OUT and parent-PRIVATE_ACCESS lines must be removed.' );
statement_assert_same( 1, wc_notice_count( 'error' ), 'Stale checkout lines must produce one blocking error.' );
statement_assert_same( 0, wc_notice_count( 'notice' ), 'Final checkout validation must not downgrade the lifecycle failure to a notice.' );
statement_assert_same( false, 0 === wc_notice_count( 'error' ), 'Woo order creation gate must remain blocked by the Statement error.' );
statement_assert_same( array( array( 'A piece in your bag is no longer available.', 'error' ) ), $statement_notices, 'Customer-facing error must remain generic.' );

statement_run_action( 'woocommerce_check_cart_items' );
statement_assert_same( array( 'A' ), array_keys( $cart->items ), 'Repeated checkout validation must remain idempotent.' );
statement_assert_same( array( 'B', 'V' ), $cart->removed, 'Repeated validation must not remove a line twice.' );
statement_assert_same( 1, wc_notice_count( 'error' ), 'Repeated validation must not duplicate the Statement error.' );

$statement_notices[] = array( 'Native Woo validation failed.', 'error' );
statement_run_action( 'woocommerce_check_cart_items' );
statement_assert_same( 2, wc_notice_count( 'error' ), 'Statement validation must preserve unrelated native Woo errors.' );
statement_assert_same( array( 'A piece in your bag is no longer available.', 'error' ), $statement_notices[0], 'Statement error must not reveal lifecycle state.' );
statement_assert_same( array( 'Native Woo validation failed.', 'error' ), $statement_notices[1], 'Native Woo error must remain unchanged.' );

fwrite( STDOUT, "PASS: M9 checkout lifecycle passed ({$statement_assertions} assertions).\n" );
