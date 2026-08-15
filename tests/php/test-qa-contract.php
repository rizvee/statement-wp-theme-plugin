<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	abstract class WC_Payment_Gateway {
		public $id = '';
		public $method_title = '';
		public $method_description = '';
		public $title = '';
		public $description = '';
		public $has_fields = false;
		public $enabled = 'no';
		public function init_form_fields() {}
		public function init_settings() {}
		public function is_available() { return true; }
	}
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/EligibilityService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/GrantService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/SessionService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/TokenService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/RateLimiter.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/ConsentService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/OrderAudit.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Order/Provenance.php';
require_once $root . '/tools/statement-integration-fixtures/src/PrivateFixtureService.php';
require_once $root . '/tools/statement-integration-fixtures/src/StatementQaGateway.php';

require_once $root . '/tools/statement-integration-fixtures/src/QaTestService.php';

use Statement\Integration\Fixtures\StatementQaGateway;
use Statement\Integration\Fixtures\QaTestService;
use Statement\Collector\Core\Access\TokenService;
use Statement\Collector\Core\Access\SessionService;

$qa_assertions = 0;

function qa_assert_same( $expected, $actual, string $message ): void {
	global $qa_assertions;
	++$qa_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// 1. Gateway ID and SKU assertion
$gw = new StatementQaGateway();
qa_assert_same( 'statement_qa_gateway', $gw->id, 'Gateway ID must match expected string' );
qa_assert_same( 'TEST-PD01-PAJ', StatementQaGateway::TARGET_SKU, 'Gateway target SKU must be TEST-PD01-PAJ' );

// 2. TokenService hashing and single-use security contract
$raw_token  = TokenService::generate_raw_token();
$token_hash = TokenService::hash_token( $raw_token );
qa_assert_same( 64, strlen( $token_hash ), 'Token hash must be 64-char sha256 hex string' );
qa_assert_same( false, $raw_token === $token_hash, 'Raw token must never equal token hash' );

// 3. SessionService hashing contract
$raw_session = SessionService::generate_raw_token();
$session_hash = SessionService::hash_token( $raw_session );
qa_assert_same( 64, strlen( $session_hash ), 'Session hash must be 64-char sha256 hex string' );

// 4. ReleaseState forward-only terminal transitions
qa_assert_same( true, \Statement\Collector\Core\Release\ReleaseState::can_transition( 'SOLD_OUT', 'ARCHIVED' ), 'SOLD_OUT can transition forward to ARCHIVED' );
qa_assert_same( false, \Statement\Collector\Core\Release\ReleaseState::can_transition( 'ARCHIVED', 'LIVE' ), 'ARCHIVED cannot transition backward to LIVE' );
qa_assert_same( false, \Statement\Collector\Core\Release\ReleaseState::can_transition( 'SOLD_OUT', 'LIVE' ), 'SOLD_OUT cannot transition backward to LIVE' );
qa_assert_same( false, \Statement\Collector\Core\Release\ReleaseState::can_transition( 'ARCHIVED', 'UPCOMING' ), 'ARCHIVED cannot transition backward to UPCOMING' );

echo "QA Contract PHP Unit Tests PASS: {$qa_assertions} assertions verified clean.\n";
