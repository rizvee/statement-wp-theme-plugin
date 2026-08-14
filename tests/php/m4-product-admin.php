<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_assertions      = 0;
$statement_assignments     = array();
$statement_can_edit        = true;
$statement_is_autosave     = false;
$statement_is_revision     = false;
$statement_available_terms = array(
	7 => 'statement_drop',
	9 => 'unrelated_taxonomy',
);

function wp_is_post_autosave( int $post_id ) {
	global $statement_is_autosave;
	return $statement_is_autosave ? $post_id : false;
}

function wp_is_post_revision( int $post_id ) {
	global $statement_is_revision;
	return $statement_is_revision ? $post_id : false;
}

function current_user_can( string $capability, int $post_id ): bool {
	global $statement_can_edit;
	return 'edit_post' === $capability && $post_id > 0 && $statement_can_edit;
}

function wp_unslash( string $value ): string {
	return stripslashes( $value );
}

function sanitize_text_field( string $value ): string {
	$value = trim( strip_tags( $value ) );
	return preg_replace( '/\s+/', ' ', $value ) ?? '';
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

function wp_set_object_terms( int $object_id, array $terms, string $taxonomy, bool $append = false ): void {
	global $statement_assignments;
	$statement_assignments[] = compact( 'object_id', 'terms', 'taxonomy', 'append' );
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

final class Statement_Admin_Test_Product {
	private $metadata = array();

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

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Admin.php';

use Statement\Collector\Core\Drop\Taxonomy;
use Statement\Collector\Core\Product\Admin;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

$product = new Statement_Admin_Test_Product();

statement_assert_same( ReleaseState::UPCOMING, Metadata::get_release_state( $product ), 'Uninitialized product state must default to UPCOMING.' );

$_POST = array(
	'_statement_collector_product_nonce' => 'valid-nonce',
	'statement_collector_drop'           => '7',
	'_statement_release_state'           => ReleaseState::LIVE,
	'_statement_edition_label'           => '<b>EDITION</b> ' . str_repeat( '1', 90 ),
);
Admin::save_fields( $product );

statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Valid forward transition must persist.' );
statement_assert_same( 80, strlen( Metadata::get_edition_label( $product ) ), 'Edition label must be bounded to 80 characters.' );
statement_assert_same( false, str_contains( Metadata::get_edition_label( $product ), '<b>' ), 'Edition label must be plain text.' );
statement_assert_same( 1, count( $statement_assignments ), 'Valid Drop save must assign once.' );
statement_assert_same( array( 7 ), $statement_assignments[0]['terms'], 'Drop save must assign exactly one term.' );
statement_assert_same( Taxonomy::KEY, $statement_assignments[0]['taxonomy'], 'Drop save must target statement_drop only.' );
statement_assert_same( false, $statement_assignments[0]['append'], 'Drop save must replace rather than append terms.' );

$_POST['_statement_release_state'] = ReleaseState::PRIVATE_ACCESS;
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Backward transition must preserve current state.' );

$_POST['_statement_release_state'] = 'INVALID';
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Invalid requested state must preserve current state.' );

unset( $_POST['_statement_release_state'] );
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Missing state input must preserve current state.' );

$assignment_count                     = count( $statement_assignments );
$_POST['statement_collector_drop']     = '999';
$_POST['_statement_edition_label']     = '';
Admin::save_fields( $product );
statement_assert_same( $assignment_count, count( $statement_assignments ), 'Unknown Drop term must preserve the prior assignment.' );
statement_assert_same( '', Metadata::get_edition_label( $product ), 'Empty edition label must remain allowed.' );

$_POST['statement_collector_drop'] = '9';
Admin::save_fields( $product );
statement_assert_same( $assignment_count, count( $statement_assignments ), 'Wrong-taxonomy term must preserve the prior assignment.' );

$_POST['statement_collector_drop'] = '';
Admin::save_fields( $product );
statement_assert_same( array(), $statement_assignments[ $assignment_count ]['terms'], 'Empty Drop input must allow an unassigned product.' );

$assignment_count = count( $statement_assignments );
unset( $_POST['statement_collector_drop'] );
Admin::save_fields( $product );
statement_assert_same( $assignment_count, count( $statement_assignments ), 'Missing Drop input must preserve assignment data.' );

$_POST['_statement_collector_product_nonce'] = 'invalid';
$_POST['_statement_release_state']           = ReleaseState::SOLD_OUT;
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Invalid nonce must block metadata changes.' );

$_POST['_statement_collector_product_nonce'] = 'valid-nonce';
$statement_can_edit                         = false;
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Missing capability must block metadata changes.' );

$statement_can_edit    = true;
$statement_is_autosave = true;
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Autosave must not mutate Statement metadata.' );

$statement_is_autosave = false;
$statement_is_revision = true;
Admin::save_fields( $product );
statement_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $product ), 'Revision save must not mutate Statement metadata.' );

fwrite( STDOUT, "PASS: M4 product-admin integrity passed ({$statement_assertions} assertions).\n" );
