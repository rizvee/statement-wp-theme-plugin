<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_query_calls = 0;

function wc_get_products( array $arguments ): array {
	global $statement_query_calls;
	++$statement_query_calls;
	return array();
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/themes/statement-collector-theme/inc/home.php';

$data = \Statement\Collector\Theme\get_home_release_data();
if ( array( 'drop' => null, 'products' => array() ) !== $data ) {
	fwrite( STDERR, "FAIL: Missing Statement Core must return empty homepage release data.\n" );
	exit( 1 );
}

if ( 0 !== $statement_query_calls ) {
	fwrite( STDERR, "FAIL: Missing Statement Core must prevent the WooCommerce candidate query.\n" );
	exit( 1 );
}

fwrite( STDOUT, "PASS: M5 homepage Core-absence safety passed (2 assertions).\n" );
