<?php

namespace Statement\Integration\Fixtures;

defined( 'ABSPATH' ) || exit;

class FixtureService {
	public const MANIFEST_OPTION = 'statement_integration_fixture_manifest';

	public static function is_environment_ready(): array {
		$woo_active  = class_exists( 'WooCommerce' );
		$core_active = class_exists( '\Statement\Collector\Core\Plugin' ) || defined( 'STATEMENT_COLLECTOR_CORE_VERSION' );
		$core_ver    = defined( 'STATEMENT_COLLECTOR_CORE_VERSION' ) ? STATEMENT_COLLECTOR_CORE_VERSION : 'MISSING';
		$currency    = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'N/A';

		return array(
			'woo_active'  => $woo_active,
			'core_active' => $core_active,
			'core_ver'    => $core_ver,
			'currency'    => $currency,
			'can_create'  => $woo_active && $core_active,
		);
	}

	public static function get_seeding_state(): string {
		$manifest = get_option( self::MANIFEST_OPTION, false );
		if ( is_array( $manifest ) && ! empty( $manifest['product_ids'] ) ) {
			$all_exist = true;
			foreach ( (array) $manifest['product_ids'] as $pid ) {
				if ( ! get_post( $pid ) ) {
					$all_exist = false;
					break;
				}
			}
			if ( $all_exist ) {
				return 'SEEDED';
			}
		}

		$discovered = self::discover_existing_fixtures();
		if ( null !== $discovered ) {
			return 'RECOVERY_REQUIRED';
		}

		return 'NOT_SEEDED';
	}

	public static function discover_existing_fixtures(): ?array {
		if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			return null;
		}

		$p1_id = wc_get_product_id_by_sku( 'TEST-LD01-MJ' );
		$p2_id = wc_get_product_id_by_sku( 'TEST-LD01-SO' );
		$p3_id = wc_get_product_id_by_sku( 'TEST-LD01-TJ' );

		if ( $p1_id < 1 || $p2_id < 1 || $p3_id < 1 ) {
			return null;
		}

		$v1_id = wc_get_product_id_by_sku( 'TEST-LD01-MJ-S' );
		$v2_id = wc_get_product_id_by_sku( 'TEST-LD01-MJ-M' );
		$v3_id = wc_get_product_id_by_sku( 'TEST-LD01-MJ-L' );

		if ( $v1_id < 1 || $v2_id < 1 || $v3_id < 1 ) {
			return null;
		}

		$cat_term  = get_term_by( 'slug', 'test-outerwear', 'product_cat' );
		$tag_term  = get_term_by( 'slug', 'test-integration', 'product_tag' );
		$drop_term = get_term_by( 'slug', 'test-live-drop-01', 'statement_drop' );

		if ( ! $cat_term || ! $tag_term || ! $drop_term ) {
			return null;
		}

