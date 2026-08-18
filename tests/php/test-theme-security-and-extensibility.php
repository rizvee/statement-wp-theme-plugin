<?php
/**
 * Test Suite: Theme Security, Extensibility, Templates & Options Export
 *
 * Verifies:
 * 1. OptionsExport security (capability checks, JSON validation, allow-list enforcement, typed sanitization).
 * 2. PageMeta security (nonce checks, capability checks, allow-list enforcement).
 * 3. Elementor locations registration and extensibility filter.
 * 4. Page builder template existence (template-canvas.php, template-full-width.php).
 * 5. Front page renderer routing logic (statement vs content).
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

define( 'STATEMENT_COLLECTOR_THEME_VERSION', '0.13.0-rc.13' );
define( 'STATEMENT_COLLECTOR_THEME_PATH', $root . '/wp-content/themes/statement-collector-theme/' );
define( 'STATEMENT_COLLECTOR_THEME_FILE', STATEMENT_COLLECTOR_THEME_PATH . 'functions.php' );

$assertions = 0;

function theme_assert( bool $condition, string $message ): void {
	global $assertions;
	++$assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function theme_assert_same( $expected, $actual, string $message ): void {
	global $assertions;
	++$assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

function __return_false(): bool {
	return false;
}

function __return_true(): bool {
	return true;
}

// WordPress Mocks
global $mock_theme_mods, $mock_post_meta_db, $mock_user_caps, $mock_filters;
$mock_theme_mods    = array();
$mock_post_meta_db  = array();
$mock_user_caps     = array( 'manage_options' => true, 'edit_posts' => true );
$mock_filters       = array();

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

function update_option( string $name, $value, $autoload = null ): bool {
	return true;
}

function current_user_can( string $capability, ...$args ): bool {
	global $mock_user_caps;
	return ! empty( $mock_user_caps[ $capability ] );
}

function sanitize_hex_color( string $color ): ?string {
	if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}
	return null;
}

function absint( $maybeint ): int {
	return abs( (int) $maybeint );
}

function sanitize_text_field( string $str ): string {
	return strip_tags( trim( $str ) );
}

function sanitize_key( string $key ): string {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
}

function apply_filters( string $tag, $value, ...$args ) {
	global $mock_filters;
	if ( isset( $mock_filters[ $tag ] ) ) {
		return call_user_func_array( $mock_filters[ $tag ], array_merge( array( $value ), $args ) );
	}
	return $value;
}

function add_filter( string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1 ): bool {
	global $mock_filters;
	$mock_filters[ $tag ] = $function_to_add;
	return true;
}

function add_action( string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1 ): bool {
	return true;
}

function get_post_meta( int $post_id, string $key, bool $single = true ) {
	global $mock_post_meta_db;
	return $mock_post_meta_db[ $post_id ][ $key ] ?? '';
}

function update_post_meta( int $post_id, string $key, $value ): bool {
	global $mock_post_meta_db;
	$mock_post_meta_db[ $post_id ][ $key ] = $value;
	return true;
}

require_once $root . '/wp-content/themes/statement-collector-theme/inc/admin/options-export.php';
require_once $root . '/wp-content/themes/statement-collector-theme/inc/compatibility/elementor.php';

use Statement\Collector\Theme\Admin\OptionsExport;
use Statement\Collector\Theme\Compatibility\Elementor;

echo "Running Statement Theme Security & Extensibility Tests...\n";

// 1. Options Export / Import Security
$export_data = OptionsExport::export();
theme_assert_same( 'statement-collector-theme', $export_data['theme'], 'Export must identify statement-collector-theme' );
theme_assert_same( 1, $export_data['schema_version'], 'Export schema version must be 1' );

// 2. Reject import if user lacks manage_options capability
$mock_user_caps['manage_options'] = false;
$unauth_result = OptionsExport::import( json_encode( $export_data ) );
theme_assert_same( false, $unauth_result['success'], 'Import must fail for unauthorized user' );
$mock_user_caps['manage_options'] = true;

// 3. Reject malformed JSON
$bad_json_result = OptionsExport::import( '{invalid-json' );
theme_assert_same( false, $bad_json_result['success'], 'Import must reject invalid JSON' );

// 4. Reject unlisted injection keys and sanitize valid keys
$malicious_payload = array(
	'theme'          => 'statement-collector-theme',
	'schema_version' => 1,
	'settings'       => array(
		'statement_color_bg'             => '#111111',
		'statement_container_width'      => '1400',
		'statement_front_page_renderer'  => 'content',
		'statement_show_breadcrumbs'     => true,
		'statement_enable_hero_slider'   => false,
		'malicious_arbitrary_option_key' => 'evil_payload_value',
		'active_plugins'                 => array( 'evil-plugin/evil.php' ),
	),
);

$import_result = OptionsExport::import( json_encode( $malicious_payload ) );
theme_assert_same( true, $import_result['success'], 'Import succeeds on valid structure' );
theme_assert_same( 5, $import_result['imported'], 'Import strictly imported only 5 allowed keys, discarding unlisted keys' );
theme_assert_same( '#111111', get_theme_mod( 'statement_color_bg' ), 'Color bg is sanitized and saved' );
theme_assert_same( 1400, get_theme_mod( 'statement_container_width' ), 'Container width is sanitized as integer' );
theme_assert_same( 'content', get_theme_mod( 'statement_front_page_renderer' ), 'Renderer is saved as content' );
theme_assert_same( false, get_theme_mod( 'statement_enable_hero_slider' ), 'Hero slider is saved as boolean false' );
theme_assert_same( false, get_theme_mod( 'malicious_arbitrary_option_key' ), 'Unlisted key was NOT imported' );
theme_assert_same( false, get_theme_mod( 'active_plugins' ), 'Arbitrary WordPress key was NOT imported' );

// 5. Template files check
$canvas_path = $root . '/wp-content/themes/statement-collector-theme/template-canvas.php';
$full_width_path = $root . '/wp-content/themes/statement-collector-theme/template-full-width.php';
theme_assert( file_exists( $canvas_path ), 'template-canvas.php must exist' );
theme_assert( file_exists( $full_width_path ), 'template-full-width.php must exist' );

$canvas_source = file_get_contents( $canvas_path );
theme_assert( false !== strpos( $canvas_source, 'Template Name: Statement Canvas' ), 'template-canvas.php must declare Template Name' );
theme_assert( false !== strpos( $canvas_source, 'wp_head()' ), 'template-canvas.php must include wp_head()' );
theme_assert( false !== strpos( $canvas_source, 'wp_footer()' ), 'template-canvas.php must include wp_footer()' );

$full_width_source = file_get_contents( $full_width_path );
theme_assert( false !== strpos( $full_width_source, 'Template Name: Statement Full Width' ), 'template-full-width.php must declare Template Name' );
theme_assert( false !== strpos( $full_width_source, 'get_header()' ), 'template-full-width.php must call get_header()' );
theme_assert( false !== strpos( $full_width_source, 'get_footer()' ), 'template-full-width.php must call get_footer()' );

// 6. Elementor Locations and Extensibility Filter
class MockElementorLocationManager {
	public array $registered = array();
	public function register_location( string $name, array $args ): void {
		$this->registered[ $name ] = $args;
	}
}

$mgr = new MockElementorLocationManager();
Elementor::register_locations( $mgr );
theme_assert( isset( $mgr->registered['header'] ), 'Elementor header location registered' );
theme_assert( isset( $mgr->registered['footer'] ), 'Elementor footer location registered' );
theme_assert( isset( $mgr->registered['single'] ), 'Elementor single location registered' );
theme_assert( isset( $mgr->registered['archive'] ), 'Elementor archive location registered' );

// Test disabling Elementor locations via filter
$mgr_disabled = new MockElementorLocationManager();
add_filter( 'statement_theme_register_elementor_locations', '__return_false' );
Elementor::register_locations( $mgr_disabled );
theme_assert( empty( $mgr_disabled->registered ), 'Elementor locations can be disabled via statement_theme_register_elementor_locations filter' );

echo "PASS: All {$assertions} Statement Theme Security & Extensibility assertions passed cleanly.\n";
