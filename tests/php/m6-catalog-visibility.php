<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions    = array();
$statement_filters    = array();
$statement_assertions = 0;
$statement_is_admin   = false;
$statement_is_cron    = false;
$statement_is_ajax    = false;
$statement_products   = array();

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
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

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
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

final class Statement_Store_Api_Request_Double {
	private $route;

	public function __construct( string $route ) {
		$this->route = $route;
	}

	public function get_route(): string {
		return $this->route;
	}
}

final class Statement_Store_Api_Response_Double {
	private $data;
	public $status = 200;

	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function get_data(): array {
		return $this->data;
	}

	public function set_data( array $data ): void {
		$this->data = $data;
	}

	public function set_status( int $status ): void {
		$this->status = $status;
	}
}

final class Statement_Catalog_Product_Double {
	private $id;
	private $state;

	public function __construct( int $id, string $state ) {
		$this->id = $id;
		$this->state = $state;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_parent_id(): int {
		return 0;
	}

	public function get_type(): string {
		return 'simple';
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->state : '';
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
statement_assert_same( 1, count( $statement_filters['rest_pre_dispatch'] ?? array() ), 'Visibility must register the WooCommerce 11 Store API dispatch boundary.' );
statement_assert_same( 1, count( $statement_filters['rest_post_dispatch'] ?? array() ), 'Visibility must register the fail-closed Store API response boundary.' );

Visibility::prepare_store_api_boundary( null, null, new Statement_Store_Api_Request_Double( '/wc/store/v1/products' ) );
statement_assert_same( 1, count( $statement_actions['pre_get_posts'] ?? array() ), 'Store API product route must install one request-scoped query boundary.' );
$store_query = new Statement_Catalog_Query_Test_Double( false, false, false, array( 'post_type' => 'product' ) );
Visibility::apply_store_api_release_constraint( $store_query );
$store_meta = $store_query->get( 'meta_query' );
statement_assert_same( array( 'LIVE', 'SOLD_OUT' ), $store_meta[0]['value'] ?? null, 'Store API query must exclude PRIVATE_ACCESS and UPCOMING products.' );

$unrelated_store_query = new Statement_Catalog_Query_Test_Double( false, false, false, array( 'post_type' => 'post' ) );
Visibility::apply_store_api_release_constraint( $unrelated_store_query );
statement_assert_same( null, $unrelated_store_query->get( 'meta_query' ), 'Store API boundary must not alter non-product queries.' );

$statement_products = array(
	10 => new Statement_Catalog_Product_Double( 10, 'LIVE' ),
	20 => new Statement_Catalog_Product_Double( 20, 'PRIVATE_ACCESS' ),
);
$store_response = new Statement_Store_Api_Response_Double( array( array( 'id' => 10 ), array( 'id' => 20 ) ) );
Visibility::filter_store_api_response( $store_response, null, new Statement_Store_Api_Request_Double( '/wc/store/v1/products' ) );
statement_assert_same( array( array( 'id' => 10 ) ), $store_response->get_data(), 'Store API response boundary must remove PRIVATE_ACCESS products even if the upstream query leaks them.' );
$private_store_response = new Statement_Store_Api_Response_Double( array( 'id' => 20, 'slug' => 'private-product' ) );
Visibility::filter_store_api_response( $private_store_response, null, new Statement_Store_Api_Request_Double( '/wc/store/v1/products/20' ) );
statement_assert_same( 404, $private_store_response->status, 'Direct Store API request for a PRIVATE_ACCESS product must become a true 404.' );
statement_assert_same( 'woocommerce_rest_product_not_found', $private_store_response->get_data()['code'] ?? null, 'Direct Store API 404 must return a generic not-found code.' );

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
statement_assert_same( array( 'LIVE', 'SOLD_OUT' ), $shop_meta[1]['value'] ?? null, 'Public Shop must include active LIVE and SOLD_OUT presentation states.' );
statement_assert_same( 'IN', $shop_meta[1]['compare'] ?? null, 'Public Shop must use the bounded active-state set.' );

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
statement_assert_same( array( 'LIVE', 'SOLD_OUT' ), $drop_meta[0]['value'] ?? null, 'Drop query must append active presentation states without rebuilding its taxonomy query.' );

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
