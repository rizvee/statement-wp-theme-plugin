<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_assertions        = 0;
$statement_products          = array();
$statement_terms             = array();
$statement_mutation_calls    = 0;
$statement_taxonomy_mutation = 0;

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
}

function get_the_terms( int $product_id, string $taxonomy ) {
	global $statement_terms;
	return 'statement_drop' === $taxonomy ? ( $statement_terms[ $product_id ] ?? false ) : false;
}

function is_wp_error( $value ): bool {
	return false;
}

function wp_set_object_terms( int $object_id, array $terms, string $taxonomy, bool $append = false ): void {
	global $statement_taxonomy_mutation;
	++$statement_taxonomy_mutation;
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

final class Statement_Public_Api_Test_Product {
	private $id;
	private $parent_id;
	private $release_state;
	private $edition_label;

	public function __construct( int $id, string $release_state, int $parent_id = 0, string $edition_label = '' ) {
		$this->id            = $id;
		$this->release_state = $release_state;
		$this->parent_id     = $parent_id;
		$this->edition_label = $edition_label;
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
		if ( '_statement_release_state' === $key ) {
			return $this->release_state;
		}

		return '_statement_edition_label' === $key ? $this->edition_label : '';
	}

	public function update_meta_data( string $key, $value ): void {
		global $statement_mutation_calls;
		++$statement_mutation_calls;
	}

	public function save(): void {
		global $statement_mutation_calls;
		++$statement_mutation_calls;
	}
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';

use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\ReleaseState;

$states = array(
	ReleaseState::LIVE           => true,
	ReleaseState::PRIVATE_ACCESS => false,
	ReleaseState::UPCOMING       => false,
	ReleaseState::SOLD_OUT       => false,
	ReleaseState::ARCHIVED       => false,
);

$product_id = 1;
foreach ( $states as $state => $expected ) {
	$product = new Statement_Public_Api_Test_Product( $product_id++, $state );
	statement_assert_same( $expected, PublicApi::is_publicly_live( $product ), "Unexpected public eligibility for {$state}." );
}

$live_parent                    = new Statement_Public_Api_Test_Product( 100, ReleaseState::LIVE, 0, ' <b>EDITION 001</b> ' );
$private_parent                 = new Statement_Public_Api_Test_Product( 200, ReleaseState::PRIVATE_ACCESS );
$statement_products[100]        = $live_parent;
$statement_products[200]        = $private_parent;
$statement_terms[100]           = array( (object) array( 'term_id' => 10, 'taxonomy' => 'statement_drop', 'name' => 'Release Ten' ) );
$live_variation                 = new Statement_Public_Api_Test_Product( 101, '', 100 );
$private_access_variation       = new Statement_Public_Api_Test_Product( 201, '', 200 );

statement_assert_same( true, PublicApi::is_publicly_live( $live_variation ), 'Variation must inherit LIVE eligibility from its canonical parent.' );
statement_assert_same( false, PublicApi::is_publicly_live( $private_access_variation ), 'Variation must inherit PRIVATE_ACCESS exclusion from its canonical parent.' );
statement_assert_same( ReleaseState::LIVE, PublicApi::get_release_state( $live_variation ), 'Public API must expose the canonical parent release state.' );
statement_assert_same( 'EDITION 001', PublicApi::get_edition_label( $live_parent ), 'Simple product edition label must be sanitized and returned.' );
statement_assert_same( 'EDITION 001', PublicApi::get_edition_label( $live_variation ), 'Variation edition label must resolve through its canonical parent.' );
statement_assert_same( '', PublicApi::get_edition_label( new Statement_Public_Api_Test_Product( 300, ReleaseState::LIVE ) ), 'Missing edition label must return an empty string.' );

$drop = PublicApi::get_drop( $live_variation );
statement_assert_same( 10, $drop->term_id ?? null, 'Variation Drop must resolve through its canonical parent.' );

$statement_terms[100] = array( (object) array( 'term_id' => 99, 'taxonomy' => 'unrelated_taxonomy', 'name' => 'Wrong' ) );
statement_assert_same( null, PublicApi::get_drop( $live_variation ), 'Wrong-taxonomy terms must not be exposed.' );
statement_assert_same( false, PublicApi::is_publicly_live( null ), 'Missing products must never be publicly eligible.' );
statement_assert_same( null, PublicApi::get_drop( null ), 'Missing products must not resolve a Drop.' );
statement_assert_same( 0, $statement_mutation_calls, 'Read-only Public API must not update or save product data.' );
statement_assert_same( 0, $statement_taxonomy_mutation, 'Read-only Public API must not mutate taxonomy relationships.' );

fwrite( STDOUT, "PASS: M5 Public API passed ({$statement_assertions} assertions).\n" );
