<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/GrantService.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/SessionService.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/EligibilityService.php';

use Statement\Collector\Core\Access\EligibilityService;
use Statement\Collector\Core\Release\ReleaseState;

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

// 1. Static eligibility rule checks
statement_assert_same( true, EligibilityService::is_state_commerce_eligible( ReleaseState::LIVE, null ), 'LIVE state is always commerce eligible.' );
statement_assert_same( false, EligibilityService::is_state_commerce_eligible( ReleaseState::SOLD_OUT, null ), 'SOLD_OUT is always blocked.' );
statement_assert_same( false, EligibilityService::is_state_commerce_eligible( ReleaseState::ARCHIVED, null ), 'ARCHIVED is always blocked.' );
statement_assert_same( false, EligibilityService::is_state_commerce_eligible( ReleaseState::UPCOMING, null ), 'UPCOMING is always blocked.' );

// PRIVATE_ACCESS without session context => false
statement_assert_same( false, EligibilityService::is_state_commerce_eligible( ReleaseState::PRIVATE_ACCESS, null ), 'PRIVATE_ACCESS without valid session context must be blocked.' );

// PRIVATE_ACCESS with valid session context => true
$valid_context = array(
	'valid'        => true,
	'drop_term_id' => 10,
	'grant_id'     => 1,
);
statement_assert_same( true, EligibilityService::is_state_commerce_eligible( ReleaseState::PRIVATE_ACCESS, $valid_context ), 'PRIVATE_ACCESS with valid session context must be eligible.' );

echo "PASS: M10 eligibility service test passed ({$statement_assertions} assertions).\n";
