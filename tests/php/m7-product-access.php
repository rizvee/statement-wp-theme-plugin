<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_actions       = array();
$statement_filters       = array();
$statement_products      = array();
$statement_assertions    = 0;
$statement_is_admin      = false;
$statement_is_cron       = false;
$statement_is_ajax       = false;
$statement_is_product    = true;
$statement_is_preview    = false;
$statement_can_edit      = false;
$statement_queried_id    = 0;
$statement_status_header = null;
$statement_nocache_calls = 0;

function add_action( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_actions;
	$statement_actions[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): void {
	global $statement_filters;
	$statement_filters[ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

function wc_get_product( int $product_id ) {
	global $statement_products;
	return $statement_products[ $product_id ] ?? false;
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

function is_singular( string $post_type = '' ): bool {
	global $statement_is_product;
	return 'product' === $post_type && $statement_is_product;
}

function get_queried_object_id(): int {
	global $statement_queried_id;
	return $statement_queried_id;
}

function is_preview(): bool {
	global $statement_is_preview;
	return $statement_is_preview;
}

function current_user_can( string $capability, int $object_id ): bool {
	global $statement_can_edit;
	return 'edit_post' === $capability && $object_id > 0 && $statement_can_edit;
}

function status_header( int $status ): void {
	global $statement_status_header;
	$statement_status_header = $status;
}

function nocache_headers(): void {
	global $statement_nocache_calls;
	++$statement_nocache_calls;
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

final class Statement_M7_Access_Product {
	private $id;
	private $state;
	private $parent_id;

	public function __construct( int $id, string $state, int $parent_id = 0 ) {
		$this->id        = $id;
		$this->state     = $state;
		$this->parent_id = $parent_id;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return $this->parent_id > 0 ? 'variation' : 'simple';
	}

	public function get_parent_id(): int {
		return $this->parent_id;
	}

	public function get_meta( string $key, bool $single = true ): string {
		return '_statement_release_state' === $key ? $this->state : '';
	}
}

final class Statement_M7_Query {
	public $is_404 = false;
	public $post = 'private-post';
	public $posts = array( 'private-post' );
	public $queried_object = 'private-product';

	public function set_404(): void {
		$this->is_404 = true;
	}
}

$root = dirname( __DIR__, 2 );

eval( 'namespace Statement\\Collector\\Core\\Access; final class EligibilityService { public static function is_commerce_eligible( $product ): bool { return false; } }' );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Access.php';

use Statement\Collector\Core\Product\Access;
use Statement\Collector\Core\Release\ReleaseState;

$states = array(
	ReleaseState::LIVE           => true,
	ReleaseState::UPCOMING       => false,
	ReleaseState::PRIVATE_ACCESS => false,
	ReleaseState::SOLD_OUT       => true,
	ReleaseState::ARCHIVED       => true,
);

$id = 1;
foreach ( $states as $state => $expected ) {
	$product = new Statement_M7_Access_Product( $id++, $state );
	statement_assert_same( $expected, Access::is_publicly_viewable( $product ), "Unexpected direct-access result for {$state}." );
}

$statement_products[100] = new Statement_M7_Access_Product( 100, ReleaseState::LIVE );
$statement_products[200] = new Statement_M7_Access_Product( 200, ReleaseState::PRIVATE_ACCESS );
statement_assert_same( true, Access::is_publicly_viewable( new Statement_M7_Access_Product( 101, '', 100 ) ), 'Variation must inherit LIVE parent access.' );
statement_assert_same( false, Access::is_publicly_viewable( new Statement_M7_Access_Product( 201, '', 200 ) ), 'Variation must inherit PRIVATE_ACCESS parent exclusion.' );
statement_assert_same( true, Access::is_publicly_viewable( $statement_products[200], true ), 'Authorized preview decision must retain product-management access.' );

$statement_products[2] = new Statement_M7_Access_Product( 2, ReleaseState::PRIVATE_ACCESS );
$statement_queried_id  = 2;
$wp_query              = new Statement_M7_Query();
$post                  = 'private-post';
Access::guard_direct_product();
statement_assert_same( true, $wp_query->is_404, 'Public PRIVATE_ACCESS request must become a real 404 query.' );
statement_assert_same( 404, $statement_status_header, 'Public PRIVATE_ACCESS request must send a 404 status.' );
statement_assert_same( 1, $statement_nocache_calls, 'Public hidden product response must send no-cache headers.' );
statement_assert_same( null, $wp_query->post, 'Public hidden product response must scrub the private queried post.' );
statement_assert_same( array(), $wp_query->posts, 'Public hidden product response must scrub private query results.' );
statement_assert_same( null, $wp_query->queried_object, 'Public hidden product response must scrub the private queried object.' );
statement_assert_same( null, $post, 'Public hidden product response must scrub the global private post.' );

$statement_products[3] = new Statement_M7_Access_Product( 3, ReleaseState::LIVE );
$statement_queried_id  = 3;
$statement_status_header = null;
$wp_query              = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'LIVE product request must remain untouched.' );
statement_assert_same( null, $statement_status_header, 'LIVE product request must not emit a 404 status.' );

$statement_products[4] = new Statement_M7_Access_Product( 4, ReleaseState::SOLD_OUT );
$statement_queried_id  = 4;
$statement_is_preview  = true;
$statement_can_edit    = true;
$wp_query              = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'Capable editor preview must not be blocked.' );
$statement_is_preview = false;
$statement_can_edit   = false;

$statement_is_admin = true;
$wp_query           = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'Admin product management must remain unaffected.' );
$statement_is_admin = false;

$statement_is_cron = true;
$wp_query          = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'Cron must remain unaffected.' );
$statement_is_cron = false;

$statement_is_ajax = true;
$wp_query          = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'AJAX must remain unaffected by the direct-page gate.' );
$statement_is_ajax = false;

$statement_is_product = false;
$wp_query             = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'Unrelated pages must remain unaffected.' );
$statement_is_product = true;

define( 'REST_REQUEST', true );
$wp_query = new Statement_M7_Query();
Access::guard_direct_product();
statement_assert_same( false, $wp_query->is_404, 'REST requests must remain unaffected.' );

Access::boot();
statement_assert_same( 1, count( $statement_actions['template_redirect'] ?? array() ), 'Access must register one direct-product gate.' );
statement_assert_same( 0, $statement_actions['template_redirect'][0]['priority'] ?? null, 'Product privacy gate must run before normal redirect handling.' );
statement_assert_same( 1, count( $statement_filters['woocommerce_add_to_cart_validation'] ?? array() ), 'Access must register one Add-to-Cart guard.' );
statement_assert_same( 6, $statement_filters['woocommerce_add_to_cart_validation'][0]['accepted_args'] ?? null, 'Add-to-Cart guard must accept variation context.' );

fwrite( STDOUT, "PASS: M7 product access passed ({$statement_assertions} assertions).\n" );
