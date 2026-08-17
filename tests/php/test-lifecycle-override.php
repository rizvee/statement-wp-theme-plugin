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
require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/LifecycleOverrideService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Admin/LifecycleV2Admin.php';

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Admin\LifecycleV2Admin;
use Statement\Collector\Core\Drop\Taxonomy;
use Statement\Collector\Core\Order\Provenance;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\LifecycleOverrideService;
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

// Global mocks
global $mock_options, $mock_products, $mock_drop_configs, $mock_the_terms;
$mock_options      = array();
$mock_products     = array();
$mock_drop_configs = array();
$mock_the_terms    = array();

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

function wp_generate_uuid4(): string {
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff )
	);
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function get_term_meta( int $term_id, string $key, bool $single = true ) {
	global $mock_drop_configs;
	return $mock_drop_configs[ $term_id ][ $key ] ?? '';
}

function get_the_terms( int $post_id, string $taxonomy ) {
	global $mock_the_terms;
	return $mock_the_terms[ $post_id ] ?? false;
}

class MockTestProduct {
	private int $id;
	private array $meta;
	private int $stock_quantity;
	private string $type;
	private array $children;
	private ?int $parent_id;

	public function __construct( int $id, array $meta = array(), int $stock = 10, string $type = 'simple', array $children = array(), ?int $parent_id = null ) {
		$this->id             = $id;
		$this->meta           = $meta;
		$this->stock_quantity = $stock;
		$this->type           = $type;
		$this->children       = $children;
		$this->parent_id      = $parent_id;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return $this->type;
	}

	public function get_parent_id(): int {
		return $this->parent_id ?? 0;
	}

	public function get_children(): array {
		return $this->children;
	}

	public function get_stock_quantity(): int {
		return $this->stock_quantity;
	}

	public function set_stock_quantity( int $qty ): void {
		$this->stock_quantity = $qty;
	}

	public function is_in_stock(): bool {
		return $this->stock_quantity > 0;
	}

	public function managing_stock(): bool {
		return true;
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
		global $mock_products;
		$mock_products[ $this->id ] = $this;
		return $this->id;
	}
}

function wc_get_product( $product_id ) {
	global $mock_products;
	if ( is_object( $product_id ) ) {
		return $product_id;
	}
	return $mock_products[ $product_id ] ?? null;
}

// -------------------------------------------------------------
// TEST SUITE: Lifecycle Override Behavioral Assertions
// -------------------------------------------------------------

echo "Running Statement Lifecycle Override Behavioral Test Suite...\n";

// 1. Invariant: Metadata::set_release_state() is strictly forward-only
$prod_soldout = new MockTestProduct( 101, array( '_statement_release_state' => ReleaseState::SOLD_OUT ), 0 );
$mock_products[101] = $prod_soldout;

$res1 = Metadata::set_release_state( $prod_soldout, ReleaseState::LIVE );
stmt_assert( false === $res1, 'Metadata::set_release_state() MUST reject SOLD_OUT -> LIVE' );
stmt_assert_same( ReleaseState::SOLD_OUT, Metadata::get_release_state( $prod_soldout ), 'Product release state remains SOLD_OUT after rejected transition' );

$prod_archived = new MockTestProduct( 102, array( '_statement_release_state' => ReleaseState::ARCHIVED ), 0 );
$mock_products[102] = $prod_archived;

$res2 = Metadata::set_release_state( $prod_archived, ReleaseState::LIVE );
stmt_assert( false === $res2, 'Metadata::set_release_state() MUST reject ARCHIVED -> LIVE' );
stmt_assert_same( ReleaseState::ARCHIVED, Metadata::get_release_state( $prod_archived ), 'Product release state remains ARCHIVED after rejected transition' );

// 2. Privileged Override: SOLD_OUT -> LIVE succeeds when stock > 0 and reason provided
$prod_soldout->set_stock_quantity( 8 );
$override_res = LifecycleOverrideService::override_state( $prod_soldout, ReleaseState::LIVE, 1, 'Restocked 8 units from studio reserve' );

stmt_assert( true === ( $override_res['success'] ?? false ), 'LifecycleOverrideService successfully reopens SOLD_OUT -> LIVE' );
stmt_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $prod_soldout ), 'Persisted state is verified as LIVE' );
stmt_assert_same( ReleaseState::SOLD_OUT, $override_res['from_state'], 'Audit reports correct from_state' );
stmt_assert_same( ReleaseState::LIVE, $override_res['to_state'], 'Audit reports correct to_state' );

