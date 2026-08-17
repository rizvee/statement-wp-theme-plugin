<?php
/**
 * Test Suite: Public QA Fixture Isolation & PublicApi Regression
 *
 * Verifies that internal QA fixtures are strictly excluded from public archive,
 * drop, catalog, and PublicApi presentation while preserving legitimate Client Demo entities.
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_post_meta = array();
$statement_posts = array();
$statement_terms = array();
$statement_assertions = 0;

function statement_assert( bool $condition, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function get_post_meta( int $post_id, string $key = '', bool $single = false ) {
	global $statement_post_meta;
	if ( '' === $key ) {
		return $statement_post_meta[ $post_id ] ?? array();
	}
	$val = $statement_post_meta[ $post_id ][ $key ] ?? null;
	if ( $single ) {
		return is_array( $val ) ? ( $val[0] ?? '' ) : (string) ( $val ?? '' );
	}
	return is_array( $val ) ? $val : array( $val );
}

function get_the_title( $post = 0 ): string {
	global $statement_posts;
	$id = is_object( $post ) ? ( $post->ID ?? 0 ) : (int) $post;
	return $statement_posts[ $id ]['post_title'] ?? '';
}

function get_post_field( string $field, $post = null ): string {
	global $statement_posts;
	$id = is_object( $post ) ? ( $post->ID ?? 0 ) : (int) $post;
	return $statement_posts[ $id ][ $field ] ?? '';
}

class Statement_Mock_Product {
	public int $id;
	public string $name;
	public string $sku;
	public string $slug;

	public function __construct( int $id, string $name, string $sku, string $slug ) {
		$this->id   = $id;
		$this->name = $name;
		$this->sku  = $sku;
		$this->slug = $slug;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_sku(): string {
		return $this->sku;
	}

	public function get_slug(): string {
		return $this->slug;
	}
}

$mock_products = array();

function wc_get_product( $the_product = false ) {
	global $mock_products;
	$id = is_object( $the_product ) ? ( $the_product->id ?? 0 ) : (int) $the_product;
	return $mock_products[ $id ] ?? null;
}

function wc_get_products( array $args = array() ): array {
	global $mock_products;
	return array_values( $mock_products );
}

$root = dirname( __DIR__, 2 );
require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Catalog/Visibility.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';

use Statement\Collector\Core\Catalog\Visibility;
use Statement\Collector\Core\PublicApi;

echo "Running Statement QA Fixture Public Isolation Test Suite...\n";

// Set up Test Entities:
// 1. QA Fixture Product (ID 213 in legacy test)
$statement_posts[213] = array(
	'ID' => 213,
	'post_title' => 'TEST — Studio Overshirt',
	'post_name' => 'test-studio-overshirt',
);
$statement_post_meta[213] = array(
	'_statement_fixture' => '1',
	'_sku' => 'TEST-SKU-001',
	'_statement_release_state' => 'archived',
);
$mock_products[213] = new Statement_Mock_Product( 213, 'TEST — Studio Overshirt', 'TEST-SKU-001', 'test-studio-overshirt' );

// 2. QA Fixture Product 2 (ID 214)
$statement_posts[214] = array(
	'ID' => 214,
	'post_title' => 'TEST — Terminal Jacket',
	'post_name' => 'test-terminal-jacket',
);
$statement_post_meta[214] = array(
	'_statement_fixture' => '1',
	'_sku' => 'TEST-SKU-002',
	'_statement_release_state' => 'archived',
);
$mock_products[214] = new Statement_Mock_Product( 214, 'TEST — Terminal Jacket', 'TEST-SKU-002', 'test-terminal-jacket' );

// 3. Legitimate Client Demo Product (ID 301)
$statement_posts[301] = array(
	'ID' => 301,
	'post_title' => 'Monogram Jacquard Jacket',
	'post_name' => 'monogram-jacquard-jacket',
);
$statement_post_meta[301] = array(
	'_statement_client_demo' => '1',
	'_sku' => 'STMT-CD-D001-01',
	'_statement_release_state' => 'archived',
);
$mock_products[301] = new Statement_Mock_Product( 301, 'Monogram Jacquard Jacket', 'STMT-CD-D001-01', 'monogram-jacquard-jacket' );

// 4. Authentic Organic Store Product (ID 401)
$statement_posts[401] = array(
	'ID' => 401,
	'post_title' => 'Panelled Hood Jacket',
	'post_name' => 'panelled-hood-jacket',
);
$statement_post_meta[401] = array(
	'_sku' => 'STMT-D001-02',
	'_statement_release_state' => 'archived',
);
$mock_products[401] = new Statement_Mock_Product( 401, 'Panelled Hood Jacket', 'STMT-D001-02', 'panelled-hood-jacket' );

// TEST 1: Visibility::is_fixture_product() classifications
statement_assert( true === Visibility::is_fixture_product( 213 ), 'QA Fixture 213 must be identified as fixture by ID' );
statement_assert( true === Visibility::is_fixture_product( $mock_products[213] ), 'QA Fixture 213 must be identified as fixture by Object' );
statement_assert( true === Visibility::is_fixture_product( 214 ), 'QA Fixture 214 must be identified as fixture' );

// TEST 2: Client Demo Protection Invariant
statement_assert( false === Visibility::is_fixture_product( 301 ), 'Client Demo Product 301 must NOT be identified as fixture' );
statement_assert( false === Visibility::is_fixture_product( $mock_products[301] ), 'Client Demo Object 301 must NOT be identified as fixture' );

// TEST 3: Authentic Organic Product Protection
statement_assert( false === Visibility::is_fixture_product( 401 ), 'Authentic Product 401 must NOT be identified as fixture' );

// TEST 4: PublicApi::get_archive_products() filtering
$archive_products = PublicApi::get_archive_products( 10 );
statement_assert( 2 === count( $archive_products ), 'Archive must return exactly 2 products (301 & 401), excluding the 2 fixtures' );

$returned_ids = array_map( static fn( $p ) => $p->get_id(), $archive_products );
statement_assert( ! in_array( 213, $returned_ids, true ), 'Archive must NEVER contain QA Fixture 213' );
statement_assert( ! in_array( 214, $returned_ids, true ), 'Archive must NEVER contain QA Fixture 214' );
statement_assert( in_array( 301, $returned_ids, true ), 'Archive must contain Client Demo Product 301' );
statement_assert( in_array( 401, $returned_ids, true ), 'Archive must contain Authentic Product 401' );

echo "PASS: All {$statement_assertions} Statement QA Fixture Public Isolation assertions passed cleanly.\n";
