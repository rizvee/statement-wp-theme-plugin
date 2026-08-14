<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/Purchasability.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Access.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Catalog/Visibility.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\Purchasability;
use Statement\Collector\Core\Product\Access;
use Statement\Collector\Core\Catalog\Visibility;
use Statement\Collector\Core\PublicApi;

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

class MockTerminalProduct {
	public int $id;
	public string $state;

	public function __construct( int $id, string $state ) {
		$this->id    = $id;
		$this->state = $state;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_meta( string $key, bool $single = true ): string {
		return $this->state;
	}
}

// 1. Purchasability Locks
$live_p     = new MockTerminalProduct( 1, ReleaseState::LIVE );
$sold_out_p = new MockTerminalProduct( 2, ReleaseState::SOLD_OUT );
$archived_p = new MockTerminalProduct( 3, ReleaseState::ARCHIVED );

statement_assert_same( true, Purchasability::filter_purchasable( true, $live_p ), 'LIVE product must be purchasable.' );
statement_assert_same( false, Purchasability::filter_purchasable( true, $sold_out_p ), 'SOLD_OUT product must NOT be purchasable.' );
statement_assert_same( false, Purchasability::filter_purchasable( true, $archived_p ), 'ARCHIVED product must NOT be purchasable.' );

// 2. Direct Product URL Public Viewability
statement_assert_same( true, Access::is_publicly_viewable( $live_p ), 'LIVE product must be publicly viewable.' );
statement_assert_same( true, Access::is_publicly_viewable( $sold_out_p ), 'SOLD_OUT product must remain permanently viewable on direct URL.' );
statement_assert_same( true, Access::is_publicly_viewable( $archived_p ), 'ARCHIVED product must remain permanently viewable on direct URL.' );

// 3. Public API Terminal Helpers
statement_assert_same( true, PublicApi::is_sold_out( $sold_out_p ), 'PublicApi::is_sold_out must return true for SOLD_OUT product.' );
statement_assert_same( false, PublicApi::is_sold_out( $live_p ), 'PublicApi::is_sold_out must return false for LIVE product.' );
statement_assert_same( true, PublicApi::is_archived( $archived_p ), 'PublicApi::is_archived must return true for ARCHIVED product.' );
statement_assert_same( false, PublicApi::is_archived( $sold_out_p ), 'PublicApi::is_archived must return false for SOLD_OUT product.' );

// 4. Main Catalog Query Filtering
$public_args = Visibility::filter_public_rest_query( array() );
statement_assert_same( array( ReleaseState::LIVE, ReleaseState::SOLD_OUT ), Visibility::filter_public_rest_query( array() )['meta_query'][0]['value'] ?? null, 'Public query filter must include LIVE and SOLD_OUT states.' );

echo "PASS: M11 terminal & archive test passed ({$statement_assertions} assertions).\n";
