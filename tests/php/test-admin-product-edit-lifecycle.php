<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Order/Provenance.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/Purchasability.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/LifecycleOverrideService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Admin/LifecycleV2Admin.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Admin.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Access.php';

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Admin\LifecycleV2Admin;
use Statement\Collector\Core\Drop\Taxonomy;
use Statement\Collector\Core\Order\Provenance;
use Statement\Collector\Core\Product\Access;
use Statement\Collector\Core\Product\Admin as ProductAdmin;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\LifecycleOverrideService;
use Statement\Collector\Core\Release\Purchasability;
use Statement\Collector\Core\Release\ReleaseState;

$assertions_count = 0;

function regression_assert( bool $condition, string $message ): void {
	global $assertions_count;
	++$assertions_count;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function regression_assert_same( $expected, $actual, string $message ): void {
	global $assertions_count;
	++$assertions_count;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// Global mocks
global $mock_options, $mock_products, $mock_drop_configs, $mock_the_terms, $mock_post_meta;
$mock_options      = array();
$mock_products     = array();
$mock_drop_configs = array();
$mock_the_terms    = array();
$mock_post_meta    = array();

function get_option( string $name, $default = false ) {
	global $mock_options;
	return $mock_options[ $name ] ?? $default;
}

function update_option( string $name, $value, $autoload = null ): bool {
	global $mock_options;
	$mock_options[ $name ] = $value;
	return true;
}

function delete_option( string $name ): bool {
	global $mock_options;
	unset( $mock_options[ $name ] );
	return true;
}

function is_wp_error( $thing ): bool {
	return false;
}

function sanitize_text_field( string $str ): string {
	return trim( strip_tags( $str ) );
}

function sanitize_key( string $key ): string {
	return preg_replace( '/[^a-z0-9_\-]/i', '', $key );
}

function absint( $maybeint ): int {
	return abs( (int) $maybeint );
}

function wp_unslash( $val ) {
	return $val;
}

function wp_verify_nonce( $nonce, $action ): bool {
	return 'valid_nonce' === $nonce;
}

function wp_create_nonce( $action ): string {
	return 'valid_nonce';
}

function wp_nonce_field( $action, $name = '_wpnonce', $referer = true, $echo = true ): string {
	$field = '<input type="hidden" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="valid_nonce" />';
	if ( $echo ) {
		echo $field;
	}
	return $field;
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_html_e( $text, $domain = 'default' ) {
	echo esc_html( $text );
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr_e( $text, $domain = 'default' ) {
	echo esc_attr( $text );
}

function esc_url( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL ) ?: '';
}

function admin_url( string $path = '' ): string {
	return 'https://example.com/wp-admin/' . ltrim( $path, '/' );
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function current_user_can( string $cap, ...$args ): bool {
	return true;
}

function get_current_user_id(): int {
	return 1;
}

function wp_is_post_autosave( $post_id ): bool {
	return false;
}

function wp_is_post_revision( $post_id ): bool {
	return false;
}

function wp_get_object_terms( $post_id, $taxonomy, $args = array() ) {
	global $mock_the_terms;
	$terms = $mock_the_terms[ $post_id ] ?? array();
	if ( isset( $args['fields'] ) && 'fields' === $args['fields'] ) {
		return array_map( function( $t ) { return is_object( $t ) ? $t->term_id : (int) $t; }, $terms );
	}
	if ( isset( $args['fields'] ) && 'ids' === $args['fields'] ) {
		return array_map( function( $t ) { return is_object( $t ) ? $t->term_id : (int) $t; }, $terms );
	}
	return $terms;
}

function wp_set_object_terms( $post_id, $terms, $taxonomy, $append = false ) {
	global $mock_the_terms;
	$mock_the_terms[ $post_id ] = (array) $terms;
	return $mock_the_terms[ $post_id ];
}

function get_term( $term_id, $taxonomy = '' ) {
	return (object) array(
		'term_id'  => (int) $term_id,
		'name'     => 'Drop ' . $term_id,
		'slug'     => 'drop-' . $term_id,
		'taxonomy' => $taxonomy ?: Taxonomy::KEY,
	);
}

function get_terms( $args = array() ) {
	return array(
		(object) array(
			'term_id'  => 1377,
			'name'     => 'Drop 001 — Monogram Study',
			'slug'     => 'drop-001-monogram-study',
			'taxonomy' => Taxonomy::KEY,
		),
	);
}

function get_post_meta( int $post_id, string $key = '', bool $single = false ) {
	global $mock_post_meta;
	if ( '' === $key ) {
		return $mock_post_meta[ $post_id ] ?? array();
	}
	$val = $mock_post_meta[ $post_id ][ $key ] ?? '';
	return $single ? $val : array( $val );
}

function update_post_meta( int $post_id, string $key, $value ): bool {
	global $mock_post_meta;
	$mock_post_meta[ $post_id ][ $key ] = $value;
	return true;
}

function get_the_terms( int $post_id, string $taxonomy ) {
	global $mock_the_terms;
	return $mock_the_terms[ $post_id ] ?? false;
}

function get_term_meta( int $term_id, string $key, bool $single = true ) {
	global $mock_drop_configs;
	return $mock_drop_configs[ $term_id ][ $key ] ?? '';
}

function woocommerce_wp_select( array $field ): void {}
function woocommerce_wp_text_input( array $field ): void {}

class MockFullProduct {
	private int $id;
	private string $name;
	private string $description;
	private string $price;
	private int $stock_quantity;
	private string $type;
	private array $meta;

	public function __construct( int $id, string $name, string $price, int $stock, array $meta = array() ) {
		$this->id             = $id;
		$this->name           = $name;
		$this->price          = $price;
		$this->stock_quantity = $stock;
		$this->description    = 'Original description';
		$this->type           = 'simple';
		$this->meta           = $meta;
	}

	public function get_id(): int { return $this->id; }
	public function get_name(): string { return $this->name; }
	public function set_name( string $name ): void { $this->name = $name; }
	public function get_price(): string { return $this->price; }
	public function set_price( string $price ): void { $this->price = $price; }
	public function get_description(): string { return $this->description; }
	public function set_description( string $desc ): void { $this->description = $desc; }
	public function get_stock_quantity(): int { return $this->stock_quantity; }
	public function set_stock_quantity( int $qty ): void { $this->stock_quantity = $qty; }
	public function get_type(): string { return $this->type; }
	public function is_type( string $type ): bool { return $this->type === $type; }
	public function is_in_stock(): bool { return $this->stock_quantity > 0; }
	public function managing_stock(): bool { return true; }
	public function is_purchasable(): bool { return Purchasability::filter_purchasable( true, $this ); }

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
		global $mock_products;
		$mock_products[ $this->id ] = $this;
		return $this->id;
	}
}

function wc_get_product( $id ) {
	global $mock_products;
	if ( is_object( $id ) ) return $id;
	return $mock_products[ $id ] ?? null;
}

echo "Running Statement Admin Product Edit & Lifecycle Regression Tests...\n";

// -------------------------------------------------------------
// TEST 1: Ordinary WooCommerce product save succeeds without override fields
// -------------------------------------------------------------
$live_prod = new MockFullProduct( 701, 'Monogram Jacquard Jacket', '295.00', 16, array(
	Metadata::RELEASE_STATE_KEY => ReleaseState::LIVE,
) );
$mock_products[701] = $live_prod;

// Simulate ordinary WooCommerce product edit save where NO lifecycle override fields exist in $_POST
$_POST = array(
	'_statement_collector_product_nonce' => 'valid_nonce',
	'statement_collector_drop'           => '1377',
	// Notice: no target_state, no override_reason, no confirm_override
);

// Admin updates title, price, stock, and description via WooCommerce CRUD
$live_prod->set_name( 'Monogram Jacquard Jacket — Updated Title' );
$live_prod->set_price( '320.00' );
$live_prod->set_stock_quantity( 24 );
$live_prod->set_description( 'Updated luxury description text' );

// Save fields via ProductAdmin handler
ProductAdmin::save_fields( $live_prod );
$live_prod->save();

regression_assert_same( 'Monogram Jacquard Jacket — Updated Title', $live_prod->get_name(), 'Product title successfully updated via normal save' );
regression_assert_same( '320.00', $live_prod->get_price(), 'Product price successfully updated via normal save' );
regression_assert_same( 24, $live_prod->get_stock_quantity(), 'Product stock quantity successfully updated via normal save' );
regression_assert_same( 'Updated luxury description text', $live_prod->get_description(), 'Product description successfully updated via normal save' );
regression_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $live_prod ), 'Release state remains LIVE after normal product save' );
regression_assert( true === $live_prod->is_purchasable(), 'LIVE product remains purchasable publicly' );

// -------------------------------------------------------------
// TEST 2: ARCHIVED product stock edit persists in Woo, but release state remains ARCHIVED and purchasability remains FALSE
// -------------------------------------------------------------
$archived_prod = new MockFullProduct( 702, 'Archived Archive Piece', '450.00', 0, array(
	Metadata::RELEASE_STATE_KEY => ReleaseState::ARCHIVED,
) );
$mock_products[702] = $archived_prod;

regression_assert_same( ReleaseState::ARCHIVED, Metadata::get_release_state( $archived_prod ), 'Initial state is ARCHIVED' );
regression_assert( false === $archived_prod->is_purchasable(), 'Initial ARCHIVED purchasability is FALSE' );

// Admin edits stock from 0 -> 10 directly in WooCommerce
$_POST = array(
	'_statement_collector_product_nonce' => 'valid_nonce',
);
$archived_prod->set_stock_quantity( 10 );
ProductAdmin::save_fields( $archived_prod );
$archived_prod->save();

regression_assert_same( 10, $archived_prod->get_stock_quantity(), 'Stock quantity 10 persists in WooCommerce' );
regression_assert_same( ReleaseState::ARCHIVED, Metadata::get_release_state( $archived_prod ), 'Release state strictly remains ARCHIVED' );
regression_assert( false === $archived_prod->is_purchasable(), 'Public purchasability remains strictly FALSE because lifecycle is ARCHIVED' );

// -------------------------------------------------------------
// TEST 3: Privileged Lifecycle Override executes ONLY when explicitly submitted
// -------------------------------------------------------------
$override_result = LifecycleOverrideService::override_state(
	$archived_prod,
	ReleaseState::LIVE,
	1,
	'Editorial reopen authorized with verified stock 10'
);

regression_assert( true === ( $override_result['success'] ?? false ), 'Privileged override succeeds when explicitly called' );
regression_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $archived_prod ), 'State transitioned to LIVE after explicit override' );
regression_assert( true === $archived_prod->is_purchasable(), 'Product is now purchasable after explicit override to LIVE' );

