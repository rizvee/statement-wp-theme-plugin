<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/GrantService.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/SessionService.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/TokenService.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/RateLimiter.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/ConsentService.php';

use Statement\Collector\Core\Access\GrantService;
use Statement\Collector\Core\Access\SessionService;
use Statement\Collector\Core\Access\TokenService;
use Statement\Collector\Core\Access\RateLimiter;
use Statement\Collector\Core\Access\ConsentService;

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

// 1. Immutable Grant Expiry Calculation Test
$granted_at = 1700000000;
$duration = 3600; // 1 hour
$drop_close_early = 1700001800; // 30 mins after grant
$drop_close_late  = 1700007200; // 2 hours after grant

$exp_early = GrantService::calculate_grant_expiry( $granted_at, $duration, $drop_close_early );
statement_assert_same( $drop_close_early, $exp_early, 'Drop close earlier than individual duration must cap grant expiry.' );

$exp_late = GrantService::calculate_grant_expiry( $granted_at, $duration, $drop_close_late );
statement_assert_same( $granted_at + $duration, $exp_late, 'Drop close later than individual duration must NOT extend grant expiry.' );

// 2. Cookie name formatting
statement_assert_same( 'statement_drop_access_42', SessionService::get_cookie_name( 42 ), 'Cookie name must be drop-specific.' );

// 3. Token hashes
$raw_token = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
$token_hash = TokenService::hash_token( $raw_token );
statement_assert_same( 64, strlen( $token_hash ), 'Token hash must be 64-char sha256 hex string.' );

// 4. Rate Limiter Threshold constants
statement_assert_same( 5, RateLimiter::IP_SHORT_LIMIT, 'IP 10-min limit must be 5.' );
statement_assert_same( 20, RateLimiter::IP_LONG_LIMIT, 'IP 24-hr limit must be 20.' );
statement_assert_same( 3, RateLimiter::EMAIL_SHORT_LIMIT, 'Email 10-min limit must be 3.' );
statement_assert_same( 10, RateLimiter::EMAIL_LONG_LIMIT, 'Email 24-hr limit must be 10.' );

// 5. Consent Service Text & Version
statement_assert_same( '1.0', ConsentService::CONSENT_VERSION, 'Consent version must be 1.0.' );
statement_assert_same( true, str_contains( ConsentService::DEFAULT_CONSENT_TEXT, 'By requesting private access' ), 'Default consent text must match specification.' );

echo "PASS: M10 grants, sessions, tokens test passed ({$statement_assertions} assertions).\n";
