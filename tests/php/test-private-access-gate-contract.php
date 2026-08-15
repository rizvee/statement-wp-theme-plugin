<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/EligibilityService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/PrivateAccessGate.php';

use Statement\Collector\Core\Access\EligibilityService;
use Statement\Collector\Core\Access\PrivateAccessGate;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

$statement_assertions = 0;

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

// Minimal mock product for testing
if ( ! class_exists( 'MockContractProduct' ) ) {
	class MockContractProduct {
		private int $id;
		private array $meta = array();

		public function __construct( int $id, array $meta = array() ) {
			$this->id   = $id;
			$this->meta = $meta;
		}

		public function get_id(): int {
			return $this->id;
		}

		public function get_type(): string {
			return 'simple';
		}

		public function get_meta( string $key, bool $single = true ) {
			return $this->meta[ $key ] ?? '';
		}

		public function update_meta_data( string $key, $value ): void {
			$this->meta[ $key ] = $value;
		}

		public function delete_meta_data( string $key ): void {
			unset( $this->meta[ $key ] );
		}

		public function save(): int {
			return $this->id;
		}
	}
}

$GLOBALS['mock_contract_products'] = array();

if ( ! function_exists( 'wc_get_product' ) ) {
	function wc_get_product( $id ) {
		$id = (int) $id;
		return $GLOBALS['mock_contract_products'][ $id ] ?? false;
	}
}

// -------------------------------------------------------------
// 1. Exact Root Cause Reproduction & Metadata Contract Assertions
// -------------------------------------------------------------

$private_prod = new MockContractProduct( 201, array(
	Metadata::RELEASE_STATE_KEY => ReleaseState::PRIVATE_ACCESS,
	Metadata::EDITION_LABEL_KEY => 'Test Edition',
) );
$GLOBALS['mock_contract_products'][201] = $private_prod;

// Passing int directly to Metadata::get_release_state fails safe to UPCOMING (reproducing the bug)
statement_assert_same(
	ReleaseState::UPCOMING,
	Metadata::get_release_state( 201 ),
	'Passing int ID to Metadata::get_release_state returns UPCOMING because ID is not a WC_Product object.'
);

// Passing valid WC_Product object returns the true PRIVATE_ACCESS state
statement_assert_same(
	ReleaseState::PRIVATE_ACCESS,
	Metadata::get_release_state( $private_prod ),
	'Passing WC_Product object to Metadata::get_release_state returns true PRIVATE_ACCESS state.'
);

// -------------------------------------------------------------
// 2. PrivateAccessGate::resolve_private_products Contract Tests
// -------------------------------------------------------------

// Candidate list with single PRIVATE_ACCESS product ID
$resolved = PrivateAccessGate::resolve_private_products( array( 201 ) );
statement_assert_same( 1, count( $resolved ), 'resolve_private_products must resolve exactly 1 product' );
statement_assert_same( $private_prod, $resolved[0], 'Resolved product must match the PRIVATE_ACCESS product object' );
statement_assert_same( ReleaseState::PRIVATE_ACCESS, Metadata::get_release_state( $resolved[0] ), 'Resolved product state must be PRIVATE_ACCESS' );

// UPCOMING only -> empty array (Gate detection FALSE)
$upcoming_prod = new MockContractProduct( 202, array( Metadata::RELEASE_STATE_KEY => ReleaseState::UPCOMING ) );
$GLOBALS['mock_contract_products'][202] = $upcoming_prod;
$resolved_upcoming = PrivateAccessGate::resolve_private_products( array( 202 ) );
statement_assert_same( 0, count( $resolved_upcoming ), 'UPCOMING products must not be resolved as private products' );

// LIVE only -> empty array (Gate detection FALSE)
$live_prod = new MockContractProduct( 203, array( Metadata::RELEASE_STATE_KEY => ReleaseState::LIVE ) );
$GLOBALS['mock_contract_products'][203] = $live_prod;
$resolved_live = PrivateAccessGate::resolve_private_products( array( 203 ) );
statement_assert_same( 0, count( $resolved_live ), 'LIVE products must not be resolved as private products' );

// SOLD_OUT only -> empty array (Gate detection FALSE)
$sold_out_prod = new MockContractProduct( 204, array( Metadata::RELEASE_STATE_KEY => ReleaseState::SOLD_OUT ) );
$GLOBALS['mock_contract_products'][204] = $sold_out_prod;
$resolved_sold_out = PrivateAccessGate::resolve_private_products( array( 204 ) );
statement_assert_same( 0, count( $resolved_sold_out ), 'SOLD_OUT products must not be resolved as private products' );

// ARCHIVED only -> empty array (Gate detection FALSE)
$archived_prod = new MockContractProduct( 205, array( Metadata::RELEASE_STATE_KEY => ReleaseState::ARCHIVED ) );
$GLOBALS['mock_contract_products'][205] = $archived_prod;
$resolved_archived = PrivateAccessGate::resolve_private_products( array( 205 ) );
statement_assert_same( 0, count( $resolved_archived ), 'ARCHIVED products must not be resolved as private products' );

// Mixed Drop (LIVE 203 + PRIVATE_ACCESS 201) -> extracts only PRIVATE_ACCESS (Gate detection TRUE)
$resolved_mixed = PrivateAccessGate::resolve_private_products( array( 203, 201 ) );
statement_assert_same( 1, count( $resolved_mixed ), 'Mixed Drop must resolve only the PRIVATE_ACCESS product' );
statement_assert_same( 201, $resolved_mixed[0]->get_id(), 'Resolved mixed product ID must be 201' );

// Invalid product ID (e.g. 999 not in store) -> safe skip, no fatal
$resolved_invalid = PrivateAccessGate::resolve_private_products( array( 999, 201, 888 ) );
statement_assert_same( 1, count( $resolved_invalid ), 'Invalid product IDs must be skipped safely' );
statement_assert_same( 201, $resolved_invalid[0]->get_id(), 'Valid product 201 must still be resolved' );

// -------------------------------------------------------------
// 3. Static Audit Regression: No Metadata::get_release_state((int)...)
// -------------------------------------------------------------

$core_src = $root . '/wp-content/plugins/statement-collector-core/src';
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $core_src ) );

foreach ( $iterator as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'php' ) {
		$content = file_get_contents( $file->getPathname() );
		// Check that no integer casts are passed to Metadata::get_release_state
		if ( preg_match( '/Metadata::get_release_state\s*\(\s*\(int\)/', $content ) ) {
			statement_assert_same( false, true, "Found forbidden integer cast to Metadata::get_release_state in " . $file->getPathname() );
		}
		// Check that no Metadata::update_release_state calls exist (must use set_release_state)
		if ( preg_match( '/Metadata::update_release_state/', $content ) ) {
			statement_assert_same( false, true, "Found non-existent Metadata::update_release_state call in " . $file->getPathname() );
		}
	}
}
statement_assert_same( true, true, 'Static audit passed: zero forbidden integer casts to Metadata::get_release_state in Core src.' );

echo "PASS: Private Access Gate contract test passed ({$statement_assertions} assertions).\n";
