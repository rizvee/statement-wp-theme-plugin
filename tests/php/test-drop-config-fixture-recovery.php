<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

// Mock WordPress options & term meta storage
$GLOBALS['_mock_options']   = array();
$GLOBALS['_mock_term_meta'] = array();
$GLOBALS['_mock_terms']     = array();
$GLOBALS['_mock_products']  = array();

function get_option( $key, $default = false ) {
	return $GLOBALS['_mock_options'][ $key ] ?? $default;
}

function update_option( $key, $val ) {
	$GLOBALS['_mock_options'][ $key ] = $val;
	return true;
}

function delete_option( $key ) {
	unset( $GLOBALS['_mock_options'][ $key ] );
	return true;
}

function get_term_meta( $term_id, $key, $single = true ) {
	return $GLOBALS['_mock_term_meta'][ $term_id ][ $key ] ?? '';
}

function update_term_meta( $term_id, $key, $val ) {
	$current = $GLOBALS['_mock_term_meta'][ $term_id ][ $key ] ?? null;
	if ( $current === $val ) {
		return false; // WordPress returns false if value unchanged
	}
	$GLOBALS['_mock_term_meta'][ $term_id ][ $key ] = $val;
	return true;
}

function delete_term_meta( $term_id, $key ) {
	unset( $GLOBALS['_mock_term_meta'][ $term_id ][ $key ] );
	return true;
}

function sanitize_text_field( $str ) {
	return trim( (string) $str );
}

function esc_html( $str ) {
	return (string) $str;
}

function wp_unslash( $val ) {
	return $val;
}

function wp_timezone() {
	return new \DateTimeZone( 'UTC' );
}

function wp_date( $format, $timestamp = null ) {
	return gmdate( $format, $timestamp ?? time() );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/SecretVault.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfigAdmin.php';
require_once $root . '/tools/statement-integration-fixtures/src/PrivateFixtureService.php';

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\DropConfigAdmin;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;
use Statement\Integration\Fixtures\PrivateFixtureService;

$statement_assertions = 0;

function statement_assert( $condition, string $message ): void {
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

// ----------------------------------------------------
// 1. DropConfig::save_config and get_config round trip
// ----------------------------------------------------
$term_id = 100;
$closes_at = '2026-10-15 12:00:00';

$ok = DropConfig::save_config(
	$term_id,
	array(
		'closes_at'           => $closes_at,
		'duration'            => 2,
		'duration_unit'       => 'hours',
		'send_access_email'   => 'no',
		'reminder_enabled'    => 'no',
		'reminder_delay'      => 1,
		'reminder_delay_unit' => 'hours',
	)
);

statement_assert( true === $ok, 'save_config must return true for valid configuration' );

$config = DropConfig::get_config( $term_id );
statement_assert( null !== $config, 'get_config must return non-null array' );
statement_assert_same( '2026-10-15 12:00:00', $config['closes_at'], 'closes_at stored in normalized UTC' );
statement_assert_same( 2, $config['duration'], 'duration must be 2' );
statement_assert_same( 'hours', $config['duration_unit'], 'duration_unit must be hours' );
statement_assert_same( 7200, $config['duration_seconds'], 'duration_seconds must equal 7200' );
statement_assert_same( 'no', $config['send_access_email'], 'send_access_email must be no' );
statement_assert_same( 'no', $config['reminder_enabled'], 'reminder_enabled must be no' );

// ----------------------------------------------------
// 2. Unchanged re-save succeeds semantically
// ----------------------------------------------------
$re_save = DropConfig::save_config(
	$term_id,
	array(
		'closes_at'           => $closes_at,
		'duration'            => 2,
		'duration_unit'       => 'hours',
		'send_access_email'   => 'no',
		'reminder_enabled'    => 'no',
		'reminder_delay'      => 1,
		'reminder_delay_unit' => 'hours',
	)
);
statement_assert( true === $re_save, 'Re-saving identical config must succeed even when update_term_meta returns false' );

// ----------------------------------------------------
// 3. DropConfig validation failures
// ----------------------------------------------------
$bad_term = DropConfig::save_config( 0, array( 'closes_at' => $closes_at, 'duration' => 2, 'duration_unit' => 'hours' ) );
statement_assert( false === $bad_term, 'Invalid term ID <= 0 must fail' );

$bad_close = DropConfig::save_config( $term_id, array( 'closes_at' => '', 'duration' => 2, 'duration_unit' => 'hours' ) );
statement_assert( false === $bad_close, 'Empty closes_at must fail' );

$bad_dur = DropConfig::save_config( $term_id, array( 'closes_at' => $closes_at, 'duration' => 0, 'duration_unit' => 'hours' ) );
statement_assert( false === $bad_dur, 'Zero duration must fail' );

$bad_unit = DropConfig::save_config( $term_id, array( 'closes_at' => $closes_at, 'duration' => 2, 'duration_unit' => 'weeks' ) );
statement_assert( false === $bad_unit, 'Disallowed duration unit must fail' );

$bad_reminder = DropConfig::save_config(
	$term_id,
	array(
		'closes_at'           => $closes_at,
		'duration'            => 2,
		'duration_unit'       => 'hours',
		'reminder_enabled'    => 'yes',
		'reminder_delay'      => 0, // invalid when enabled
		'reminder_delay_unit' => 'hours',
	)
);
statement_assert( false === $bad_reminder, 'Enabled reminder with 0 delay must fail' );

// ----------------------------------------------------
// 4. Mock WC_Product and Metadata object contract
// ----------------------------------------------------
class MockWcProduct {
	public int $id = 213;
	public string $sku = 'TEST-PD01-PAJ';
	public string $type = 'simple';
	public array $meta = array();

	public function get_id(): int {
		return $this->id;
	}
	public function get_type(): string {
		return $this->type;
	}
	public function get_sku(): string {
		return $this->sku;
	}
	public function get_meta( $key, $single = true ) {
		return $this->meta[ $key ] ?? '';
	}
	public function update_meta_data( $key, $val ) {
		$this->meta[ $key ] = $val;
	}
	public function delete_meta_data( $key ) {
		unset( $this->meta[ $key ] );
	}
	public function save(): int {
		return $this->id;
	}
}

$mock_prod = new MockWcProduct();

// Test Metadata setters operate on WC_Product object
$set_ed = Metadata::set_edition_label( $mock_prod, 'Private Integration Edition' );
statement_assert( true === $set_ed, 'Metadata::set_edition_label must return true for valid product object' );
statement_assert_same( 'Private Integration Edition', Metadata::get_edition_label( $mock_prod ), 'Edition label must match' );

$set_rel = Metadata::set_release_state( $mock_prod, ReleaseState::PRIVATE_ACCESS );
statement_assert( true === $set_rel, 'Metadata::set_release_state must transition UPCOMING -> PRIVATE_ACCESS' );
statement_assert_same( ReleaseState::PRIVATE_ACCESS, Metadata::get_release_state( $mock_prod ), 'Release state must be PRIVATE_ACCESS' );

// Terminal transition block
$mock_terminal = new MockWcProduct();
Metadata::set_release_state( $mock_terminal, ReleaseState::SOLD_OUT );
$revert = Metadata::set_release_state( $mock_terminal, ReleaseState::PRIVATE_ACCESS );
statement_assert( false === $revert, 'Metadata::set_release_state MUST reject backwards transition from SOLD_OUT to PRIVATE_ACCESS' );

echo "PASS: DropConfig & Fixture Recovery behavior tests passed ({$statement_assertions} assertions).\n";
