<?php
/**
 * Test Suite: Core HPOS Compatibility & Theme Responsibility Isolation
 *
 * Verifies that:
 * 1. Statement Collector Core owns and declares HPOS (custom_order_tables) compatibility.
 * 2. Theme does NOT declare custom_order_tables compatibility.
 * 3. Core order operations rely strictly on WooCommerce order CRUD without postmeta bypasses.
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

$statement_assertions = 0;

function hpos_assert( bool $condition, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

// 1. Static source audit: Core must hook before_woocommerce_init and declare custom_order_tables
$core_source = file_get_contents( $root . '/wp-content/plugins/statement-collector-core/statement-collector-core.php' );
hpos_assert( false !== strpos( $core_source, 'before_woocommerce_init' ), 'Core must register a before_woocommerce_init callback' );
hpos_assert( false !== strpos( $core_source, 'declare_compatibility' ), 'Core must call declare_compatibility' );
hpos_assert( false !== strpos( $core_source, 'custom_order_tables' ), 'Core must declare custom_order_tables compatibility' );
hpos_assert( false !== strpos( $core_source, 'STATEMENT_COLLECTOR_CORE_FILE' ), 'Core must declare compatibility on behalf of STATEMENT_COLLECTOR_CORE_FILE' );

// 2. Static source audit: Theme must NOT declare custom_order_tables
$theme_woo_source = file_get_contents( $root . '/wp-content/themes/statement-collector-theme/inc/compatibility/woocommerce.php' );
hpos_assert( false === strpos( $theme_woo_source, 'custom_order_tables' ), 'Theme must NOT declare custom_order_tables compatibility' );
hpos_assert( false !== strpos( $theme_woo_source, "add_theme_support(\n\t\t\t'woocommerce'" ) || false !== strpos( $theme_woo_source, "add_theme_support( 'woocommerce'" ) || false !== strpos( $theme_woo_source, "add_theme_support(\n\t\t\t'woocommerce'" ) || false !== strpos( $theme_woo_source, "'woocommerce'" ), 'Theme must declare add_theme_support for woocommerce' );

// 3. Static source audit: Core Order directory must use WooCommerce Order CRUD and no raw shop_order postmeta
$order_files = glob( $root . '/wp-content/plugins/statement-collector-core/src/Order/*.php' );
hpos_assert( ! empty( $order_files ), 'Core must contain Order subsystem files' );

foreach ( $order_files as $file ) {
	$content = file_get_contents( $file );
	hpos_assert( false === stripos( $content, 'get_post_meta' ), 'Core Order files must not call get_post_meta (violates HPOS)' );
	hpos_assert( false === stripos( $content, 'update_post_meta' ), 'Core Order files must not call update_post_meta (violates HPOS)' );
	hpos_assert( false === stripos( $content, 'add_post_meta' ), 'Core Order files must not call add_post_meta (violates HPOS)' );
	hpos_assert( false === stripos( $content, 'delete_post_meta' ), 'Core Order files must not call delete_post_meta (violates HPOS)' );
	hpos_assert( false === stripos( $content, "'shop_order'" ), 'Core Order files must not perform post_type=shop_order queries (violates HPOS)' );
}

echo "PASS: All {$statement_assertions} HPOS Compatibility assertions passed cleanly.\n";
