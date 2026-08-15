<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

// Global hooks mock
$statement_test_filters = array();

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( string $tag, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		global $statement_test_filters;
		$statement_test_filters[ $tag ][] = $callback;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( string $tag, $value, ...$args ) {
		global $statement_test_filters;
		if ( empty( $statement_test_filters[ $tag ] ) ) {
			return $value;
		}
		foreach ( $statement_test_filters[ $tag ] as $callback ) {
			$value = call_user_func_array( $callback, array_merge( array( $value ), $args ) );
		}
		return $value;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $tag, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		// Mock
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin(): bool {
		return false;
	}
}

$assertions = 0;
function test_assert_same( $expected, $actual, string $message ): void {
	global $assertions;
	++$assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// =========================================================================
// TEST 1: Bootstrap Without WooCommerce (NO WC_Payment_Gateway class)
// =========================================================================

test_assert_same( false, class_exists( 'WC_Payment_Gateway' ), 'WC_Payment_Gateway must NOT be defined initially' );

// Load fixture plugin entry point
require_once $root . '/tools/statement-integration-fixtures/statement-integration-fixtures.php';

test_assert_same( true, defined( 'STATEMENT_INTEGRATION_FIXTURES_VERSION' ), 'Fixture version constant must be defined' );
test_assert_same( '0.3.1', STATEMENT_INTEGRATION_FIXTURES_VERSION, 'Fixture version must be 0.3.1' );
test_assert_same( false, class_exists( 'Statement\Integration\Fixtures\StatementQaGateway', false ), 'StatementQaGateway must NOT be loaded on plugin bootstrap without WooCommerce' );

// Trigger filter when WooCommerce is absent -> returns unmodified list
$gateways_empty = apply_filters( 'woocommerce_payment_gateways', array( 'ExistingGateway' ) );
test_assert_same( array( 'ExistingGateway' ), $gateways_empty, 'Gateway filter without WC_Payment_Gateway returns unchanged list' );
test_assert_same( false, class_exists( 'Statement\Integration\Fixtures\StatementQaGateway', false ), 'StatementQaGateway still not loaded' );

// =========================================================================
// TEST 2: Simulate WooCommerce Gateway Registration
// =========================================================================

eval('
abstract class WC_Payment_Gateway {
	public $id = "";
	public $method_title = "";
	public $method_description = "";
	public $title = "";
	public $description = "";
	public $has_fields = false;
	public $enabled = "no";
	public function init_form_fields() {}
	public function init_settings() {}
	public function is_available() { return true; }
	public function get_return_url( $order = null ) { return "https://example.com/checkout/order-received/999/"; }
}
');


// Now invoke the filter
$gateways_after = apply_filters( 'woocommerce_payment_gateways', array( 'ExistingGateway' ) );

test_assert_same( true, class_exists( 'Statement\Integration\Fixtures\StatementQaGateway', false ), 'StatementQaGateway class is loaded after WC_Payment_Gateway exists' );
test_assert_same(
	array( 'ExistingGateway', 'Statement\Integration\Fixtures\StatementQaGateway' ),
	$gateways_after,
	'Gateway is registered in gateway list'
);

// Repeated filter invocation does NOT append duplicate
$gateways_repeated = apply_filters( 'woocommerce_payment_gateways', $gateways_after );
test_assert_same(
	array( 'ExistingGateway', 'Statement\Integration\Fixtures\StatementQaGateway' ),
	$gateways_repeated,
	'Repeated filter invocation does not add duplicate gateway registration'
);

// =========================================================================
// TEST 3: Gateway Scope & Order Stock Reduction Safety
// =========================================================================

use Statement\Integration\Fixtures\StatementQaGateway;

// Gateway metadata & constants
$gw = new StatementQaGateway();
test_assert_same( 'statement_qa_gateway', $gw->id, 'Gateway ID matches' );
test_assert_same( 'TEST-PD01-PAJ', StatementQaGateway::TARGET_SKU, 'Target SKU is TEST-PD01-PAJ' );
test_assert_same( '0.3.1', StatementQaGateway::VERSION, 'Gateway version is 0.3.1' );

// Mock WooCommerce Order with stock tracking
class MockOrderItem {
	private $sku;
	private $id;

	public function __construct( string $sku, int $id = 213 ) {
		$this->sku = $sku;
		$this->id  = $id;
	}

	public function get_product() {
		return new class( $this->sku, $this->id ) {
			private $sku;
			private $id;
			public function __construct( $sku, $id ) { $this->sku = $sku; $this->id = $id; }
			public function get_sku() { return $this->sku; }
			public function get_id() { return $this->id; }
		};
	}
}

class MockOrder {
	public $id = 999;
	public $status = 'pending';
	public $meta = array();
	public $items = array();
	public $stock_reduced_count = 0;

	public function __construct( array $items ) {
		$this->items = $items;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_items() {
		return $this->items;
	}

	public function update_meta_data( $key, $value ) {
		$this->meta[ $key ] = $value;
	}

	public function payment_complete() {
		$this->status = 'processing';
		// Simulates WooCommerce core stock reduction on payment_complete hook
		++$this->stock_reduced_count;
	}
}

if ( ! function_exists( 'wc_get_order' ) ) {
	function wc_get_order( $id ) {
		global $statement_mock_order;
		return $statement_mock_order;
	}
}

// 3A: Order Scope Validation - Invalid / Non-Test Product Rejected
global $statement_mock_order;
$statement_mock_order = new MockOrder( array( new MockOrderItem( 'WRONG-SKU-999' ) ) );
$res_fail = $gw->process_payment( 999 );
test_assert_same( 'failure', $res_fail['result'], 'process_payment must fail for wrong SKU' );
test_assert_same( 0, $statement_mock_order->stock_reduced_count, 'Stock must not be reduced on failed order' );

// 3B: Order Scope Validation - Valid Exact Test Product Accepted
$statement_mock_order = new MockOrder( array( new MockOrderItem( 'TEST-PD01-PAJ' ) ) );
$res_pass = $gw->process_payment( 999 );
test_assert_same( 'success', $res_pass['result'], 'process_payment succeeds for exact test SKU' );
test_assert_same( 'processing', $statement_mock_order->status, 'Order status set to processing' );
test_assert_same( 'yes', $statement_mock_order->meta['_statement_is_qa_order'], 'QA order metadata set' );
test_assert_same( '0.3.1', $statement_mock_order->meta['_statement_qa_gateway_version'], 'QA gateway version metadata set' );

// 3C: Single-Stock Reduction Check: Exactly 1 stock deduction occurred (no duplicate wc_reduce_stock_levels call)
test_assert_same( 1, $statement_mock_order->stock_reduced_count, 'Order stock must be reduced exactly ONCE via payment_complete' );

echo "Fixture 0.3.1 Bootstrap & QA Gateway Behavior Tests PASS: {$assertions} assertions verified clean.\n";