		return array(
			'category_id'    => (int) $cat_term->term_id,
			'product_tag_id' => (int) $tag_term->term_id,
			'drop_id'        => (int) $drop_term->term_id,
			'product_ids'    => array( (int) $p1_id, (int) $p2_id, (int) $p3_id ),
			'variation_ids'  => array( (int) $v1_id, (int) $v2_id, (int) $v3_id ),
			'skus'           => array( 'TEST-LD01-MJ', 'TEST-LD01-MJ-S', 'TEST-LD01-MJ-M', 'TEST-LD01-MJ-L', 'TEST-LD01-SO', 'TEST-LD01-TJ' ),
		);
	}

	public static function adopt_existing_fixtures(): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return array( 'success' => false, 'message' => 'Unauthorized capability check failed.' );
		}

		$discovered = self::discover_existing_fixtures();
		if ( null === $discovered ) {
			return array( 'success' => false, 'message' => 'Adoption failed: Discovered fixtures are incomplete or do not match expected SKUs and terms.' );
		}

		$previous_currency = get_option( 'woocommerce_currency', 'AUD' );
		if ( 'AUD' !== $previous_currency ) {
			update_option( 'woocommerce_currency', 'AUD' );
		}

		$manifest = array(
			'schema_version'    => '1.0.0',
			'created_at'        => current_time( 'mysql' ),
			'plugin_version'    => STATEMENT_INTEGRATION_FIXTURES_VERSION,
			'previous_currency' => 'USD',
			'current_currency'  => 'AUD',
			'category_id'       => $discovered['category_id'],
			'product_tag_id'    => $discovered['product_tag_id'],
			'drop_id'           => $discovered['drop_id'],
			'product_ids'       => $discovered['product_ids'],
			'variation_ids'     => $discovered['variation_ids'],
			'skus'              => $discovered['skus'],
		);

		update_option( self::MANIFEST_OPTION, $manifest );

		return array(
			'success'  => true,
			'message'  => 'Successfully adopted existing integration fixtures and saved manifest.',
			'manifest' => $manifest,
		);
	}

	public static function check_collisions(): array {
		$collisions = array();

		$slugs = array(
			'category' => array( 'taxonomy' => 'product_cat', 'slug' => 'test-outerwear' ),
			'tag'      => array( 'taxonomy' => 'product_tag', 'slug' => 'test-integration' ),
			'drop'     => array( 'taxonomy' => 'statement_drop', 'slug' => 'test-live-drop-01' ),
		);

		foreach ( $slugs as $type => $info ) {
			if ( taxonomy_exists( $info['taxonomy'] ) ) {
				$term = get_term_by( 'slug', $info['slug'], $info['taxonomy'] );
				if ( $term ) {
					$collisions[] = sprintf( 'Existing %s term found with slug "%s" (ID: %d)', $info['taxonomy'], $info['slug'], $term->term_id );
				}
			}
		}

		$skus = array( 'TEST-LD01-MJ', 'TEST-LD01-MJ-S', 'TEST-LD01-MJ-M', 'TEST-LD01-MJ-L', 'TEST-LD01-SO', 'TEST-LD01-TJ' );
		foreach ( $skus as $sku ) {
			if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
				$id = wc_get_product_id_by_sku( $sku );
				if ( $id > 0 ) {
					$collisions[] = sprintf( 'Existing product found with SKU "%s" (ID: %d)', $sku, $id );
				}
			}
		}

		return $collisions;
	}

	public static function create(): array {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return array( 'success' => false, 'message' => 'Unauthorized capability check failed.' );
		}

		$env = self::is_environment_ready();
		if ( ! $env['can_create'] ) {
			return array( 'success' => false, 'message' => 'Preflight failed: WooCommerce or Statement Core missing.' );
		}

		$state = self::get_seeding_state();
		if ( 'SEEDED' === $state ) {
			return array( 'success' => false, 'message' => 'Fixtures already seeded according to manifest.' );
		}
		if ( 'RECOVERY_REQUIRED' === $state ) {
			return array( 'success' => false, 'message' => 'Existing fixture records detected! Please use Adopt Existing Test Fixtures.' );
		}

		$collisions = self::check_collisions();
		if ( ! empty( $collisions ) ) {
			return array( 'success' => false, 'message' => 'Collision check failed: ' . implode( '; ', $collisions ) );
		}

		$previous_currency = get_option( 'woocommerce_currency', 'USD' );
		update_option( 'woocommerce_currency', 'AUD' );

		// 1. Create WooCommerce Product Category (product_cat)
		$cat_result = wp_insert_term( 'TEST — Outerwear', 'product_cat', array(
			'slug'        => 'test-outerwear',
			'description' => 'Controlled Atomic integration fixture. Not production catalog data.',
		) );
		if ( is_wp_error( $cat_result ) ) {
			return array( 'success' => false, 'message' => 'Failed to create product category: ' . $cat_result->get_error_message() );
		}
		$cat_id = (int) $cat_result['term_id'];

		// 2. Create WooCommerce Product Tag (product_tag)
		$tag_result = wp_insert_term( 'TEST — Integration', 'product_tag', array(
			'slug'        => 'test-integration',
			'description' => 'Controlled Atomic integration fixture. Not production catalog data.',
		) );
		if ( is_wp_error( $tag_result ) ) {
			return array( 'success' => false, 'message' => 'Failed to create product tag: ' . $tag_result->get_error_message() );
		}
		$tag_id = (int) $tag_result['term_id'];

		// 3. Create Statement Drop (statement_drop)
		$drop_result = wp_insert_term( 'TEST — Live Drop 01', 'statement_drop', array(
			'slug'        => 'test-live-drop-01',
			'description' => 'Controlled Atomic integration fixture. Not production inventory.',
		) );
		if ( is_wp_error( $drop_result ) ) {
			return array( 'success' => false, 'message' => 'Failed to create Statement Drop: ' . $drop_result->get_error_message() );
		}
		$drop_id = (int) $drop_result['term_id'];

		// 4. Create Product 1 — Variable LIVE (TEST — Monogram Jacket)
		$p1 = new \WC_Product_Variable();
		$p1->set_name( 'TEST — Monogram Jacket' );
		$p1->set_slug( 'test-monogram-jacket' );
		$p1->set_sku( 'TEST-LD01-MJ' );
		$p1->set_status( 'publish' );
		$p1->set_description( 'Controlled Atomic integration fixture for variable-product and LIVE lifecycle testing.' );
		$p1->set_short_description( 'TEST fixture — not production inventory.' );
		$p1->set_category_ids( array( $cat_id ) );
		$p1->set_tag_ids( array( $tag_id ) );

		// Set product attribute Size (S, M, L)
		$attr = new \WC_Product_Attribute();
		$attr->set_name( 'Size' );
		$attr->set_options( array( 'S', 'M', 'L' ) );
		$attr->set_position( 0 );
		$attr->set_visible( true );
		$attr->set_variation( true );
		$p1->set_attributes( array( $attr ) );

		// Set Statement Domain Metadata using Core APIs
		\Statement\Collector\Core\Product\Metadata::set_edition_label( $p1, 'Integration Edition' );
		\Statement\Collector\Core\Product\Metadata::set_release_state( $p1, 'LIVE' );
		$p1_id = $p1->save();

		// Assign Drop term
		wp_set_object_terms( $p1_id, array( $drop_id ), 'statement_drop' );

		// Create Variations S, M, L
		$var_ids = array();
		$sizes   = array(
			'S' => 'TEST-LD01-MJ-S',
			'M' => 'TEST-LD01-MJ-M',
			'L' => 'TEST-LD01-MJ-L',
		);

		foreach ( $sizes as $size => $sku ) {
			$v = new \WC_Product_Variation();
			$v->set_parent_id( $p1_id );
			$v->set_attributes( array( 'size' => $size ) );
			$v->set_sku( $sku );
			$v->set_regular_price( '295' );
			$v->set_manage_stock( true );
			$v->set_stock_quantity( 2 );
			$v->set_stock_status( 'instock' );
			$v->set_status( 'publish' );
			$var_ids[] = $v->save();
		}

		// 5. Create Product 2 — Simple LIVE (TEST — Studio Overshirt)
		$p2 = new \WC_Product_Simple();
		$p2->set_name( 'TEST — Studio Overshirt' );
		$p2->set_slug( 'test-studio-overshirt' );
		$p2->set_sku( 'TEST-LD01-SO' );
		$p2->set_status( 'publish' );
		$p2->set_regular_price( '240' );
		$p2->set_manage_stock( true );
		$p2->set_stock_quantity( 5 );
		$p2->set_stock_status( 'instock' );
		$p2->set_description( 'Controlled Atomic integration fixture for simple-product and LIVE lifecycle testing.' );
		$p2->set_short_description( 'TEST fixture — not production inventory.' );
		$p2->set_category_ids( array( $cat_id ) );
		$p2->set_tag_ids( array( $tag_id ) );

		\Statement\Collector\Core\Product\Metadata::set_edition_label( $p2, 'Integration Edition' );
		\Statement\Collector\Core\Product\Metadata::set_release_state( $p2, 'LIVE' );
		$p2_id = $p2->save();
		wp_set_object_terms( $p2_id, array( $drop_id ), 'statement_drop' );

		// 6. Create Product 3 — Terminal Positive-Stock Test (TEST — Terminal Jacket)
		$p3 = new \WC_Product_Simple();
		$p3->set_name( 'TEST — Terminal Jacket' );
		$p3->set_slug( 'test-terminal-jacket' );
		$p3->set_sku( 'TEST-LD01-TJ' );
		$p3->set_status( 'publish' );
		$p3->set_regular_price( '275' );
		$p3->set_manage_stock( true );
		$p3->set_stock_quantity( 5 );
		$p3->set_stock_status( 'instock' );
		$p3->set_description( 'Controlled Atomic integration fixture for terminal-lifecycle and SOLD_OUT testing.' );
		$p3->set_short_description( 'TEST fixture — not production inventory.' );
		$p3->set_category_ids( array( $cat_id ) );
		$p3->set_tag_ids( array( $tag_id ) );

		// Initial state UPCOMING -> LIVE -> SOLD_OUT via canonical transition
		\Statement\Collector\Core\Product\Metadata::set_edition_label( $p3, 'Integration Edition' );
		\Statement\Collector\Core\Product\Metadata::set_release_state( $p3, 'LIVE' );
		\Statement\Collector\Core\Product\Metadata::set_release_state( $p3, 'SOLD_OUT' );
		$p3_id = $p3->save();
		wp_set_object_terms( $p3_id, array( $drop_id ), 'statement_drop' );

		// 7. Save Fixture Manifest
		$manifest = array(
			'schema_version'    => '1.0.0',
			'created_at'        => current_time( 'mysql' ),
			'plugin_version'    => STATEMENT_INTEGRATION_FIXTURES_VERSION,
			'previous_currency' => $previous_currency,
			'current_currency'  => 'AUD',
			'category_id'       => $cat_id,
			'product_tag_id'    => $tag_id,
			'drop_id'           => $drop_id,
			'product_ids'       => array( $p1_id, $p2_id, $p3_id ),
			'variation_ids'     => $var_ids,
			'skus'              => array( 'TEST-LD01-MJ', 'TEST-LD01-MJ-S', 'TEST-LD01-MJ-M', 'TEST-LD01-MJ-L', 'TEST-LD01-SO', 'TEST-LD01-TJ' ),
		);

		update_option( self::MANIFEST_OPTION, $manifest );

		return array(
			'success'  => true,
			'message'  => 'Approved integration fixtures created successfully.',
			'manifest' => $manifest,
		);
	}
}
