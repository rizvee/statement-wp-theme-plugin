<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';
require $root . '/wp-content/themes/statement-collector-theme/inc/home.php';

if ( function_exists( 'wc_get_products' ) ) {
	fwrite( STDERR, "FAIL: WooCommerce product API must be absent for this fixture.\n" );
	exit( 1 );
}

$data = \Statement\Collector\Theme\get_home_release_data();
if ( array( 'drop' => null, 'products' => array() ) !== $data ) {
	fwrite( STDERR, "FAIL: Missing WooCommerce must return empty homepage release data.\n" );
	exit( 1 );
}

fwrite( STDOUT, "PASS: M5 homepage WooCommerce-absence safety passed (2 assertions).\n" );
