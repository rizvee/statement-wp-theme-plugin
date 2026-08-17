<?php
/**
 * Test Suite: Theme Options Export/Import Security & Sanitization.
 *
 * Verifies capability enforcement, schema validation, and strict sanitization.
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

define( 'STATEMENT_COLLECTOR_THEME_VERSION', '0.13.0-rc.9' );

// Mock WordPress Environment
global $mock_theme_mods, $mock_options_db, $mock_current_user_caps;
$mock_theme_mods         = array();
$mock_options_db        = array();
$mock_current_user_caps = array( 'manage_options' => true );

function get_theme_mod( string $name, $default = false ) {
	global $mock_theme_mods;
	return $mock_theme_mods[ $name ] ?? $default;
}

function set_theme_mod( string $name, $value ): bool {
	global $mock_theme_mods;
	$mock_theme_mods[ $name ] = $value;
	return true;
}

function remove_theme_mod( string $name ): bool {
	global $mock_theme_mods;
	unset( $mock_theme_mods[ $name ] );
	return true;
}

function update_option( string $option, $value, $autoload = null ): bool {
	global $mock_options_db;
	$mock_options_db[ $option ] = $value;
	return true;
}

function get_option( string $option, $default = false ) {
	global $mock_options_db;
	return $mock_options_db[ $option ] ?? $default;
}

function current_user_can( string $capability ): bool {
	global $mock_current_user_caps;
	return ! empty( $mock_current_user_caps[ $capability ] );
}

function sanitize_hex_color( string $color ): ?string {
	if ( '' === $color ) {
		return '';
	}
	if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}
	return null;
}

function absint( $maybeint ): int {
	return abs( (int) $maybeint );
}

function sanitize_text_field( string $str ): string {
	return trim( strip_tags( $str ) );
}

require_once $root . '/wp-content/themes/statement-collector-theme/inc/admin/options-export.php';

use Statement\Collector\Theme\Admin\OptionsExport;

$assertions = 0;
function stmt_assert( bool $cond, string $msg ): void {
	global $assertions;
	++$assertions;
	if ( ! $cond ) {
		fwrite( STDERR, "FAIL: {$msg}\n" );
		exit( 1 );
	}
}

function stmt_assert_same( $expected, $actual, string $msg ): void {
	global $assertions;
	++$assertions;
	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$msg}\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

echo "Running Statement Theme Options Security Test Suite...\n";

// 1. Export structure
set_theme_mod( 'statement_color_bg', '#FAFAFA' );
set_theme_mod( 'statement_container_width', 1240 );

$export = OptionsExport::export();
stmt_assert_same( 'statement-collector-theme', $export['theme'], 'Export specifies theme' );
stmt_assert_same( 1, $export['schema_version'], 'Export specifies schema version 1' );
stmt_assert_same( '#FAFAFA', $export['settings']['statement_color_bg'], 'Export includes color setting' );
stmt_assert_same( 1240, $export['settings']['statement_container_width'], 'Export includes width setting' );

// 2. Unauthorized import rejection
$mock_current_user_caps['manage_options'] = false;
$import_res = OptionsExport::import( json_encode( $export ) );
stmt_assert( false === $import_res['success'], 'Import fails when user lacks manage_options' );
stmt_assert_same( 'Unauthorized capability', $import_res['errors'][0], 'Error message matches unauthorized' );

// 3. Authorized import with strict sanitization
$mock_current_user_caps['manage_options'] = true;
$malicious_payload = array(
	'theme'          => 'statement-collector-theme',
	'schema_version' => 1,
	'settings'       => array(
		'statement_color_bg'        => '<script>alert(1)</script>#112233',
		'statement_container_width' => '1400px; DROP TABLE',
		'unauthorized_key'          => 'secret_value',
		'statement_hero_slide_1_heading' => 'DROP 001 <img src=x onerror=alert(1)>',
	),
);

$import_res = OptionsExport::import( json_encode( $malicious_payload ) );
stmt_assert( true === $import_res['success'], 'Import succeeds with sanitized values' );
stmt_assert_same( 2, $import_res['imported'], 'Imported exactly 2 valid sanitized keys (invalid hex color rejected)' );
stmt_assert_same( 1400, get_theme_mod( 'statement_container_width' ), 'Container width cast to absint' );
stmt_assert_same( 'DROP 001', get_theme_mod( 'statement_hero_slide_1_heading' ), 'Heading tag stripped' );
stmt_assert( null === get_theme_mod( 'unauthorized_key', null ), 'Unauthorized key rejected' );

// 4. Reset to defaults
$reset_res = OptionsExport::reset_defaults();
stmt_assert( true === $reset_res, 'Reset returns true' );
stmt_assert( null === get_theme_mod( 'statement_color_bg', null ), 'Theme mods wiped on reset' );

echo "PASS: All {$assertions} Theme Options Security assertions passed cleanly.\n";
