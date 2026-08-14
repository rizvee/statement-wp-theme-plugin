<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/MakeDropLive.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Catalog/Visibility.php';

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Access\MakeDropLive;
use Statement\Collector\Core\Catalog\Visibility;

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

// 1. REST API and Store API query protection filtering
$public_args = Visibility::filter_public_rest_query( array( 'post_type' => 'product' ) );
statement_assert_same( true, is_array( $public_args['meta_query'] ?? null ), 'REST query filter must append meta_query array.' );
statement_assert_same( Metadata::RELEASE_STATE_KEY, $public_args['meta_query'][0]['key'], 'REST query filter must query release state key.' );
statement_assert_same( ReleaseState::LIVE, $public_args['meta_query'][0]['value'], 'REST query filter must restrict to LIVE state for public requests.' );

$store_api_args = Visibility::filter_public_store_api_query( array( 'post_type' => 'product' ) );
statement_assert_same( ReleaseState::LIVE, $store_api_args['meta_query'][0]['value'], 'Store API query filter must restrict to LIVE state for public requests.' );

// 2. MakeDropLive Atomicity & Rollback Verification
class MockProductForAtomicity {
	public int $id;
	public string $state = ReleaseState::PRIVATE_ACCESS;
	public bool $should_fail_save = false;

	public function __construct( int $id, bool $should_fail_save = false ) {
		$this->id = $id;
		$this->should_fail_save = $should_fail_save;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_meta( string $key, bool $single = true ): string {
		return $this->state;
	}

	public function update_meta_data( string $key, string $val ): void {
		$this->state = $val;
	}

	public function save(): bool {
		if ( $this->should_fail_save ) {
			return false;
		}
		return true;
	}
}

// Mock functions in global namespace for test fixture
$GLOBALS['mock_products'] = array(
	101 => new MockProductForAtomicity( 101, false ),
	102 => new MockProductForAtomicity( 102, true ), // Product 102 fails save
);

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $cap ): bool {
		return true;
	}
}

if ( ! function_exists( 'get_posts' ) ) {
	function get_posts( $args ): array {
		return array( 101, 102 );
	}
}

if ( ! function_exists( 'wc_get_product' ) ) {
	function wc_get_product( $id ) {
		return $GLOBALS['mock_products'][ $id ] ?? null;
	}
}

// Execute MakeDropLive with injected failure on Product 102
$result = MakeDropLive::execute( 50, time() );

statement_assert_same( false, $result['ok'], 'MakeDropLive execute must return ok=false when product save fails midway.' );
statement_assert_same( 0, $result['transitioned_count'], 'Transitioned count must be 0 when atomic rollback occurs.' );
statement_assert_same( ReleaseState::PRIVATE_ACCESS, Metadata::get_release_state( $GLOBALS['mock_products'][101] ), 'Product 101 state must be rolled back to PRIVATE_ACCESS after failure.' );
statement_assert_same( ReleaseState::PRIVATE_ACCESS, Metadata::get_release_state( $GLOBALS['mock_products'][102] ), 'Product 102 state must remain PRIVATE_ACCESS.' );

echo "PASS: M10 atomicity & API protection test passed ({$statement_assertions} assertions).\n";
