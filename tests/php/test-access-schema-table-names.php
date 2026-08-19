<?php
/**
 * Test: Canonical Access Schema Table Names & Stale Name Prohibition
 */

declare( strict_types=1 );

namespace Statement\Collector\Core\Tests;

use Statement\Collector\Core\Access\Schema;
use Statement\Collector\Core\Access\ReminderService;
use Statement\Integration\Fixtures\FinalCleanupService;
use Statement\Integration\Fixtures\StatementQaGateway;

// Mock ABSPATH if not defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../../' );
}

require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Access/Schema.php';
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Access/ReminderService.php';
require_once __DIR__ . '/../../tools/statement-integration-fixtures/src/FinalCleanupService.php';

echo "Running Canonical Access Schema & Table Name Contract Tests...\n";

// Test 1: Schema::get_table_names() returns exact canonical M10 tables
$tables = Schema::get_table_names( 'wp_' );

assert( isset( $tables['grants'] ) && 'wp_statement_access_grants' === $tables['grants'], 'Grants table must be wp_statement_access_grants' );
assert( isset( $tables['sessions'] ) && 'wp_statement_access_sessions' === $tables['sessions'], 'Sessions table must be wp_statement_access_sessions' );
assert( isset( $tables['tokens'] ) && 'wp_statement_access_tokens' === $tables['tokens'], 'Tokens table must be wp_statement_access_tokens' );
assert( isset( $tables['rate_limits'] ) && 'wp_statement_access_rate_limits' === $tables['rate_limits'], 'Rate limits table must be wp_statement_access_rate_limits' );
assert( isset( $tables['consent'] ) && 'wp_statement_consent_events' === $tables['consent'], 'Consent table must be wp_statement_consent_events' );
assert( 5 === count( $tables ), 'Schema must define exactly 5 operational tables' );

echo "PASS 1: Schema::get_table_names() defines exact 5 canonical tables.\n";

// Test 2: Ensure forbidden stale table names are never in Schema
$forbidden_names = array(
	'statement_private_grants',
	'statement_private_sessions',
	'statement_private_drops',
	'statement_rate_limits',
	'statement_marketing_consents',
	'wp_statement_rate_limits',
);

foreach ( $tables as $table_name ) {
	foreach ( $forbidden_names as $forbidden ) {
		assert( ! str_contains( $table_name, $forbidden ), "Table name '{$table_name}' must not contain forbidden stale identifier '{$forbidden}'" );
	}
}

echo "PASS 2: Zero forbidden stale table names in Schema definition.\n";

// Test 3: Action Scheduler Hook Canonical Constant
assert( 'statement_private_access_reminder_action' === ReminderService::ACTION_HOOK, 'Reminder action hook must match canonical constant.' );

echo "PASS 3: Action Scheduler reminder hook constant matches canonical definition.\n";

// Test 4: FinalCleanupService QA identifiers and constants
assert( in_array( 'test-live-drop-01', FinalCleanupService::TEST_DROP_SLUGS, true ), 'FinalCleanupService must target test-live-drop-01' );
assert( in_array( 'test-private-drop-01', FinalCleanupService::TEST_DROP_SLUGS, true ), 'FinalCleanupService must target test-private-drop-01' );
assert( in_array( 'statement_fixture_manifest', FinalCleanupService::QA_OPTIONS, true ), 'FinalCleanupService must clean manifest option' );
assert( in_array( 'statement_qa_last_order_id', FinalCleanupService::QA_OPTIONS, true ), 'FinalCleanupService must clean QA order option' );

echo "PASS 4: FinalCleanupService constants correctly target test identifiers.\n";

echo "ALL 4 Access Schema Contract tests passed cleanly.\n";
