<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions    = array();
$statement_assertions = 0;
$statement_is_admin   = false;
$statement_is_cron    = false;
$statement_is_ajax    = false;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function is_admin(): bool {
	global $statement_is_admin;
	return $statement_is_admin;
}

function wp_doing_cron(): bool {
	global $statement_is_cron;
	return $statement_is_cron;
}

function wp_doing_ajax(): bool {
	global $statement_is_ajax;
	return $statement_is_ajax;
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

final class Statement_Catalog_Query_Test_Double {
	private $main;
	private $shop;
	private $drop;
	private $vars;

	public function __construct( bool $main, bool $shop, bool $drop, array $vars = array() ) {
		$this->main = $main;
		$this->shop = $shop;
		$this->drop = $drop;
		$this->vars = $vars;
	}

	public function is_main_query(): bool {
		return $this->main;
	}

	public function is_post_type_archive( string $post_type ): bool {
		return 'product' === $post_type && $this->shop;
	}

	public function is_tax( string $taxonomy = '' ): bool {
		return 'statement_drop' === $taxonomy && $this->drop;
	}

	public function get( string $key ) {
		return $this->vars[ $key ] ?? null;
	}

	public function set( string $key, $value ): void {
		$this->vars[ $key ] = $value;
	}
}

function statement_live_clauses( Statement_Catalog_Query_Test_Double $query ): array {
	$meta_query = $query->get( 'meta_query' );
	if ( ! is_array( $meta_query ) ) {
		return array();
	}

	$matches = array();
	array_walk_recursive(
		$meta_query,
		static function ( $value, $key ) use ( &$matches ): void {
			if ( 'key' === $key && '_statement_release_state' === $value ) {
				$matches[] = $value;
			}
		}
	);
	return $matches;
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Catalog/Visibility.php';

use Statement\Collector\Core\Catalog\Visibility;

Visibility::boot();
statement_assert_same( 1, count( $statement_actions['woocommerce_product_query'] ?? array() ), 'Visibility must register one WooCommerce product-query callback.' );
statement_assert_same( 2, $statement_actions['woocommerce_product_query'][0]['accepted_args'] ?? null, 'Visibility callback must accept the Woo query context.' );

$shop_existing = array(
	array(
		'key'     => 'foo',
		'value'   => 'bar',
		'compare' => '=',
	),
);
$shop = new Statement_Catalog_Query_Test_Double( true, true, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $shop );
$shop_meta = $shop->get( 'meta_query' );
statement_assert_same( $shop_existing[0], $shop_meta[0] ?? null, 'Public Shop must preserve existing WooCommerce meta constraints.' );
statement_assert_same( '_statement_release_state', $shop_meta[1]['key'] ?? null, 'Public Shop must append the canonical release-state key.' );
statement_assert_same( 'LIVE', $shop_meta[1]['value'] ?? null, 'Public Shop must require LIVE.' );
statement_assert_same( '=', $shop_meta[1]['compare'] ?? null, 'Public Shop must use an exact release-state comparison.' );

$drop_existing = array(
	'relation'     => 'AND',
	'price_clause' => array(
		'key'     => '_price',
		'value'   => 50,
		'compare' => '>=',
	),
);
$drop = new Statement_Catalog_Query_Test_Double( true, false, true, array( 'meta_query' => $drop_existing ) );
Visibility::apply_live_constraint( $drop );
$drop_meta = $drop->get( 'meta_query' );
statement_assert_same( 'AND', $drop_meta['relation'] ?? null, 'Drop query must preserve an existing relation.' );
statement_assert_same( $drop_existing['price_clause'], $drop_meta['price_clause'] ?? null, 'Drop query must preserve named WooCommerce clauses.' );
statement_assert_same( 'LIVE', $drop_meta[0]['value'] ?? null, 'Drop query must append LIVE without rebuilding its taxonomy query.' );

$empty = new Statement_Catalog_Query_Test_Double( true, true, false );
Visibility::apply_live_constraint( $empty );
statement_assert_same( 1, count( statement_live_clauses( $empty ) ), 'Missing meta_query must receive one LIVE clause.' );
Visibility::apply_live_constraint( $empty );
statement_assert_same( 1, count( statement_live_clauses( $empty ) ), 'Repeated callbacks must not duplicate the LIVE clause.' );

$non_main = new Statement_Catalog_Query_Test_Double( false, true, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $non_main );
statement_assert_same( $shop_existing, $non_main->get( 'meta_query' ), 'Non-main product query must remain unchanged.' );

$unrelated = new Statement_Catalog_Query_Test_Double( true, false, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $unrelated );
statement_assert_same( $shop_existing, $unrelated->get( 'meta_query' ), 'Unrelated main product query must remain unchanged.' );

$statement_is_admin = true;
$admin = new Statement_Catalog_Query_Test_Double( true, true, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $admin );
statement_assert_same( $shop_existing, $admin->get( 'meta_query' ), 'Admin query must remain unchanged.' );
$statement_is_admin = false;

$statement_is_cron = true;
$cron = new Statement_Catalog_Query_Test_Double( true, true, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $cron );
statement_assert_same( $shop_existing, $cron->get( 'meta_query' ), 'Cron-like query must remain unchanged.' );
$statement_is_cron = false;

$statement_is_ajax = true;
$ajax = new Statement_Catalog_Query_Test_Double( true, true, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $ajax );
statement_assert_same( $shop_existing, $ajax->get( 'meta_query' ), 'AJAX-like query must remain unchanged.' );
$statement_is_ajax = false;

define( 'REST_REQUEST', true );
$rest = new Statement_Catalog_Query_Test_Double( true, true, false, array( 'meta_query' => $shop_existing ) );
Visibility::apply_live_constraint( $rest );
statement_assert_same( $shop_existing, $rest->get( 'meta_query' ), 'REST-like query must remain unchanged.' );

fwrite( STDOUT, "PASS: M6 catalog visibility passed ({$statement_assertions} assertions).\n" );