// Check audit log
$audit_log = LifecycleV2Admin::get_audit_log();
stmt_assert( ! empty( $audit_log ), 'Audit log entry recorded on verified success' );
$last_audit = end( $audit_log );
stmt_assert_same( 101, (int) $last_audit['product_id'], 'Audit log records product ID' );
stmt_assert_same( 1, (int) $last_audit['actor_id'], 'Audit log records actor ID' );
stmt_assert_same( 'Restocked 8 units from studio reserve', $last_audit['reason'], 'Audit log records exact reason' );
stmt_assert_same( 8, (int) $last_audit['stock_after'], 'Audit log records stock after' );
stmt_assert_same( true, $last_audit['success'], 'Audit log marks success = true' );

// 3. Privileged Override: ARCHIVED -> LIVE succeeds when stock > 0 and reason provided
$prod_archived->set_stock_quantity( 5 );
$override_res_arc = LifecycleOverrideService::override_state( $prod_archived, ReleaseState::LIVE, 1, 'Historical retrospective release allocation' );

stmt_assert( true === ( $override_res_arc['success'] ?? false ), 'LifecycleOverrideService successfully reopens ARCHIVED -> LIVE' );
stmt_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $prod_archived ), 'Persisted state is verified as LIVE' );

// 4. Precondition Failure: Reopen rejected when stock <= 0
$prod_no_stock = new MockTestProduct( 103, array( '_statement_release_state' => ReleaseState::SOLD_OUT ), 0 );
$mock_products[103] = $prod_no_stock;

$zero_stock_res = LifecycleOverrideService::override_state( $prod_no_stock, ReleaseState::LIVE, 1, 'Attempting zero stock reopen' );
stmt_assert( false === ( $zero_stock_res['success'] ?? true ), 'LifecycleOverrideService rejects reopen when stock <= 0' );
stmt_assert_same( ReleaseState::SOLD_OUT, Metadata::get_release_state( $prod_no_stock ), 'State remains SOLD_OUT when stock <= 0' );

// 5. Precondition Failure: Reopen rejected when reason is empty or whitespace
$prod_soldout->update_meta_data( '_statement_release_state', ReleaseState::SOLD_OUT );
$prod_soldout->set_stock_quantity( 5 );

$empty_reason_res = LifecycleOverrideService::override_state( $prod_soldout, ReleaseState::LIVE, 1, '   ' );
stmt_assert( false === ( $empty_reason_res['success'] ?? true ), 'LifecycleOverrideService rejects override with empty reason' );

// 6. Variation object canonicalizes to parent product
$parent_prod = new MockTestProduct( 200, array( '_statement_release_state' => ReleaseState::SOLD_OUT ), 10, 'variable', array( 201, 202 ) );
$child_var   = new MockTestProduct( 201, array(), 5, 'variation', array(), 200 );
$mock_products[200] = $parent_prod;
$mock_products[201] = $child_var;

$var_override_res = LifecycleOverrideService::override_state( $child_var, ReleaseState::LIVE, 1, 'Reopening variable parent via variation' );
stmt_assert( true === ( $var_override_res['success'] ?? false ), 'LifecycleOverrideService handles variation input' );
stmt_assert_same( 200, (int) $var_override_res['release_owner_id'], 'Release owner canonicalized to parent product ID 200' );
stmt_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $parent_prod ), 'Parent product release state updated to LIVE' );

// 7. PRIVATE_ACCESS Transition: Requires assigned Drop and valid future DropConfig
$prod_private_target = new MockTestProduct( 301, array( '_statement_release_state' => ReleaseState::SOLD_OUT ), 5 );
$mock_products[301]  = $prod_private_target;

// Without Drop assigned -> Rejects
$no_drop_res = LifecycleOverrideService::override_state( $prod_private_target, ReleaseState::PRIVATE_ACCESS, 1, 'Set private access' );
stmt_assert( false === ( $no_drop_res['success'] ?? true ), 'Rejects PRIVATE_ACCESS without assigned Drop' );

// Assign Drop with invalid / expired config -> Rejects
$mock_the_terms[301] = array(
	(object) array(
		'term_id'  => 505,
		'name'     => 'Drop 505',
		'taxonomy' => Taxonomy::KEY,
	),
);
$mock_drop_configs[505] = array(
	DropConfig::META_CLOSES_AT     => gmdate( 'Y-m-d H:i:s', time() - 3600 ), // expired
	DropConfig::META_DURATION      => 2,
	DropConfig::META_DURATION_UNIT => 'hours',
);

