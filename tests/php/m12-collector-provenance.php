<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Order/Provenance.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Order/Completion.php';

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Order\Provenance;
use Statement\Collector\Core\Order\Completion;

$statement_assertions = 0;

function statement_assert( bool $condition, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
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

class MockOrderItem {
	public array $meta = array();

	public function add_meta_data( string $key, $value, bool $unique = false ): void {
		if ( $unique && isset( $this->meta[ $key ] ) ) {
			return;
		}
		$this->meta[ $key ] = $value;
	}

	public function get_meta( string $key, bool $single = true ) {
		return $this->meta[ $key ] ?? '';
	}
}

class MockOrder {
	public string $status;

	public function __construct( string $status = 'processing' ) {
		$this->status = $status;
	}

	public function get_status(): string {
		return $this->status;
	}
}

class MockOrderProduct {
	public int $id;
	public string $name;
	public string $state;
	public string $edition;
	public ?int $parent_id;

	public function __construct( int $id, string $name, string $state, string $edition = 'First Edition', ?int $parent_id = null ) {
		$this->id        = $id;
		$this->name      = $name;
		$this->state     = $state;
		$this->edition   = $edition;
		$this->parent_id = $parent_id;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_type(): string {
		return null !== $this->parent_id ? 'variation' : 'simple';
	}

	public function get_parent_id(): int {
		return $this->parent_id ?? 0;
	}

	public function get_meta( string $key, bool $single = true ): string {
		if ( Metadata::RELEASE_STATE_KEY === $key ) {
			return $this->state;
		}
		if ( Metadata::EDITION_LABEL_KEY === $key ) {
			return $this->edition;
		}
		return '';
	}
}

// 1. CAPTURE PROVENANCE SNAPSHOT
$item    = new MockOrderItem();
$product = new MockOrderProduct( 42, 'Raw Ceremony Jacket', ReleaseState::PRIVATE_ACCESS, 'First Edition' );
$values  = array( 'data' => $product );

Provenance::capture_line_item_provenance( $item, 'cart_key_1', $values, new MockOrder() );

statement_assert_same( 1, (int) $item->get_meta( Provenance::META_VERSION ), 'Schema version must be 1.' );
statement_assert_same( 42, (int) $item->get_meta( Provenance::META_PRODUCT_ID ), 'Captured product ID must match.' );
statement_assert_same( 0, (int) $item->get_meta( Provenance::META_VARIATION_ID ), 'Simple product variation ID must be 0.' );
statement_assert_same( 'Raw Ceremony Jacket', $item->get_meta( Provenance::META_PRODUCT_TITLE ), 'Captured title must match.' );
statement_assert_same( 'First Edition', $item->get_meta( Provenance::META_EDITION_LABEL ), 'Captured edition label must match.' );
statement_assert_same( ReleaseState::PRIVATE_ACCESS, $item->get_meta( Provenance::META_RELEASE_STATE ), 'Captured release state must match.' );
statement_assert( ! empty( $item->get_meta( Provenance::META_PURCHASED_AT ) ), 'Purchase timestamp must be recorded.' );

// 2. VARIATION CAPTURE
$var_item = new MockOrderItem();
$variation = new MockOrderProduct( 99, 'Raw Ceremony Jacket - L', ReleaseState::LIVE, 'First Edition', 42 );
$var_values = array( 'data' => $variation );

// Mock parent lookup function for standalone environment
function wc_get_product( $id ) {
	if ( 42 === $id ) {
		return new MockOrderProduct( 42, 'Raw Ceremony Jacket', ReleaseState::LIVE, 'First Edition' );
	}
	return null;
}

Provenance::capture_line_item_provenance( $var_item, 'cart_key_2', $var_values, new MockOrder() );
statement_assert_same( 42, (int) $var_item->get_meta( Provenance::META_PRODUCT_ID ), 'Variation must capture parent product ID.' );
statement_assert_same( 99, (int) $var_item->get_meta( Provenance::META_VARIATION_ID ), 'Variation must capture variation ID.' );

// 3. IDEMPOTENCY
$initial_title = $item->get_meta( Provenance::META_PRODUCT_TITLE );
$product->name = 'MODIFIED TITLE AFTER PURCHASE';
Provenance::capture_line_item_provenance( $item, 'cart_key_1', $values, new MockOrder() );
statement_assert_same( $initial_title, $item->get_meta( Provenance::META_PRODUCT_TITLE ), 'Second capture attempt must NOT overwrite existing provenance.' );

// 4. HISTORICAL IMMUTABILITY (Reading provenance snapshot)
$snapshot = Provenance::get_provenance( $item );
statement_assert_same( 'Raw Ceremony Jacket', $snapshot['product_title'], 'Snapshot must return initial captured title.' );
statement_assert_same( 'First Edition', $snapshot['edition_label'], 'Snapshot must return initial captured edition label.' );
statement_assert_same( ReleaseState::PRIVATE_ACCESS, $snapshot['release_state'], 'Snapshot must return initial release state.' );

// 5. COMMERCIAL COMPLETION HELPER
statement_assert( Completion::is_commercially_completed( new MockOrder( 'processing' ) ), 'Processing status is commercially completed.' );
statement_assert( Completion::is_commercially_completed( new MockOrder( 'completed' ) ), 'Completed status is commercially completed.' );
statement_assert( ! Completion::is_commercially_completed( new MockOrder( 'pending' ) ), 'Pending status is NOT commercially completed.' );
statement_assert( ! Completion::is_commercially_completed( new MockOrder( 'failed' ) ), 'Failed status is NOT commercially completed.' );
statement_assert( ! Completion::is_commercially_completed( new MockOrder( 'cancelled' ) ), 'Cancelled status is NOT commercially completed.' );
statement_assert( ! Completion::is_commercially_completed( new MockOrder( 'refunded' ) ), 'Refunded status is NOT commercially completed.' );
statement_assert( ! Completion::is_commercially_completed( new MockOrder( 'on-hold' ) ), 'On-hold status is NOT commercially completed.' );

// 6. DATA MINIMIZATION
$all_keys = array_keys( $item->meta );
foreach ( $all_keys as $k ) {
	statement_assert( ! preg_match( '/email|phone|address|ip|token|secret|serial|certificate/i', $k ), "Provenance key {$k} must not leak PII or serial concepts." );
}

echo "PASS: M12 collector provenance test passed ({$statement_assertions} assertions).\n";
