<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Schema.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Precheck.php';

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\Precheck;

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

// 1. Duration unit conversions
statement_assert_same( 300, DropConfig::convert_to_seconds( 5, 'minutes' ), '5 minutes = 300 seconds.' );
statement_assert_same( 7200, DropConfig::convert_to_seconds( 2, 'hours' ), '2 hours = 7200 seconds.' );
statement_assert_same( 259200, DropConfig::convert_to_seconds( 3, 'days' ), '3 days = 259200 seconds.' );
statement_assert_same( 0, DropConfig::convert_to_seconds( 1, 'invalid' ), 'Invalid unit returns 0.' );

// 2. Drop config validation
$now = 1700000000;
$valid_config = array(
	'closes_at'           => '2026-12-31 23:59:59',
	'closes_at_ts'        => 1798761599,
	'duration'            => 4,
	'duration_unit'       => 'hours',
	'send_access_email'   => 'yes',
	'reminder_enabled'    => 'yes',
	'reminder_delay'      => 1,
	'reminder_delay_unit' => 'hours',
);

statement_assert_same( true, DropConfig::is_config_valid( $valid_config, $now ), 'Valid drop config must pass validation.' );

$invalid_closes_at = $valid_config;
$invalid_closes_at['closes_at_ts'] = 1600000000; // Past date
statement_assert_same( false, DropConfig::is_config_valid( $invalid_closes_at, $now ), 'Past closing time must fail validation.' );

$invalid_duration = $valid_config;
$invalid_duration['duration'] = 0;
statement_assert_same( false, DropConfig::is_config_valid( $invalid_duration, $now ), 'Zero duration must fail validation.' );

echo "PASS: M10 drop config & precheck test passed ({$statement_assertions} assertions).\n";
