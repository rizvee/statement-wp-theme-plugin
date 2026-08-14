<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_assertions      = 0;
$statement_available_terms = array(
	10 => 'statement_drop',
	20 => 'statement_drop',
	30 => 'statement_drop',
	40 => 'unrelated_taxonomy',
);
$statement_object_terms    = array();

function wp_is_post_autosave( int $post_id ) {
	return false;
}

function wp_is_post_revision( int $post_id ) {
	return false;
}

function current_user_can( string $capability, int $post_id ): bool {
	return 'edit_post' === $capability && $post_id > 0;
}

function wp_unslash( string $value ): string {
	return stripslashes( $value );
}

function sanitize_text_field( string $value ): string {
	return trim( strip_tags( $value ) );
}

function wp_verify_nonce( string $nonce, string $action ): bool {
	return 'valid-nonce' === $nonce && 'statement_collector_save_product_data' === $action;
}

function absint( $value ): int {
	return abs( (int) $value );
}

function get_term( int $term_id, string $taxonomy ) {
	global $statement_available_terms;
	if ( ! isset( $statement_available_terms[ $term_id ] ) ) {
		return null;
	}

	return (object) array(
		'term_id'  => $term_id,
		'taxonomy' => $statement_available_terms[ $term_id ],
	);
}

function is_wp_error( $value ): bool {
	return false;
}

function wp_get_object_terms( int $object_id, string $taxonomy, array $arguments = array() ): array {
	global $statement_object_terms;
	return 'statement_drop' === $taxonomy ? ( $statement_object_terms[ $object_id ] ?? array() ) : array();
}

function wp_set_object_terms( int $object_id, array $terms, string $taxonomy, bool $append = false ): void {
	global $statement_object_terms;
	if ( 'statement_drop' === $taxonomy && ! $append ) {
		$statement_object_terms[ $object_id ] = array_values( $terms );
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

final class Statement_Drop_History_Test_Product {
	private $metadata = array();

	public function __construct( string $release_state ) {
		$this->metadata['_statement_release_state'] = $release_state;
	}

	public function get_id(): int {
		return 42;
	}

	public function get_meta( string $key, bool $single = true ) {
		return $this->metadata[ $key ] ?? '';
	}

	public function update_meta_data( string $key, $value ): void {
		$this->metadata[ $key ] = $value;
	}

	public function delete_meta_data( string $key ): void {
		unset( $this->metadata[ $key ] );
	}
}

function statement_save_drop( Statement_Drop_History_Test_Product $product, string $submitted ): void {
	$_POST = array(
		'_statement_collector_product_nonce' => 'valid-nonce',
		'statement_collector_drop'           => $submitted,
	);

	\Statement\Collector\Core\Product\Admin::save_fields( $product );
}

function statement_set_drop( int $product_id, array $term_ids ): void {
	global $statement_object_terms;
	$statement_object_terms[ $product_id ] = $term_ids;
}

function statement_get_drop( int $product_id ): array {
	global $statement_object_terms;
	return $statement_object_terms[ $product_id ] ?? array();
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Admin.php';

use Statement\Collector\Core\Release\ReleaseState;

foreach ( array( ReleaseState::PRIVATE_ACCESS, ReleaseState::LIVE, ReleaseState::SOLD_OUT, ReleaseState::ARCHIVED ) as $locked_state ) {
	$product = new Statement_Drop_History_Test_Product( $locked_state );
	statement_set_drop( $product->get_id(), array( 10 ) );
	statement_save_drop( $product, '20' );
	statement_assert_same( array( 10 ), statement_get_drop( $product->get_id() ), "{$locked_state} must preserve an established historical Drop." );
}

$product = new Statement_Drop_History_Test_Product( ReleaseState::LIVE );
statement_set_drop( $product->get_id(), array( 10 ) );
statement_save_drop( $product, '' );
statement_assert_same( array( 10 ), statement_get_drop( $product->get_id() ), 'Released product must reject historical Drop removal.' );

$product = new Statement_Drop_History_Test_Product( ReleaseState::UPCOMING );
statement_set_drop( $product->get_id(), array( 10 ) );
statement_save_drop( $product, '20' );
statement_assert_same( array( 20 ), statement_get_drop( $product->get_id() ), 'UPCOMING product may change its Drop.' );
statement_save_drop( $product, '' );
statement_assert_same( array(), statement_get_drop( $product->get_id() ), 'UPCOMING product may clear its Drop.' );

$product = new Statement_Drop_History_Test_Product( ReleaseState::LIVE );
statement_set_drop( $product->get_id(), array() );
statement_save_drop( $product, '20' );
statement_assert_same( array( 20 ), statement_get_drop( $product->get_id() ), 'Released product with no historical Drop may receive one first valid assignment.' );
statement_save_drop( $product, '30' );
statement_assert_same( array( 20 ), statement_get_drop( $product->get_id() ), 'First recovery assignment must become immutable.' );

statement_save_drop( $product, '999' );
statement_assert_same( array( 20 ), statement_get_drop( $product->get_id() ), 'Unknown Drop input must preserve the historical relationship.' );
statement_save_drop( $product, '40' );
statement_assert_same( array( 20 ), statement_get_drop( $product->get_id() ), 'Wrong-taxonomy input must preserve the historical relationship.' );

$product = new Statement_Drop_History_Test_Product( ReleaseState::SOLD_OUT );
statement_set_drop( $product->get_id(), array( 999 ) );
statement_save_drop( $product, '30' );
statement_assert_same( array( 30 ), statement_get_drop( $product->get_id() ), 'Released product may recover from an invalid historical relationship with one valid assignment.' );

fwrite( STDOUT, "PASS: M4.1 historical Drop integrity passed ({$statement_assertions} assertions).\n" );
