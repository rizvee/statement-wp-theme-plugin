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
require_once $root . '/wp-content/plugins/statement-collector-core/src/Cart/Integrity.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/EligibilityService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\Purchasability;
use Statement\Collector\Core\Product\Access;
use Statement\Collector\Core\Catalog\Visibility;
use Statement\Collector\Core\Cart\Integrity;
use Statement\Collector\Core\Access\EligibilityService;
use Statement\Collector\Core\PublicApi;

$statement_assertions = 0;

function statement_assert( bool $condition, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
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

class MockTerminalProduct {
	public int $id;
	public string $state;
	public int $stock_quantity;
	public string $stock_status;
	public ?int $parent_id;

	public function __construct( int $id, string $state, int $stock_quantity = 10, string $stock_status = 'instock', ?int $parent_id = null ) {
		$this->id             = $id;
		$this->state          = $state;
		$this->stock_quantity = $stock_quantity;
		$this->stock_status   = $stock_status;
		$this->parent_id      = $parent_id;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return null !== $this->parent_id ? 'variation' : 'simple';
	}

	public function get_parent_id(): int {
		return $this->parent_id ?? 0;
	}

	public function get_meta( string $key, bool $single = true ): string {
		return $this->state;
	}

	public function is_in_stock(): bool {
		return 'instock' === $this->stock_status && $this->stock_quantity > 0;
	}
}

// 1. LIFECYCLE TRANSITION IRREVERSIBILITY
statement_assert( ReleaseState::can_transition( ReleaseState::LIVE, ReleaseState::SOLD_OUT ), 'LIVE -> SOLD_OUT must be allowed.' );
statement_assert( ReleaseState::can_transition( ReleaseState::SOLD_OUT, ReleaseState::ARCHIVED ), 'SOLD_OUT -> ARCHIVED must be allowed.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::SOLD_OUT, ReleaseState::LIVE ), 'SOLD_OUT -> LIVE must be rejected.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::ARCHIVED, ReleaseState::SOLD_OUT ), 'ARCHIVED -> SOLD_OUT must be rejected.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::ARCHIVED, ReleaseState::LIVE ), 'ARCHIVED -> LIVE must be rejected.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::SOLD_OUT, ReleaseState::PRIVATE_ACCESS ), 'SOLD_OUT -> PRIVATE_ACCESS must be rejected.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::ARCHIVED, ReleaseState::PRIVATE_ACCESS ), 'ARCHIVED -> PRIVATE_ACCESS must be rejected.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::SOLD_OUT, ReleaseState::UPCOMING ), 'SOLD_OUT -> UPCOMING must be rejected.' );
statement_assert( ! ReleaseState::can_transition( ReleaseState::ARCHIVED, ReleaseState::UPCOMING ), 'ARCHIVED -> UPCOMING must be rejected.' );

// 2. PURCHASABILITY LOCKS EVEN WITH POSITIVE WOO STOCK
$live_p             = new MockTerminalProduct( 101, ReleaseState::LIVE, 10, 'instock' );
$sold_out_p          = new MockTerminalProduct( 102, ReleaseState::SOLD_OUT, 10, 'instock' );
$archived_p          = new MockTerminalProduct( 103, ReleaseState::ARCHIVED, 10, 'instock' );
$sold_out_variation = new MockTerminalProduct( 104, ReleaseState::SOLD_OUT, 5, 'instock', 102 );

statement_assert( Purchasability::filter_purchasable( true, $live_p ), 'LIVE product with positive stock must be purchasable.' );
statement_assert( ! Purchasability::filter_purchasable( true, $sold_out_p ), 'SOLD_OUT product with positive stock (10) MUST NOT be purchasable.' );
statement_assert( ! Purchasability::filter_purchasable( true, $archived_p ), 'ARCHIVED product with positive stock (10) MUST NOT be purchasable.' );

// 3. COMMERCE ELIGIBILITY & CART INTEGRITY LOCKS
statement_assert( EligibilityService::is_commerce_eligible( $live_p ), 'LIVE product must be commerce eligible.' );
statement_assert( ! EligibilityService::is_commerce_eligible( $sold_out_p ), 'SOLD_OUT product MUST NOT be commerce eligible.' );
statement_assert( ! EligibilityService::is_commerce_eligible( $archived_p ), 'ARCHIVED product MUST NOT be commerce eligible.' );

statement_assert( Integrity::is_cart_product_eligible( $live_p ), 'LIVE product must be cart eligible.' );
statement_assert( ! Integrity::is_cart_product_eligible( $sold_out_p ), 'SOLD_OUT product MUST NOT be cart eligible.' );
statement_assert( ! Integrity::is_cart_product_eligible( $archived_p ), 'ARCHIVED product MUST NOT be cart eligible.' );

// 4. DIRECT PRODUCT URL VIEWABILITY
statement_assert( Access::is_publicly_viewable( $live_p ), 'LIVE product must be publicly viewable.' );
statement_assert( Access::is_publicly_viewable( $sold_out_p ), 'SOLD_OUT product must remain permanently viewable on direct URL.' );
statement_assert( Access::is_publicly_viewable( $archived_p ), 'ARCHIVED product must remain permanently viewable on direct URL.' );

// 5. PUBLIC API HELPERS
statement_assert( PublicApi::is_sold_out( $sold_out_p ), 'PublicApi::is_sold_out must return true for SOLD_OUT.' );
statement_assert( ! PublicApi::is_sold_out( $live_p ), 'PublicApi::is_sold_out must return false for LIVE.' );
statement_assert( PublicApi::is_archived( $archived_p ), 'PublicApi::is_archived must return true for ARCHIVED.' );
statement_assert( ! PublicApi::is_archived( $sold_out_p ), 'PublicApi::is_archived must return false for SOLD_OUT.' );

// 6. STRUCTURED DATA OFFER AVAILABILITY
$live_offer     = Visibility::filter_structured_data_offer( array( 'availability' => 'https://schema.org/InStock' ), $live_p );
$sold_out_offer = Visibility::filter_structured_data_offer( array( 'availability' => 'https://schema.org/InStock' ), $sold_out_p );
$archived_offer = Visibility::filter_structured_data_offer( array( 'availability' => 'https://schema.org/InStock' ), $archived_p );

statement_assert_same( 'https://schema.org/InStock', $live_offer['availability'], 'LIVE product keeps InStock structured data.' );
statement_assert_same( 'https://schema.org/OutOfStock', $sold_out_offer['availability'], 'SOLD_OUT product overrides structured data to OutOfStock.' );
statement_assert_same( 'https://schema.org/OutOfStock', $archived_offer['availability'], 'ARCHIVED product overrides structured data to OutOfStock.' );

echo "PASS: M11 terminal & archive test passed ({$statement_assertions} assertions).\n";
