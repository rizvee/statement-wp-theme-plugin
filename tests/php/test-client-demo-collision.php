<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require_once $root . '/tools/statement-client-demo/src/OwnershipClassifier.php';
require_once $root . '/tools/statement-client-demo/src/ManifestService.php';
require_once $root . '/tools/statement-client-demo/src/AssetRegistry.php';
require_once $root . '/tools/statement-client-demo/src/DemoSeederService.php';

use Statement\ClientDemo\OwnershipClassifier;
use Statement\ClientDemo\DemoSeederService;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

$statement_assertions = 0;

function stmt_assert( bool $condition, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function stmt_assert_same( $expected, $actual, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// Mock WordPress Environment
global $mock_post_meta, $mock_products_db;
$mock_post_meta   = array();
$mock_products_db = array();

function get_post_meta( int $post_id, string $key, bool $single = true ) {
	global $mock_post_meta;
	return $mock_post_meta[ $post_id ][ $key ] ?? '';
}

function update_post_meta( int $post_id, string $key, $value ): bool {
	global $mock_post_meta;
	$mock_post_meta[ $post_id ][ $key ] = $value;
	return true;
}

class MockDemoProduct {
	private int $id;
	private string $name;
	private string $slug;
	private string $sku;
	private array $meta = array();

	public function __construct( int $id, string $name, string $slug, string $sku, array $meta = array() ) {
		$this->id   = $id;
		$this->name = $name;
		$this->slug = $slug;
		$this->sku  = $sku;
		$this->meta = $meta;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function get_sku(): string {
		return $this->sku;
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
}

function wc_get_products( array $args ): array {
	global $mock_products_db;

	$results = array();
	$limit   = $args['limit'] ?? 10;
	$sku     = $args['sku'] ?? null;
	$slug    = $args['slug'] ?? null;

	foreach ( $mock_products_db as $p ) {
		if ( null !== $sku && $p->get_sku() !== $sku ) {
			continue;
		}
		if ( null !== $slug && $p->get_slug() !== $slug ) {
			continue;
		}
		$results[] = $p;
		if ( count( $results ) >= $limit ) {
			break;
		}
	}

	return $results;
}

// -------------------------------------------------------------
// TEST SUITE: Client Demo Collision Prevention Test Suite
// -------------------------------------------------------------

echo "Running Statement Client Demo Collision Prevention Test Suite...\n";

// Setup QA Fixture Product 213 (Simulated QA Fixture Collision Condition)
$qa_product_id = 213;
$qa_product    = new MockDemoProduct(
	$qa_product_id,
	'TEST — QA Jacquard Jacket Fixture',
	'monogram-jacquard-jacket',
	'TEST-QA-213',
	array(
		'_statement_fixture'       => '1',
		'_statement_release_state' => ReleaseState::LIVE,
	)
);
$mock_products_db[ $qa_product_id ] = $qa_product;
$mock_post_meta[ $qa_product_id ]   = array(
	'_statement_fixture'       => '1',
	'_statement_release_state' => ReleaseState::LIVE,
	'_sku'                     => 'TEST-QA-213',
);

// 1. Assert QA Fixture properties
stmt_assert_same( 213, $qa_product->get_id(), 'QA Product ID is 213' );
stmt_assert( 0 === strpos( $qa_product->get_name(), 'TEST —' ), 'QA Product title starts with TEST —' );
stmt_assert_same( 'monogram-jacquard-jacket', $qa_product->get_slug(), 'QA Product shares the canonical slug' );

// 2. DemoSeederService::find_owned_product MUST NOT adopt QA Fixture 213
$found_before_seed = DemoSeederService::find_owned_product( DemoSeederService::SKU_P1, 'monogram-jacquard-jacket' );
stmt_assert( null === $found_before_seed, 'find_owned_product strictly returns NULL and rejects QA fixture 213' );

// 3. QA Product remains completely un-mutated
stmt_assert_same( '', get_post_meta( 213, '_statement_client_demo', true ), 'QA product 213 has NO _statement_client_demo meta' );
stmt_assert_same( '1', get_post_meta( 213, '_statement_fixture', true ), 'QA product 213 retains _statement_fixture = 1' );
stmt_assert_same( 'TEST-QA-213', $qa_product->get_sku(), 'QA product 213 SKU remains TEST-QA-213' );

// 4. Create legitimate Client Demo Owned Product 301
$demo_product_id = 301;
$demo_product    = new MockDemoProduct(
	$demo_product_id,
	'Monogram Jacquard Jacket',
	'monogram-jacquard-jacket-demo',
	DemoSeederService::SKU_P1,
	array(
		'_statement_client_demo'   => '1',
		'_statement_release_state' => ReleaseState::LIVE,
	)
);
$mock_products_db[ $demo_product_id ] = $demo_product;
$mock_post_meta[ $demo_product_id ]   = array(
	'_statement_client_demo'   => 1,
	'_statement_release_state' => ReleaseState::LIVE,
	'_sku'                     => DemoSeederService::SKU_P1,
);

// 5. DemoSeederService::find_owned_product now correctly finds owned Demo Product 301 by SKU
$found_owned = DemoSeederService::find_owned_product( DemoSeederService::SKU_P1, 'monogram-jacquard-jacket' );
stmt_assert( null !== $found_owned, 'find_owned_product discovers owned demo product' );
stmt_assert_same( 301, $found_owned->get_id(), 'Discovered product is strictly the owned Demo product 301' );
stmt_assert_same( DemoSeederService::SKU_P1, $found_owned->get_sku(), 'Discovered product SKU matches STMT-CD-D001-MJ' );
stmt_assert_same( 1, (int) get_post_meta( $found_owned->get_id(), '_statement_client_demo', true ), 'Discovered product has _statement_client_demo marker' );

// 6. QA Product 213 remains unaffected throughout
stmt_assert_same( '', get_post_meta( 213, '_statement_client_demo', true ), 'QA product 213 still has NO _statement_client_demo marker' );
stmt_assert_same( 'TEST — QA Jacquard Jacket Fixture', $qa_product->get_name(), 'QA product 213 title remains intact' );

// 7. Hard Invariant: Accidental coexistence of _statement_fixture and _statement_client_demo is rejected
$mock_post_meta[301]['_statement_fixture'] = '1';
$found_coexistent = DemoSeederService::find_owned_product( DemoSeederService::SKU_P1, 'monogram-jacquard-jacket' );
stmt_assert( null === $found_coexistent, 'find_owned_product strictly rejects product if _statement_fixture is set, even if _statement_client_demo is present' );

// 8. Restore clean demo marker on 301
unset( $mock_post_meta[301]['_statement_fixture'] );
$found_restored = DemoSeederService::find_owned_product( DemoSeederService::SKU_P1, 'monogram-jacquard-jacket' );
stmt_assert( null !== $found_restored && 301 === $found_restored->get_id(), 'find_owned_product recovers once fixture marker is absent' );

// 9. OwnershipClassifier Verification
$c_qa = OwnershipClassifier::classify( 213 );
stmt_assert_same( OwnershipClassifier::STATUS_QA_FIXTURE, $c_qa['status'], 'QA Product 213 is classified as STATUS_QA_FIXTURE' );

$c_demo = OwnershipClassifier::classify( 301 );
stmt_assert_same( OwnershipClassifier::STATUS_CLIENT_DEMO, $c_demo['status'], 'Demo Product 301 is classified as STATUS_CLIENT_DEMO' );

// 10. Conflict Classification
$mock_post_meta[301]['_statement_fixture'] = '1';
$c_conflict = OwnershipClassifier::classify( 301 );
stmt_assert_same( OwnershipClassifier::STATUS_CONFLICT, $c_conflict['status'], 'Mixed Product 301 is classified as STATUS_CONFLICT' );
unset( $mock_post_meta[301]['_statement_fixture'] );

// 11. Production Organic Product Classification (No fixture, no demo markers)
$prod_product_id = 401;
$prod_product    = new MockDemoProduct(
	$prod_product_id,
	'Organic Store Jacket',
	'organic-store-jacket',
	'STMT-D001-01',
	array()
);
$mock_products_db[ $prod_product_id ] = $prod_product;
$mock_post_meta[ $prod_product_id ]   = array(
	'_sku' => 'STMT-D001-01',
);

$c_prod = OwnershipClassifier::classify( 401 );
stmt_assert_same( OwnershipClassifier::STATUS_PRODUCTION, $c_prod['status'], 'Organic Product 401 is classified as STATUS_PRODUCTION' );

echo "PASS: All {$statement_assertions} Statement Client Demo Collision Prevention assertions passed cleanly.\n";