// -------------------------------------------------------------
// TEST 4: Metabox HTML rendering has NO nested <form>, NO required attributes blocking #post
// -------------------------------------------------------------
$mock_post = (object) array( 'ID' => 701 );
ob_start();
LifecycleV2Admin::render_meta_box( $mock_post );
$html = ob_get_clean();

regression_assert( false === stripos( $html, '<form' ), 'Metabox MUST NOT contain a <form> tag' );
regression_assert( false === stripos( $html, '</form>' ), 'Metabox MUST NOT contain a </form> tag' );
regression_assert( 0 === preg_match( '/<select\b[^>]*\brequired\b/i', $html ), 'Metabox select MUST NOT have HTML required attribute' );
regression_assert( 0 === preg_match( '/<input\b[^>]*\brequired\b/i', $html ), 'Metabox input MUST NOT have HTML required attribute' );
regression_assert( 0 === preg_match( '/<textarea\b[^>]*\brequired\b/i', $html ), 'Metabox textarea MUST NOT have HTML required attribute' );
regression_assert( false !== strpos( $html, 'type="button"' ), 'Metabox override button MUST be type="button"' );
regression_assert( false !== strpos( $html, 'STATEMENT RELEASE' ), 'Metabox contains STATEMENT RELEASE header' );
regression_assert( false !== strpos( $html, 'PRIVILEGED OVERRIDE' ), 'Metabox contains PRIVILEGED OVERRIDE header' );

echo "PASS: All {$assertions_count} Statement Admin Product Edit & Lifecycle regression assertions passed cleanly.\n";
