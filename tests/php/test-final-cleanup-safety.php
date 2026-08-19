<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

// -------------------------------------------------------------------------
// Global Mocks
// -------------------------------------------------------------------------

$mock_options   = array();
$mock_posts     = array();
$mock_postmeta  = array();
$mock_terms     = array();
$mock_orders    = array();
$mock_actions   = array();
$mock_user_caps = array( 'manage_woocommerce' => true );

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( string $cap ): bool {
		global $mock_user_caps;
		return ! empty( $mock_user_caps[ $cap ] );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $name, $default = false ) {
		global $mock_options;
		return array_key_exists( $name, $mock_options ) ? $mock_options[ $name ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $name, $value ): bool {
		global $mock_options;
		$mock_options[ $name ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( string $name ): bool {
		global $mock_options;
		unset( $mock_options[ $name ] );
		return true;
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $id ) {
		global $mock_posts;
		$id = (int) $id;
		return $mock_posts[ $id ] ?? null;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( int $post_id, string $key = '', bool $single = false ) {
		global $mock_postmeta;
		if ( '' === $key ) {
			return $mock_postmeta[ $post_id ] ?? array();
		}
		$val = $mock_postmeta[ $post_id ][ $key ] ?? ( $single ? '' : array() );
		return $val;
	}
}

if ( ! function_exists( 'get_term_by' ) ) {
	function get_term_by( string $field, $value, string $taxonomy = '' ) {
		global $mock_terms;
		foreach ( $mock_terms as $t ) {
			if ( $taxonomy && $t->taxonomy !== $taxonomy ) {
				continue;
			}
			if ( 'slug' === $field && $t->slug === $value ) {
				return $t;
			}
			if ( 'id' === $field && $t->term_id === (int) $value ) {
				return $t;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ): bool {
		return false;
	}
}

if ( ! function_exists( 'wc_get_orders' ) ) {
	function wc_get_orders( array $args = array() ): array {
		global $mock_orders;
		$results = array();
		$filter_key = $args['meta_key'] ?? null;
		$filter_val = $args['meta_value'] ?? null;

		foreach ( $mock_orders as $ord ) {
			if ( null !== $filter_key ) {
				$val = $ord->get_meta( $filter_key );
				if ( $val !== $filter_val ) {
					continue;
				}
			}
			$results[] = $ord;
		}
		return $results;
	}
}

if ( ! class_exists( 'ActionScheduler_Store' ) ) {
	class ActionScheduler_Store {
		public const STATUS_PENDING = 'pending';
	}
}

if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
	function as_get_scheduled_actions( array $args = array(), string $return_type = 'ids' ): array {
		global $mock_actions;
		$matched = array();
		$hook = $args['hook'] ?? '';
		$target_args = $args['args'] ?? null;

		foreach ( $mock_actions as $id => $act ) {
			if ( $act['hook'] !== $hook ) {
				continue;
			}
			if ( null !== $target_args ) {
				if ( $act['args'] !== $target_args ) {
					continue;
				}
			}
			$matched[] = $id;
		}
		return $matched;
	}
}

if ( ! function_exists( 'as_unschedule_action' ) ) {
	function as_unschedule_action( string $hook, array $args = array(), string $group = '' ): void {
		global $mock_actions;
		foreach ( $mock_actions as $id => $act ) {
			if ( $act['hook'] === $hook && $act['args'] === $args ) {
				unset( $mock_actions[ $id ] );
			}
		}
	}
}

class MockOrder {
	public int $id;
	public string $order_number;
	public string $total;
	public array $meta = array();

	public function __construct( int $id, string $num, string $total, array $meta = array() ) {
		$this->id           = $id;
		$this->order_number = $num;
		$this->total        = $total;
		$this->meta         = $meta;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_order_number(): string {
		return $this->order_number;
	}

	public function get_total(): string {
		return $this->total;
	}

	public function get_meta( string $key ): string {
		return (string) ( $this->meta[ $key ] ?? '' );
	}
}

class MockWpdb {
	public string $prefix = 'wp_';
	public string $postmeta = 'wp_postmeta';
	public array $queries = array();

	public function prepare( string $query, ...$args ): string {
		return vsprintf( str_replace( '%s', "'%s'", str_replace( '%d', '%d', $query ) ), $args );
	}

	public function get_col( string $query ): array {
		$this->queries[] = $query;
		return array();
	}

	public function get_var( string $query ) {
		$this->queries[] = $query;
		if ( str_contains( $query, 'SHOW TABLES LIKE' ) ) {
			// Mock table exists
			preg_match( "/LIKE '([^']+)'/", $query, $m );
			return $m[1] ?? null;
		}
		return 0;
	}

	public function query( string $query ) {
		$this->queries[] = $query;
		return 0;
	}
}

global $wpdb;
$wpdb = new MockWpdb();

// Load Core & Fixture classes
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Schema.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/ReminderService.php';
require_once $root . '/tools/statement-integration-fixtures/src/FinalCleanupService.php';
require_once $root . '/tools/statement-integration-fixtures/src/CleanupService.php';

use Statement\Integration\Fixtures\FinalCleanupService;
use Statement\Collector\Core\Access\Schema as AccessSchema;
use Statement\Collector\Core\Access\ReminderService;

$safety_assertions = 0;

function safety_assert( $expected, $actual, string $message ): void {
	global $safety_assertions;
	++$safety_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// =========================================================================
// TEST 1: Schema Table Derivation and Zero Hard-Coded wp_ Prefix
// =========================================================================

$custom_prefix = 'custom_store_';
$derived_tables = AccessSchema::get_table_names( $custom_prefix );

safety_assert( 'custom_store_statement_access_grants', $derived_tables['grants'], 'Grants table uses dynamic prefix' );
safety_assert( 'custom_store_statement_access_sessions', $derived_tables['sessions'], 'Sessions table uses dynamic prefix' );
safety_assert( 'custom_store_statement_access_tokens', $derived_tables['tokens'], 'Tokens table uses dynamic prefix' );
safety_assert( 'custom_store_statement_access_rate_limits', $derived_tables['rate_limits'], 'Rate limits table uses dynamic prefix' );
safety_assert( 'custom_store_statement_consent_events', $derived_tables['consent'], 'Consent table uses dynamic prefix' );

// =========================================================================
// TEST 2: Product 213 Special Safety Test (Preserve vs Delete)
// =========================================================================

// Setup Mock Entities:
// 1. Product 213 = Live Monogram Jacquard Jacket (Client Demo / Production entity)
$p213 = (object) array(
	'ID'         => 213,
	'post_title' => 'Monogram Jacquard Jacket',
	'post_type'  => 'product',
);
$mock_posts[213] = $p213;
$mock_postmeta[213] = array(
	'_sku'                   => 'STMT-CD-D001-MJJ',
	'_statement_client_demo' => '1',
	'_statement_fixture'     => '',
);

// 2. Product 271 = Panelled Hood Jacket (Client Demo / Production entity)
$p271 = (object) array(
	'ID'         => 271,
	'post_title' => 'Panelled Hood Jacket',
	'post_type'  => 'product',
);
$mock_posts[271] = $p271;
$mock_postmeta[271] = array(
	'_sku'                   => 'STMT-CD-D001-PHJ',
	'_statement_client_demo' => '1',
	'_statement_fixture'     => '',
);

// 3. QA Product 501 = TEST — Variable Product (QA Test entity)
$p501 = (object) array(
	'ID'         => 501,
	'post_title' => 'TEST — Variable Product',
	'post_type'  => 'product',
);
$mock_posts[501] = $p501;
$mock_postmeta[501] = array(
	'_sku'                   => 'TEST-VP01-PARENT',
	'_statement_fixture'     => '1',
	'_statement_client_demo' => '',
);

// 4. Stale manifest includes historical ID 213 and QA ID 501
$mock_options['statement_fixture_manifest'] = array(
	'product_ids'   => array( 213, 501 ),
	'variation_ids' => array(),
);

// Run dry-run
$dry_run = FinalCleanupService::dry_run();

safety_assert( true, $dry_run['success'], 'Dry-run succeeds' );
safety_assert( true, $dry_run['is_safe_to_execute'], 'Dry-run is safe to execute' );

// Product 213 must be in PRESERVED entities and NOT in products_to_delete
$preserved_ids = array_column( $dry_run['preserved_entities'], 'id' );
$delete_ids    = array_column( $dry_run['products_to_delete'], 'id' );

safety_assert( true, in_array( 213, $preserved_ids, true ), 'Product 213 MUST be in PRESERVED set' );
safety_assert( true, in_array( 271, $preserved_ids, true ), 'Product 271 MUST be in PRESERVED set' );
safety_assert( false, in_array( 213, $delete_ids, true ), 'Product 213 MUST NEVER be in delete set' );
safety_assert( false, in_array( 271, $delete_ids, true ), 'Product 271 MUST NEVER be in delete set' );
safety_assert( true, in_array( 501, $delete_ids, true ), 'QA Product 501 is queued for deletion' );

// =========================================================================
// TEST 3: HPOS Order Safety (QA Orders vs Genuine Customer Orders)
// =========================================================================

// Order 1001: Genuine Customer Order (no _statement_is_qa_order meta)
$mock_orders[1001] = new MockOrder( 1001, '1001', '310.00', array(
	'_billing_email' => 'customer@example.com',
) );

// Order 1002: QA Order (_statement_is_qa_order = 'yes')
$mock_orders[1002] = new MockOrder( 1002, '1002', '10.00', array(
	'_statement_is_qa_order' => 'yes',
) );

$dry_run_orders = FinalCleanupService::dry_run();
$order_delete_ids = array_column( $dry_run_orders['orders_to_delete'], 'id' );

safety_assert( true, in_array( 1002, $order_delete_ids, true ), 'QA Order 1002 is queued for deletion' );
safety_assert( false, in_array( 1001, $order_delete_ids, true ), 'Customer Order 1001 MUST NEVER be targeted for deletion' );

// =========================================================================
// TEST 4: Action Scheduler Reminder Isolation
// =========================================================================

// Scheduled Action 51: QA grant reminder (grant_id = 701)
$mock_actions[51] = array(
	'hook' => ReminderService::ACTION_HOOK,
	'args' => array( 'grant_id' => 701 ),
);

// Scheduled Action 52: Genuine customer grant reminder (grant_id = 999)
$mock_actions[52] = array(
	'hook' => ReminderService::ACTION_HOOK,
	'args' => array( 'grant_id' => 999 ),
);

// Simulate QA grant 701 discovery via wpdb mock
class MockWpdbWithGrants extends MockWpdb {
	public function get_col( string $query ): array {
		$this->queries[] = $query;
		if ( str_contains( $query, 'statement_access_grants' ) ) {
			return array( '701' );
		}
		return array();
	}
}
$wpdb = new MockWpdbWithGrants();

$dry_run_as = FinalCleanupService::dry_run();
safety_assert( true, in_array( 51, $dry_run_as['action_scheduler_action_ids'], true ), 'QA reminder action 51 is queued for cancellation' );
safety_assert( false, in_array( 52, $dry_run_as['action_scheduler_action_ids'], true ), 'Genuine customer reminder action 52 MUST NEVER be cancelled' );

// =========================================================================
// TEST 5: Ambiguous Entity Aborts Execution Safely
// =========================================================================

// Create an ambiguous entity (lacks clear QA signals and lacks demo metadata)
$p999 = (object) array(
	'ID'         => 999,
	'post_title' => 'Custom User Draft Product',
	'post_type'  => 'product',
);
$mock_posts[999] = $p999;
$mock_postmeta[999] = array(
	'_sku' => 'UNKNOWN-SKU',
);
$mock_options['statement_fixture_manifest']['product_ids'][] = 999;

$dry_run_ambig = FinalCleanupService::dry_run();
safety_assert( false, $dry_run_ambig['is_safe_to_execute'], 'Ambiguous entity marks is_safe_to_execute = false' );
safety_assert( true, ! empty( $dry_run_ambig['ambiguities'] ), 'Ambiguity list contains warning' );

// Attempt execution with ambiguity
$exec_result = FinalCleanupService::execute_cleanup();
safety_assert( false, $exec_result['success'], 'Execute cleanup must abort when ambiguities exist' );
safety_assert( true, str_contains( $exec_result['message'], 'Cleanup aborted' ), 'Execution abort message returned' );

// =========================================================================
// TEST 6: Capability Guard
// =========================================================================

$mock_user_caps['manage_woocommerce'] = false;
$unauth_dry = FinalCleanupService::dry_run();
safety_assert( false, $unauth_dry['is_safe_to_execute'], 'Unprivileged user cannot run dry-run' );

$unauth_exec = FinalCleanupService::execute_cleanup();
safety_assert( false, $unauth_exec['success'], 'Unprivileged user cannot execute cleanup' );

echo "PASS: FinalCleanupService safety and deterministic preservation tests passed ({$safety_assertions} assertions).\n";
