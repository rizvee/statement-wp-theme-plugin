<?php

/**
 * Statement Client Demo Runtime Behavioral Test Suite
 *
 * Simulates complete WordPress & WooCommerce runtime to verify:
 * - Deterministic creation of Product 01 & Product 02
 * - Unique IDs & deterministic SKUs
 * - S/M/L variable products with stock & AUD pricing
 * - Canonical Metadata::set_edition_label() and LIVE ReleaseState
 * - Safe adoption and rejection of QA fixtures (Product 213)
 * - Safe preflight detection of collisions
 * - Second run idempotency
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../../' );
}
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require_once __DIR__ . '/../../wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once __DIR__ . '/../../tools/statement-client-demo/src/AssetRegistry.php';
require_once __DIR__ . '/../../tools/statement-client-demo/src/ManifestService.php';
require_once __DIR__ . '/../../tools/statement-client-demo/src/DemoSeederService.php';

use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Drop\Taxonomy;
use Statement\ClientDemo\DemoSeederService;
use Statement\ClientDemo\ManifestService;
use Statement\ClientDemo\AssetRegistry;

// Mock in-memory database & WordPress functions
class MockWpStore {
	public static $posts = array();
	public static $postmeta = array();
	public static $options = array();
	public static $terms = array();
	public static $termmeta = array();
	public static $term_relationships = array();
	public static $theme_mods = array();
	public static $next_id = 1000;

	public static function reset(): void {
		self::$posts = array();
		self::$postmeta = array();
		self::$options = array();
		self::$terms = array();
		self::$termmeta = array();
		self::$term_relationships = array();
		self::$theme_mods = array();
		self::$next_id = 1000;
	}
}

class WC_Product_Attribute {
	public $name = '';
	public $options = array();
	public $position = 0;
	public $visible = true;
	public $variation = true;

	public function set_name( $name ) { $this->name = $name; }
	public function set_options( $options ) { $this->options = $options; }
	public function set_position( $pos ) { $this->position = $pos; }
	public function set_visible( $vis ) { $this->visible = $vis; }
	public function set_variation( $var ) { $this->variation = $var; }
}

class WC_Product_Variable {
	public $id = 0;
	public $name = '';
	public $slug = '';
	public $short_description = '';
	public $description = '';
	public $sku = '';
	public $status = 'draft';
	public $catalog_visibility = 'visible';
	public $image_id = 0;
	public $gallery_image_ids = array();
	public $attributes = array();
	public $meta = array();

	public function __construct( $id = 0 ) {
		if ( $id > 0 ) {
			$this->id = $id;
			if ( isset( MockWpStore::$posts[ $id ] ) ) {
				$this->name = MockWpStore::$posts[ $id ]['post_title'];
				$this->slug = MockWpStore::$posts[ $id ]['post_name'];
				$this->short_description = MockWpStore::$posts[ $id ]['post_excerpt'] ?? '';
				$this->description = MockWpStore::$posts[ $id ]['post_content'] ?? '';
			}
			$this->sku = MockWpStore::$postmeta[ $id ]['_sku'] ?? '';
			$this->meta = MockWpStore::$postmeta[ $id ] ?? array();
		}
	}

	public function get_id() { return $this->id; }
	public function get_name() { return $this->name; }
	public function get_slug() { return $this->slug; }
	public function get_sku() { return $this->sku; }
	public function get_short_description() { return $this->short_description; }
	public function get_description() { return $this->description; }
	public function get_type() { return 'variable'; }

	public function set_name( $val ) { $this->name = $val; }
	public function set_slug( $val ) { $this->slug = $val; }
	public function set_short_description( $val ) { $this->short_description = $val; }
	public function set_description( $val ) { $this->description = $val; }
	public function set_sku( $val ) { $this->sku = $val; }
	public function set_status( $val ) { $this->status = $val; }
	public function set_catalog_visibility( $val ) { $this->catalog_visibility = $val; }
	public function set_image_id( $val ) { $this->image_id = $val; }
	public function set_gallery_image_ids( $val ) { $this->gallery_image_ids = $val; }
	public function set_attributes( $val ) { $this->attributes = $val; }

	public function get_meta( $key, $single = true ) {
		return MockWpStore::$postmeta[ $this->id ][ $key ] ?? '';
	}

	public function update_meta_data( $key, $value ) {
		$this->meta[ $key ] = $value;
	}

	public function delete_meta_data( $key ) {
		unset( $this->meta[ $key ] );
	}

	public function save() {
		if ( $this->id < 1 ) {
			$this->id = ++MockWpStore::$next_id;
		}
		MockWpStore::$posts[ $this->id ] = array(
			'ID'           => $this->id,
			'post_title'   => $this->name,
			'post_name'    => $this->slug,
			'post_content' => $this->description,
			'post_excerpt' => $this->short_description,
			'post_type'    => 'product',
			'post_status'  => $this->status,
		);
		MockWpStore::$postmeta[ $this->id ]['_sku'] = $this->sku;
		foreach ( $this->meta as $k => $v ) {
			MockWpStore::$postmeta[ $this->id ][ $k ] = $v;
		}
		return $this->id;
	}
}

class WC_Product_Variation {
	public $id = 0;
	public $parent_id = 0;
	public $sku = '';
	public $attributes = array();
	public $regular_price = '';
	public $price = '';
	public $manage_stock = true;
	public $stock_quantity = 0;
	public $stock_status = 'instock';
	public $status = 'publish';

	public function __construct( $id = 0 ) {
		if ( $id > 0 ) {
			$this->id = $id;
		}
	}

	public function set_parent_id( $val ) { $this->parent_id = $val; }
	public function set_sku( $val ) { $this->sku = $val; }
	public function set_attributes( $val ) { $this->attributes = $val; }
	public function set_regular_price( $val ) { $this->regular_price = $val; }
	public function set_price( $val ) { $this->price = $val; }
	public function set_manage_stock( $val ) { $this->manage_stock = $val; }
	public function set_stock_quantity( $val ) { $this->stock_quantity = $val; }
	public function set_stock_status( $val ) { $this->stock_status = $val; }
	public function set_status( $val ) { $this->status = $val; }

	public function save() {
		if ( $this->id < 1 ) {
			$this->id = ++MockWpStore::$next_id;
		}
		MockWpStore::$posts[ $this->id ] = array(
			'ID'          => $this->id,
			'post_parent' => $this->parent_id,
			'post_type'   => 'product_variation',
			'post_status' => $this->status,
		);
		MockWpStore::$postmeta[ $this->id ]['_sku'] = $this->sku;
		MockWpStore::$postmeta[ $this->id ]['_price'] = $this->price;
		MockWpStore::$postmeta[ $this->id ]['_regular_price'] = $this->regular_price;
		MockWpStore::$postmeta[ $this->id ]['_stock'] = $this->stock_quantity;
		return $this->id;
	}
}

// Global functions
function get_post_meta( $id, $key, $single = true ) {
	return MockWpStore::$postmeta[ $id ][ $key ] ?? '';
}
function update_post_meta( $id, $key, $value ) {
	MockWpStore::$postmeta[ $id ][ $key ] = $value;
	return true;
}
function delete_post_meta( $id, $key ) {
	unset( MockWpStore::$postmeta[ $id ][ $key ] );
	return true;
}
function get_option( $name, $default = false ) {
	return MockWpStore::$options[ $name ] ?? $default;
}
function update_option( $name, $value, $autoload = false ) {
	MockWpStore::$options[ $name ] = $value;
	return true;
}
function get_theme_mod( $name, $default = false ) {
	return MockWpStore::$theme_mods[ $name ] ?? $default;
}
function set_theme_mod( $name, $value ) {
	MockWpStore::$theme_mods[ $name ] = $value;
	return true;
}
function wc_get_products( $args ) {
	$results = array();
	if ( isset( $args['sku'] ) ) {
		foreach ( MockWpStore::$postmeta as $pid => $meta ) {
			if ( isset( $meta['_sku'] ) && $meta['_sku'] === $args['sku'] ) {
				$results[] = new WC_Product_Variable( $pid );
			}
		}
	} elseif ( isset( $args['slug'] ) ) {
		foreach ( MockWpStore::$posts as $pid => $post ) {
			if ( $post['post_name'] === $args['slug'] && ( $post['post_type'] ?? '' ) === 'product' ) {
				$results[] = new WC_Product_Variable( $pid );
			}
		}
	}
	return $results;
}
function get_term_by( $field, $value, $tax ) {
	foreach ( MockWpStore::$terms as $term ) {
		if ( $term->slug === $value && $term->taxonomy === $tax ) {
			return $term;
		}
	}
	return null;
}
function wp_insert_term( $name, $tax, $args = array() ) {
	$id = ++MockWpStore::$next_id;
	$term = (object) array(
		'term_id'  => $id,
		'name'     => $name,
		'slug'     => $args['slug'] ?? '',
		'taxonomy' => $tax,
	);
	MockWpStore::$terms[ $id ] = $term;
	return array( 'term_id' => $id, 'term_taxonomy_id' => $id );
}
function update_term_meta( $id, $key, $value ) {
	MockWpStore::$termmeta[ $id ][ $key ] = $value;
	return true;
}
function wp_set_object_terms( $object_id, $terms, $taxonomy, $append = false ) {
	MockWpStore::$term_relationships[ $object_id ] = (array) $terms;
	return true;
}
function get_the_terms( $post_id, $taxonomy ) {
	$term_ids = MockWpStore::$term_relationships[ $post_id ] ?? array();
	$res = array();
	foreach ( $term_ids as $tid ) {
		if ( isset( MockWpStore::$terms[ $tid ] ) && MockWpStore::$terms[ $tid ]->taxonomy === $taxonomy ) {
			$res[] = MockWpStore::$terms[ $tid ];
		}
	}
	return ! empty( $res ) ? $res : false;
}
function get_page_by_path( $slug, $output = OBJECT, $post_type = array( 'page' ) ) {
	foreach ( MockWpStore::$posts as $pid => $post ) {
		if ( ( $post['post_name'] ?? '' ) === $slug ) {
			return (object) $post;
		}
	}
	return null;
}
function wp_insert_post( $args ) {
	$id = ++MockWpStore::$next_id;
	$args['ID'] = $id;
	MockWpStore::$posts[ $id ] = $args;
	return $id;
}
function wp_update_post( $args ) {
	$id = $args['ID'];
	if ( isset( MockWpStore::$posts[ $id ] ) ) {
		MockWpStore::$posts[ $id ] = array_merge( MockWpStore::$posts[ $id ], $args );
	}
	return $id;
}
function get_the_title( $id ) {
	return MockWpStore::$posts[ $id ]['post_title'] ?? '';
}
function wp_insert_attachment( $attachment, $file ) {
	$id = ++MockWpStore::$next_id;
	$attachment['ID'] = $id;
	$attachment['post_type'] = 'attachment';
	MockWpStore::$posts[ $id ] = $attachment;
	return $id;
}
function is_wp_error( $thing ) {
	return false;
}
function set_post_thumbnail( $id, $thumb_id ) {
	MockWpStore::$postmeta[ $id ]['_thumbnail_id'] = $thumb_id;
}

$assertions_passed = 0;
function assert_runtime( bool $condition, string $message ): void {
	global $assertions_passed;
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
	$assertions_passed++;
}

echo "Running Statement Client Demo Runtime Behavioral Test Suite...\n";

MockWpStore::reset();

// Setup QA fixture Product 213 in Mock DB
MockWpStore::$posts[213] = array(
	'ID'           => 213,
	'post_title'   => 'TEST — Private Access Jacket',
	'post_name'    => 'test-private-access-jacket',
	'post_content' => 'QA fixture body',
	'post_type'    => 'product',
	'post_status'  => 'publish',
);
MockWpStore::$postmeta[213] = array(
	'_sku'                => 'TEST-PD01-PAJ',
	'_statement_fixture'  => '1',
	'_statement_release_state' => 'PRIVATE_ACCESS',
);

// TEST 1: Initial Discovery - QA Product 213 must NOT be adopted as demo Product 1 or 2
$p1_find = DemoSeederService::find_owned_product( DemoSeederService::SKU_P1, 'monogram-jacquard-jacket' );
assert_runtime( null === $p1_find, 'find_owned_product for P1 must return null when no demo product exists' );

$p2_find = DemoSeederService::find_owned_product( DemoSeederService::SKU_P2, 'panelled-hood-jacket' );
assert_runtime( null === $p2_find, 'find_owned_product for P2 must return null when no demo product exists' );

// TEST 2: Preflight diagnostics before seeding
$preflight_1 = DemoSeederService::preflight();
assert_runtime( true === $preflight_1['safe'], 'Initial preflight must be safe before seeding' );
assert_runtime( false === $preflight_1['has_duplicate_id'], 'Initial preflight must report no duplicate IDs' );
assert_runtime( false === $preflight_1['has_fixture_collision'], 'Initial preflight must report no fixture collisions' );

// TEST 3: Execute Seed
$report_1 = DemoSeederService::seed_or_update();
if ( ! empty( $report_1['errors'] ) ) {
	print_r( $report_1['errors'] );
}
assert_runtime( true === $report_1['success'], 'First seed execution must succeed' );
assert_runtime( empty( $report_1['errors'] ), 'First seed execution must contain zero errors' );

$p1_id = $report_1['products']['product_01'];
$p2_id = $report_1['products']['product_02'];

assert_runtime( $p1_id > 0, "Product 01 must have valid ID: {$p1_id}" );
assert_runtime( $p2_id > 0, "Product 02 must have valid ID: {$p2_id}" );
assert_runtime( $p1_id !== $p2_id, "Product 01 ID ({$p1_id}) must NEVER equal Product 02 ID ({$p2_id})" );
assert_runtime( 213 !== $p1_id, 'Product 01 must NEVER adopt QA Product 213' );
assert_runtime( 213 !== $p2_id, 'Product 02 must NEVER adopt QA Product 213' );

// TEST 4: Verify Product 01 properties
assert_runtime( 'STMT-CD-D001-MJ' === MockWpStore::$postmeta[ $p1_id ]['_sku'], 'Product 01 must carry deterministic SKU STMT-CD-D001-MJ' );
assert_runtime( '1' === (string) MockWpStore::$postmeta[ $p1_id ]['_statement_client_demo'], 'Product 01 must have _statement_client_demo=1' );
assert_runtime( 'LIVE' === MockWpStore::$postmeta[ $p1_id ]['_statement_release_state'], 'Product 01 must have LIVE release state' );
assert_runtime( 'MONOGRAM STUDY / DROP 001' === MockWpStore::$postmeta[ $p1_id ]['_statement_edition_label'], 'Product 01 must carry canonical edition label' );

// TEST 5: Verify Product 02 properties
assert_runtime( 'STMT-CD-D001-PHJ' === MockWpStore::$postmeta[ $p2_id ]['_sku'], 'Product 02 must carry deterministic SKU STMT-CD-D001-PHJ' );
assert_runtime( '1' === (string) MockWpStore::$postmeta[ $p2_id ]['_statement_client_demo'], 'Product 02 must have _statement_client_demo=1' );
assert_runtime( 'LIVE' === MockWpStore::$postmeta[ $p2_id ]['_statement_release_state'], 'Product 02 must have LIVE release state' );
assert_runtime( 'MONOGRAM STUDY / DROP 001' === MockWpStore::$postmeta[ $p2_id ]['_statement_edition_label'], 'Product 02 must carry canonical edition label' );

// TEST 6: Verify QA Product 213 remains untouched
assert_runtime( 'TEST-PD01-PAJ' === MockWpStore::$postmeta[213]['_sku'], 'QA Product 213 SKU must remain untouched' );
assert_runtime( '1' === (string) MockWpStore::$postmeta[213]['_statement_fixture'], 'QA Product 213 fixture marker must remain intact' );
assert_runtime( 'PRIVATE_ACCESS' === MockWpStore::$postmeta[213]['_statement_release_state'], 'QA Product 213 release state must remain PRIVATE_ACCESS' );

// TEST 7: Verify Variations for Product 01
$var_s = MockWpStore::$postmeta[ $p1_id + 1 ] ?? array();
$var_m = MockWpStore::$postmeta[ $p1_id + 2 ] ?? array();
$var_l = MockWpStore::$postmeta[ $p1_id + 3 ] ?? array();
assert_runtime( 'STMT-CD-D001-MJ-S' === ( $var_s['_sku'] ?? '' ), 'Variation S SKU must match STMT-CD-D001-MJ-S' );
assert_runtime( '295.00' === ( $var_s['_price'] ?? '' ), 'Variation S Price must be AUD 295.00' );
assert_runtime( 'STMT-CD-D001-MJ-M' === ( $var_m['_sku'] ?? '' ), 'Variation M SKU must match STMT-CD-D001-MJ-M' );
assert_runtime( 'STMT-CD-D001-MJ-L' === ( $var_l['_sku'] ?? '' ), 'Variation L SKU must match STMT-CD-D001-MJ-L' );

// TEST 8: Verify Hero Slider Theme Mods (Image-First Defaults)
assert_runtime( '' === MockWpStore::$theme_mods['statement_hero_slide_1_eyebrow'], 'Slide 1 eyebrow must be empty for image-first' );
assert_runtime( '' === MockWpStore::$theme_mods['statement_hero_slide_1_heading'], 'Slide 1 heading must be empty for image-first' );
assert_runtime( (int) MockWpStore::$theme_mods['statement_hero_slide_1_image'] > 0, 'Slide 1 image must be valid attachment ID' );

// TEST 9: Idempotency (Second Seed Execution)
$report_2 = DemoSeederService::seed_or_update();
assert_runtime( true === $report_2['success'], 'Second seed execution must succeed' );
assert_runtime( $report_2['products']['product_01'] === $p1_id, 'Product 01 ID must remain identical on second run' );
assert_runtime( $report_2['products']['product_02'] === $p2_id, 'Product 02 ID must remain identical on second run' );

// TEST 10: Contaminated QA Product Handling
MockWpStore::$postmeta[213]['_statement_client_demo'] = '1'; // simulate accidental contamination
$p1_check = DemoSeederService::find_owned_product( DemoSeederService::SKU_P1, 'test-private-access-jacket' );
assert_runtime( null === $p1_check || $p1_check->get_id() !== 213, 'find_owned_product must NEVER return Product 213 even if carrying _statement_client_demo=1' );

$repair_report = DemoSeederService::repair_client_demo();
assert_runtime( true === $repair_report['success'], 'Repair client demo must succeed' );

echo "PASS: All {$assertions_passed} Statement Client Demo Runtime behavioral assertions passed cleanly.\n";