$expired_drop_res = LifecycleOverrideService::override_state( $prod_private_target, ReleaseState::PRIVATE_ACCESS, 1, 'Set private access expired drop' );
stmt_assert( false === ( $expired_drop_res['success'] ?? true ), 'Rejects PRIVATE_ACCESS when DropConfig is expired' );

// Assign valid future DropConfig -> Succeeds
$mock_drop_configs[505] = array(
	DropConfig::META_CLOSES_AT     => gmdate( 'Y-m-d H:i:s', time() + 7200 ),
	DropConfig::META_DURATION      => 2,
	DropConfig::META_DURATION_UNIT => 'hours',
);

$valid_private_res = LifecycleOverrideService::override_state( $prod_private_target, ReleaseState::PRIVATE_ACCESS, 1, 'Setting private access with valid Drop' );
stmt_assert( true === ( $valid_private_res['success'] ?? false ), 'PRIVATE_ACCESS override succeeds with valid assigned Drop and future DropConfig' );
stmt_assert_same( ReleaseState::PRIVATE_ACCESS, Metadata::get_release_state( $prod_private_target ), 'Product release state is PRIVATE_ACCESS' );

// 8. Ordinary Forward Transitions through LifecycleOverrideService
$prod_fwd = new MockTestProduct( 401, array( '_statement_release_state' => ReleaseState::UPCOMING ), 10 );
$mock_products[401] = $prod_fwd;

$fwd_res = LifecycleOverrideService::override_state( $prod_fwd, ReleaseState::LIVE, 1, 'Launching product' );
stmt_assert( true === ( $fwd_res['success'] ?? false ), 'Forward transition UPCOMING -> LIVE succeeds' );
stmt_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $prod_fwd ), 'State is LIVE' );

$sold_res = LifecycleOverrideService::override_state( $prod_fwd, ReleaseState::SOLD_OUT, 1, 'Sell out' );
stmt_assert( true === ( $sold_res['success'] ?? false ), 'Forward transition LIVE -> SOLD_OUT succeeds' );
stmt_assert_same( ReleaseState::SOLD_OUT, Metadata::get_release_state( $prod_fwd ), 'State is SOLD_OUT' );

// 9. Normal WooCommerce stock edit DOES NOT change release state
$terminal_prod = new MockTestProduct( 501, array( '_statement_release_state' => ReleaseState::SOLD_OUT ), 0 );
$mock_products[501] = $terminal_prod;

// Admin edits stock directly in WooCommerce
$terminal_prod->set_stock_quantity( 25 );
$terminal_prod->save();

stmt_assert_same( ReleaseState::SOLD_OUT, Metadata::get_release_state( $terminal_prod ), 'Editing stock quantity to 25 DOES NOT change release state from SOLD_OUT' );

// 10. Historical Order Provenance remains frozen across overrides
$order_item_meta = array(
	Provenance::META_VERSION        => Provenance::SCHEMA_VERSION,
	Provenance::META_RELEASE_STATE  => ReleaseState::SOLD_OUT,
	Provenance::META_PRODUCT_ID     => 101,
	Provenance::META_EDITION_LABEL  => 'INITIAL / DROP 001',
	Provenance::META_PURCHASED_AT   => '2026-08-15 12:00:00',
);

// Release 101 is reopened to LIVE
$prod_reopened = $mock_products[101];
$prod_reopened->set_stock_quantity( 10 );
LifecycleOverrideService::override_state( $prod_reopened, ReleaseState::LIVE, 1, 'Reopening product 101 to test provenance immutability' );

stmt_assert_same( ReleaseState::LIVE, Metadata::get_release_state( $prod_reopened ), 'Current release state is LIVE' );
// Historical order line provenance remains frozen at purchase state
stmt_assert_same( ReleaseState::SOLD_OUT, $order_item_meta[ Provenance::META_RELEASE_STATE ], 'Order line provenance release state remains frozen at SOLD_OUT' );
stmt_assert_same( 'INITIAL / DROP 001', $order_item_meta[ Provenance::META_EDITION_LABEL ], 'Order line provenance edition label remains immutable' );

echo "PASS: All {$statement_assertions} Statement Lifecycle Override behavioral assertions passed cleanly.\n";
