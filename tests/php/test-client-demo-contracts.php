<?php

/**
 * Statement Client Demo Static Contract & Symbol Drift Test Suite
 *
 * Verifies that all Statement Core constants, methods, and taxonomy keys
 * referenced across tools/statement-client-demo/ actually exist in Statement Core.
 */

// 1. Mock WordPress and WooCommerce primitives for CLI test runner
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../../' );
}

// Load Core classes
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';

use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Drop\Taxonomy;

$assertions_passed = 0;
function assert_contract( bool $condition, string $message ): void {
	global $assertions_passed;
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
	$assertions_passed++;
}

echo "Running Statement Client Demo API Contract & Symbol Drift Test Suite...\n";

// A. Verify Core canonical Metadata API contracts
assert_contract(
	defined( 'Statement\Collector\Core\Product\Metadata::RELEASE_STATE_KEY' ),
	'Metadata::RELEASE_STATE_KEY must be defined'
);
assert_contract(
	defined( 'Statement\Collector\Core\Product\Metadata::EDITION_LABEL_KEY' ),
	'Metadata::EDITION_LABEL_KEY must be defined'
);
assert_contract(
	! defined( 'Statement\Collector\Core\Product\Metadata::EDITION_KEY' ),
	'Metadata::EDITION_KEY must NOT exist in Core Metadata (prevents regression of undefined constant)'
);
assert_contract(
	method_exists( Metadata::class, 'set_edition_label' ),
	'Metadata::set_edition_label method must exist'
);
assert_contract(
	method_exists( Metadata::class, 'get_edition_label' ),
	'Metadata::get_edition_label method must exist'
);
assert_contract(
	method_exists( Metadata::class, 'set_release_state' ),
	'Metadata::set_release_state method must exist'
);
assert_contract(
	method_exists( Metadata::class, 'get_release_state' ),
	'Metadata::get_release_state method must exist'
);

// B. Verify ReleaseState and Taxonomy constants
assert_contract(
	defined( 'Statement\Collector\Core\Release\ReleaseState::LIVE' ),
	'ReleaseState::LIVE must be defined'
);
assert_contract(
	defined( 'Statement\Collector\Core\Release\ReleaseState::PRIVATE_ACCESS' ),
	'ReleaseState::PRIVATE_ACCESS must be defined'
);
assert_contract(
	defined( 'Statement\Collector\Core\Release\ReleaseState::SOLD_OUT' ),
	'ReleaseState::SOLD_OUT must be defined'
);
assert_contract(
	defined( 'Statement\Collector\Core\Release\ReleaseState::ARCHIVED' ),
	'ReleaseState::ARCHIVED must be defined'
);
assert_contract(
	defined( 'Statement\Collector\Core\Drop\Taxonomy::KEY' ),
	'Taxonomy::KEY must be defined'
);
assert_contract(
	'statement_drop' === Taxonomy::KEY,
	'Taxonomy::KEY must equal statement_drop'
);

// C. Scan Client Demo source files for static references
$demo_files = array(
	__DIR__ . '/../../tools/statement-client-demo/src/DemoSeederService.php',
	__DIR__ . '/../../tools/statement-client-demo/src/AdminPage.php',
	__DIR__ . '/../../tools/statement-client-demo/src/ManifestService.php',
	__DIR__ . '/../../tools/statement-client-demo/src/AssetRegistry.php',
	__DIR__ . '/../../tools/statement-client-demo/statement-client-demo.php',
);

foreach ( $demo_files as $file ) {
	assert_contract( file_exists( $file ), "Demo file {$file} must exist" );
	$content = file_get_contents( $file );

	// Ensure no forbidden references to non-existent EDITION_KEY
	assert_contract(
		false === strpos( $content, 'Metadata::EDITION_KEY' ),
		"File " . basename( $file ) . " must not reference non-existent Metadata::EDITION_KEY"
	);

	// Check Metadata:: usages
	preg_match_all( '/Metadata::([A-Za-z0-9_]+)/', $content, $meta_matches );
	if ( ! empty( $meta_matches[1] ) ) {
		foreach ( $meta_matches[1] as $symbol ) {
			if ( 'class' === $symbol ) {
				continue;
			}
			$is_const  = defined( "Statement\\Collector\\Core\\Product\\Metadata::{$symbol}" );
			$is_method = method_exists( Metadata::class, $symbol );
			assert_contract(
				$is_const || $is_method,
				"Referenced Metadata::{$symbol} in " . basename( $file ) . " must exist in Core Metadata"
			);
		}
	}

	// Check ReleaseState:: usages
	preg_match_all( '/ReleaseState::([A-Za-z0-9_]+)/', $content, $rs_matches );
	if ( ! empty( $rs_matches[1] ) ) {
		foreach ( $rs_matches[1] as $symbol ) {
			if ( 'class' === $symbol ) {
				continue;
			}
			$is_const  = defined( "Statement\\Collector\\Core\\Release\\ReleaseState::{$symbol}" );
			$is_method = method_exists( ReleaseState::class, $symbol );
			assert_contract(
				$is_const || $is_method,
				"Referenced ReleaseState::{$symbol} in " . basename( $file ) . " must exist in Core ReleaseState"
			);
		}
	}

	// Check Taxonomy:: usages
	preg_match_all( '/Taxonomy::([A-Za-z0-9_]+)/', $content, $tax_matches );
	if ( ! empty( $tax_matches[1] ) ) {
		foreach ( $tax_matches[1] as $symbol ) {
			if ( 'class' === $symbol ) {
				continue;
			}
			$is_const  = defined( "Statement\\Collector\\Core\\Drop\\Taxonomy::{$symbol}" );
			$is_method = method_exists( Taxonomy::class, $symbol );
			assert_contract(
				$is_const || $is_method,
				"Referenced Taxonomy::{$symbol} in " . basename( $file ) . " must exist in Core Taxonomy"
			);
		}
	}
}

echo "PASS: All {$assertions_passed} Statement Client Demo API Contract assertions passed cleanly.\n";
