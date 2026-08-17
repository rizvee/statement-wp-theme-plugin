<?php

namespace Statement\ClientDemo;

use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Drop\Taxonomy;

defined( 'ABSPATH' ) || exit;

final class DemoSeederService {

	public const SKU_P1   = 'STMT-CD-D001-MJ';
	public const SKU_P1_S = 'STMT-CD-D001-MJ-S';
	public const SKU_P1_M = 'STMT-CD-D001-MJ-M';
	public const SKU_P1_L = 'STMT-CD-D001-MJ-L';

	public const SKU_P2   = 'STMT-CD-D001-PHJ';
	public const SKU_P2_S = 'STMT-CD-D001-PHJ-S';
	public const SKU_P2_M = 'STMT-CD-D001-PHJ-M';
	public const SKU_P2_L = 'STMT-CD-D001-PHJ-L';

	/**
	 * Run dry run analysis without mutating the database.
	 *
	 * @return array<string, mixed>
	 */
	public static function dry_run(): array {
		$assets   = AssetRegistry::get_assets();
		$manifest = ManifestService::get_manifest();
		$rollback = ManifestService::get_rollback();
		$hashes   = ManifestService::get_hashes();

		$current_front_type  = get_option( 'show_on_front', 'posts' );
		$current_front_id    = (int) get_option( 'page_on_front', 0 );
		$current_front_title = $current_front_id > 0 ? get_the_title( $current_front_id ) : 'Latest Posts';

		$existing_media = array();
		foreach ( $assets as $key => $asset ) {
			$att_id                 = self::find_attachment_by_key( $key );
			$existing_media[ $key ] = array(
				'file'   => $asset['file'],
				'status' => $att_id > 0 ? "Existing (ID: {$att_id})" : 'Ready to import',
				'id'     => $att_id,
			);
		}

		$drop_term    = function_exists( 'get_term_by' ) ? get_term_by( 'slug', 'drop-001-monogram-study', Taxonomy::KEY ) : null;
		$prod1        = self::find_owned_product( self::SKU_P1, 'monogram-jacquard-jacket' );
		$prod2        = self::find_owned_product( self::SKU_P2, 'panelled-hood-jacket' );
		$home_page    = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'statement-home', OBJECT, array( 'page' ) ) : null;
		$drops_page   = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'drops', OBJECT, array( 'page' ) ) : null;
		$about_page   = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'about', OBJECT, array( 'page' ) ) : null;
		$journal_page = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'journal', OBJECT, array( 'page' ) ) : null;

		$post1 = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'study-and-form-monogram-study', OBJECT, array( 'post' ) ) : null;
		$post2 = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'the-object', OBJECT, array( 'post' ) ) : null;

		return array(
			'mode'            => 'DRY_RUN',
			'timestamp'       => gmdate( 'Y-m-d H:i:s' ),
			'assets_total'    => count( $assets ),
			'assets_plan'     => $existing_media,
			'drop_plan'       => array(
				'name'   => 'Drop 001 — Monogram Study',
				'slug'   => 'drop-001-monogram-study',
				'status' => is_object( $drop_term ) ? "Existing (ID: {$drop_term->term_id})" : 'Ready to create (LIVE state)',
			),
			'product_01_plan' => array(
				'title'      => 'Monogram Jacquard Jacket',
				'slug'       => 'monogram-jacquard-jacket',
				'sku'        => self::SKU_P1,
				'type'       => 'variable (S, M, L)',
				'demo_price' => 'AUD 295.00',
				'status'     => is_object( $prod1 ) ? "Existing (ID: {$prod1->get_id()})" : 'Ready to create (Owned)',
			),
			'product_02_plan' => array(
				'title'      => 'Panelled Hood Jacket',
				'slug'       => 'panelled-hood-jacket',
				'sku'        => self::SKU_P2,
				'type'       => 'variable (S, M, L)',
				'demo_price' => 'AUD 275.00',
				'status'     => is_object( $prod2 ) ? "Existing (ID: {$prod2->get_id()})" : 'Ready to create (Owned)',
			),
			'pages_plan'      => array(
				'statement_home' => is_object( $home_page ) ? "Existing (ID: {$home_page->ID})" : 'Ready to create',
				'drops'          => is_object( $drops_page ) ? "Existing (ID: {$drops_page->ID})" : 'Ready to create',
				'about'          => is_object( $about_page ) ? "Existing (ID: {$about_page->ID})" : 'Ready to create',
				'journal'        => is_object( $journal_page ) ? "Existing (ID: {$journal_page->ID})" : 'Ready to create',
			),
			'journal_posts'   => array(
				'study_and_form' => is_object( $post1 ) ? "Existing (ID: {$post1->ID})" : 'Ready to create',
				'the_object'     => is_object( $post2 ) ? "Existing (ID: {$post2->ID})" : 'Ready to create',
			),
			'front_page_plan' => array(
				'current_setting' => "show_on_front='{$current_front_type}', page_on_front={$current_front_id} ('{$current_front_title}')",
				'action'          => "Preserve rollback and switch page_on_front to 'Statement Home'",
				'rollback_stored' => ! empty( $rollback ),
			),
			'demo_markers'    => array(
				'_statement_client_demo'      => 1,
				'_statement_demo_price'       => 1,
				'_statement_demo_stock'       => 1,
				'_statement_demo_measurements' => 1,
			),
		);
	}

	/**
	 * Seed or update the client demo content idempotently.
	 *
	 * @return array<string, mixed>
	 */
	public static function seed_or_update(): array {
		$report = array(
			'mode'       => 'SEED_EXECUTE',
			'timestamp'  => gmdate( 'Y-m-d H:i:s' ),
			'media'      => array(),
			'drop_id'    => 0,
			'products'   => array(),
			'pages'      => array(),
			'posts'      => array(),
			'front_page' => array(),
			'errors'     => array(),
		);

		// 1. Import Media
		$imported_media = self::import_assets( $report );

		// 2. Seed Drop
		$drop_id = self::seed_drop( $report );
		$report['drop_id'] = $drop_id;

		// 3. Seed Products
		self::seed_products( $drop_id, $imported_media, $report );

		// 4. Seed Pages
		$pages = self::seed_pages( $imported_media, $report );

		// 5. Seed Journal Posts
		self::seed_journal_posts( $imported_media, $report );

		// 6. Switch Front Page safely
		if ( isset( $pages['statement_home'] ) && $pages['statement_home'] > 0 ) {
			self::switch_front_page( $pages['statement_home'], $report );
		}

		// 7. Save Manifest v2
		$manifest_data = array(
			'manifest_version' => '2.0',
			'seeded_at'        => gmdate( 'Y-m-d H:i:s' ),
			'drop_id'          => $drop_id,
			'products'         => $report['products'],
			'pages'            => $pages,
			'posts'            => $report['posts'],
			'media_count'      => count( $imported_media ),
		);
		ManifestService::save_manifest( $manifest_data );

		return $report;
	}

	/**
	 * Repair client demo: audits manifest, detects unowned IDs, and fixes references.
	 *
	 * @return array<string, mixed>
	 */
	public static function repair_client_demo(): array {
		$manifest = ManifestService::get_manifest();
		$repairs  = array(
			'mode'      => 'REPAIR_EXECUTE',
			'timestamp' => gmdate( 'Y-m-d H:i:s' ),
			'detached'  => array(),
			'repaired'  => array(),
		);

		// Check product 01
		$prod1 = self::find_owned_product( self::SKU_P1, 'monogram-jacquard-jacket' );
		if ( ! is_object( $prod1 ) ) {
			$repairs['detached'][] = 'Product 01 unowned or collision detected; re-seeding owned product.';
		}

		// Check product 02
		$prod2 = self::find_owned_product( self::SKU_P2, 'panelled-hood-jacket' );
		if ( ! is_object( $prod2 ) ) {
			$repairs['detached'][] = 'Product 02 unowned or collision detected; re-seeding owned product.';
		}

		// Run seed/update to ensure clean state
		$seed_report = self::seed_or_update();
		$repairs['seed_report'] = $seed_report;

		return $repairs;
	}

	/**
	 * Import assets into Media Library.
	 *
	 * @param array<string, mixed> $report Report array reference.
	 * @return array<string, int>
	 */
	private static function import_assets( array &$report ): array {
		$assets = AssetRegistry::get_assets();
		$result = array();

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$upload_dir = wp_upload_dir();

		foreach ( $assets as $key => $asset ) {
			$existing_id = self::find_attachment_by_key( $key );
			if ( $existing_id > 0 ) {
				$result[ $key ] = $existing_id;
				$report['media'][ $key ] = array(
					'id'     => $existing_id,
					'status' => 'reused',
				);
				continue;
			}

			$source_file = STATEMENT_CLIENT_DEMO_DIR . '/assets/images/' . $asset['file'];
			if ( ! file_exists( $source_file ) ) {
				$report['errors'][] = "Source asset not found: {$asset['file']}";
				continue;
			}

			$filename  = basename( $source_file );
			$dest_file = $upload_dir['path'] . '/' . $filename;
			copy( $source_file, $dest_file );

			$filetype = wp_check_filetype( $filename, null );
			$attachment = array(
				'guid'           => $upload_dir['url'] . '/' . $filename,
				'post_mime_type' => $filetype['type'],
				'post_title'     => $asset['title'],
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment, $dest_file );
			if ( ! is_wp_error( $attach_id ) && $attach_id > 0 ) {
				$attach_data = wp_generate_attachment_metadata( $attach_id, $dest_file );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				update_post_meta( $attach_id, '_statement_demo_asset_key', $key );
				update_post_meta( $attach_id, '_statement_client_demo', 1 );
				update_post_meta( $attach_id, '_wp_attachment_image_alt', $asset['alt'] );

				$result[ $key ] = $attach_id;
				$report['media'][ $key ] = array(
					'id'     => $attach_id,
					'status' => 'imported',
				);
			}
		}

		return $result;
	}

	/**
	 * Seed or adopt Drop 001.
	 *
	 * @param array<string, mixed> $report Report array reference.
	 * @return int Term ID.
	 */
	private static function seed_drop( array &$report ): int {
		$slug      = 'drop-001-monogram-study';
		$term_name = 'Drop 001 — Monogram Study';

		$term = function_exists( 'get_term_by' ) ? get_term_by( 'slug', $slug, Taxonomy::KEY ) : null;
		if ( is_object( $term ) && isset( $term->term_id ) ) {
			update_term_meta( (int) $term->term_id, '_statement_client_demo', 1 );
			return (int) $term->term_id;
		}

		$created = wp_insert_term(
			$term_name,
			Taxonomy::KEY,
			array(
				'slug'        => $slug,
				'description' => 'A study in repeating surface, restrained geometry, and structural wool.',
			)
		);

		if ( is_array( $created ) && isset( $created['term_id'] ) ) {
			$term_id = (int) $created['term_id'];
			update_term_meta( $term_id, '_statement_client_demo', 1 );
			return $term_id;
		}

		return 0;
	}

	/**
	 * Seed demo products and variations.
	 *
	 * @param int                  $drop_id Drop term ID.
	 * @param array<string, int>   $media Media key-to-ID dictionary.
	 * @param array<string, mixed> $report Report array reference.
	 */
	private static function seed_products( int $drop_id, array $media, array &$report ): void {
		$hashes = ManifestService::get_hashes();

		// Product 01: Monogram Jacquard Jacket
		$p1_title = 'Monogram Jacquard Jacket';
		$p1_short = 'Statement monogram outer layer. Drop 001 — Monogram Study.';
		$p1_desc  = 'A structured outer layer defined by Statement\'s repeating monogram and a controlled silhouette. Graphic at the surface, restrained in form.';
		$p1_feat  = $media['monogram_front'] ?? 0;
		$p1_gall  = array_filter(
			array(
				$media['monogram_back'] ?? 0,
				$media['monogram_flatlay_slate'] ?? 0,
				$media['monogram_collar_detail'] ?? 0,
				$media['monogram_flatlay_concrete'] ?? 0,
			)
		);

		$prod1_id = self::create_or_update_product(
			array(
				'sku'         => self::SKU_P1,
				'slug'        => 'monogram-jacquard-jacket',
				'title'       => $p1_title,
				'short_desc'  => $p1_short,
				'desc'        => $p1_desc,
				'price'       => '295.00',
				'edition'     => 'MONOGRAM STUDY / DROP 001',
				'featured_id' => $p1_feat,
				'gallery_ids' => $p1_gall,
				'drop_id'     => $drop_id,
				'variations'  => array(
					array( 'size' => 'S', 'sku' => self::SKU_P1_S, 'stock' => 4 ),
					array( 'size' => 'M', 'sku' => self::SKU_P1_M, 'stock' => 6 ),
					array( 'size' => 'L', 'sku' => self::SKU_P1_L, 'stock' => 4 ),
				),
			),
			$hashes,
			$report
		);
		$report['products']['product_01'] = $prod1_id;

		// Product 02: Panelled Hood Jacket
		$p2_title = 'Panelled Hood Jacket';
		$p2_short = 'A light-bodied hooded layer framed by patterned sleeve panels.';
		$p2_desc  = 'Contrasting panels frame the silhouette while the Statement insignia holds the centre with minimal interruption.';
		$p2_feat  = $media['hood_front'] ?? 0;
		$p2_gall  = array_filter(
			array(
				$media['hood_back'] ?? 0,
				$media['hood_cathedral'] ?? 0,
				$media['hood_embroidery'] ?? 0,
				$media['hood_night'] ?? 0,
			)
		);

		$prod2_id = self::create_or_update_product(
			array(
				'sku'         => self::SKU_P2,
				'slug'        => 'panelled-hood-jacket',
				'title'       => $p2_title,
				'short_desc'  => $p2_short,
				'desc'        => $p2_desc,
				'price'       => '275.00',
				'edition'     => 'MONOGRAM STUDY / DROP 001',
				'featured_id' => $p2_feat,
				'gallery_ids' => $p2_gall,
				'drop_id'     => $drop_id,
				'variations'  => array(
					array( 'size' => 'S', 'sku' => self::SKU_P2_S, 'stock' => 4 ),
					array( 'size' => 'M', 'sku' => self::SKU_P2_M, 'stock' => 4 ),
					array( 'size' => 'L', 'sku' => self::SKU_P2_L, 'stock' => 4 ),
				),
			),
			$hashes,
			$report
		);
		$report['products']['product_02'] = $prod2_id;

		ManifestService::save_hashes( $hashes );
	}

	/**
	 * Find strictly owned demo product by SKU or slug with _statement_client_demo marker.
	 */
	public static function find_owned_product( string $sku, string $slug ): ?object {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return null;
		}

		// Search by SKU first
		$prods = wc_get_products(
			array(
				'sku'    => $sku,
				'limit'  => 1,
				'return' => 'objects',
			)
		);

		if ( is_array( $prods ) && ! empty( $prods ) && is_object( $prods[0] ) ) {
			$prod = $prods[0];
			if ( (int) get_post_meta( $prod->get_id(), '_statement_client_demo', true ) === 1 ) {
				return $prod;
			}
		}

		// Search by slug
		$prods_slug = wc_get_products(
			array(
				'slug'   => $slug,
				'limit'  => 1,
				'return' => 'objects',
			)
		);

		if ( is_array( $prods_slug ) && ! empty( $prods_slug ) && is_object( $prods_slug[0] ) ) {
			$prod = $prods_slug[0];
			// Safety: Reject QA fixtures
			$title = $prod->get_name();
			if ( 0 === strpos( $title, 'TEST —' ) || (int) get_post_meta( $prod->get_id(), '_statement_fixture', true ) === 1 ) {
				return null;
			}
			return $prod;
		}

		return null;
	}

	/**
	 * Create or update product safely preserving admin modifications.
	 *
	 * @param array<string, mixed> $spec Product spec.
	 * @param array<string, string> $hashes Hashes reference.
	 * @param array<string, mixed> $report Report reference.
	 * @return int Product ID.
	 */
	private static function create_or_update_product( array $spec, array &$hashes, array &$report ): int {
		$existing = self::find_owned_product( $spec['sku'], $spec['slug'] );
		$product  = is_object( $existing ) ? $existing : new \WC_Product_Variable();

		$is_new = ! is_object( $existing ) || $existing->get_id() < 1;

		$content_key  = 'prod_' . $spec['slug'];
		$content_hash = md5( $spec['title'] . '|' . $spec['short_desc'] . '|' . $spec['desc'] );

		$should_update_content = $is_new || ! isset( $hashes[ $content_key ] ) || $hashes[ $content_key ] === md5( $product->get_name() . '|' . $product->get_short_description() . '|' . $product->get_description() );

		if ( $should_update_content ) {
			$product->set_name( $spec['title'] );
			$product->set_slug( $spec['slug'] );
			$product->set_short_description( $spec['short_desc'] );
			$product->set_description( $spec['desc'] );
			$hashes[ $content_key ] = $content_hash;
		} else {
			$report['products'][ $spec['slug'] . '_status' ] = 'ADMIN_MODIFIED_PRESERVED';
		}

		$product->set_sku( $spec['sku'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );

		if ( $spec['featured_id'] > 0 ) {
			$product->set_image_id( $spec['featured_id'] );
		}
		if ( ! empty( $spec['gallery_ids'] ) ) {
			$product->set_gallery_image_ids( $spec['gallery_ids'] );
		}

		// Setup Size Attribute
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'Size' );
		$attribute->set_options( array( 'S', 'M', 'L' ) );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );

		$product_id = $product->save();

		// Metadata
		update_post_meta( $product_id, '_statement_client_demo', 1 );
		update_post_meta( $product_id, '_statement_demo_price', 1 );
		update_post_meta( $product_id, '_statement_demo_stock', 1 );
		update_post_meta( $product_id, '_statement_demo_measurements', 1 );
		update_post_meta( $product_id, Metadata::EDITION_KEY, $spec['edition'] );
		Metadata::set_release_state( $product, ReleaseState::LIVE );
		$product->save();

		// Taxonomy Drop
		if ( $spec['drop_id'] > 0 ) {
			wp_set_object_terms( $product_id, array( $spec['drop_id'] ), Taxonomy::KEY, false );
		}

		// Variations
		foreach ( $spec['variations'] as $vspec ) {
			self::create_or_update_variation( $product_id, $vspec, $spec['price'] );
		}

		return (int) $product_id;
	}

	/**
	 * Create or update product variation.
	 *
	 * @param int                  $product_id Parent product ID.
	 * @param array<string, mixed> $vspec Variation spec.
	 * @param string               $price Price string.
	 */
	private static function create_or_update_variation( int $product_id, array $vspec, string $price ): void {
		$var_id    = self::find_variation_by_sku( $product_id, $vspec['sku'] );
		$variation = $var_id > 0 ? new \WC_Product_Variation( $var_id ) : new \WC_Product_Variation();

		$variation->set_parent_id( $product_id );
		$variation->set_sku( $vspec['sku'] );
		$variation->set_attributes( array( 'size' => $vspec['size'] ) );
		$variation->set_regular_price( $price );
		$variation->set_price( $price );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( (int) $vspec['stock'] );
		$variation->set_stock_status( 'instock' );
		$variation->set_status( 'publish' );

		$saved_id = $variation->save();

		update_post_meta( $saved_id, '_statement_client_demo', 1 );
		update_post_meta( $saved_id, '_statement_demo_stock', 1 );
		update_post_meta( $saved_id, '_statement_demo_price', 1 );
	}

	/**
	 * Seed necessary demo pages.
	 *
	 * @param array<string, int>   $media Media dictionary.
	 * @param array<string, mixed> $report Report array reference.
	 * @return array<string, int>
	 */
	private static function seed_pages( array $media, array &$report ): array {
		$pages = array();

		// Statement Home
		$pages['statement_home'] = self::create_or_update_page(
			'statement-home',
			'Statement Home',
			'',
			'default',
			$media['monogram_front'] ?? 0
		);

		// Drops Page
		$pages['drops'] = self::create_or_update_page(
			'drops',
			'Drops',
			'',
			'page-drops.php',
			$media['hood_front'] ?? 0
		);

		// About Page
		$about_content = "<p class=\"statement-lead\">Statement approaches clothing through considered releases, distinct graphics and a focus on the object itself.</p>\n<p>Each release is developed as an isolated study in form, fabric, and surface geometry.</p>";
		$pages['about'] = self::create_or_update_page(
			'about',
			'About Statement',
			$about_content,
			'default',
			$media['brand_patch_palm'] ?? 0
		);

		// Journal Index Page
		$pages['journal'] = self::create_or_update_page(
			'journal',
			'Journal',
			'',
			'default',
			$media['wordmark'] ?? 0
		);

		$report['pages'] = $pages;
		return $pages;
	}

	/**
	 * Seed Journal editorial posts.
	 *
	 * @param array<string, int>   $media Media dictionary.
	 * @param array<string, mixed> $report Report reference.
	 */
	private static function seed_journal_posts( array $media, array &$report ): void {
		$posts = array();

		// Post 01: STUDY & FORM
		$p1_content = "<p class=\"statement-lead\">A photographic record of Drop 001 — Monogram Study. Built around repeating surface, structural wool, and contrasting sleeve paneling.</p>";
		$post1_id = self::create_or_update_post(
			'study-and-form-monogram-study',
			'STUDY & FORM',
			$p1_content,
			$media['monogram_flatlay_slate'] ?? 0
		);
		$posts['study_and_form'] = $post1_id;

		// Post 02: THE OBJECT
		$p2_content = "<p class=\"statement-lead\">Packaging, mark, and material identity. Every Statement piece is housed in structural dust-bag cotton with heavy leather insignia branding.</p>";
		$post2_id = self::create_or_update_post(
			'the-object',
			'THE OBJECT',
			$p2_content,
			$media['dust_bag'] ?? 0
		);
		$posts['the_object'] = $post2_id;

		$report['posts'] = $posts;
	}

	/**
	 * Create or update standard page.
	 */
	private static function create_or_update_page( string $slug, string $title, string $content, string $template = 'default', int $feat_id = 0 ): int {
		$page    = function_exists( 'get_page_by_path' ) ? get_page_by_path( $slug, OBJECT, array( 'page' ) ) : null;
		$page_id = is_object( $page ) ? (int) $page->ID : 0;

		$data = array(
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => $content,
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
		);

		if ( $page_id > 0 ) {
			$data['ID'] = $page_id;
			wp_update_post( $data );
		} else {
			$page_id = wp_insert_post( $data );
		}

		if ( $feat_id > 0 ) {
			set_post_thumbnail( $page_id, $feat_id );
		}

		update_post_meta( $page_id, '_wp_page_template', $template );
		update_post_meta( $page_id, '_statement_client_demo', 1 );

		return (int) $page_id;
	}

	/**
	 * Create or update editorial post.
	 */
	private static function create_or_update_post( string $slug, string $title, string $content, int $feat_id = 0 ): int {
		$post    = function_exists( 'get_page_by_path' ) ? get_page_by_path( $slug, OBJECT, array( 'post' ) ) : null;
		$post_id = is_object( $post ) ? (int) $post->ID : 0;

		$data = array(
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_content'   => $content,
			'post_status'    => 'publish',
			'post_type'      => 'post',
			'comment_status' => 'closed',
		);

		if ( $post_id > 0 ) {
			$data['ID'] = $post_id;
			wp_update_post( $data );
		} else {
			$post_id = wp_insert_post( $data );
		}

		if ( $feat_id > 0 ) {
			set_post_thumbnail( $post_id, $feat_id );
		}

		update_post_meta( $post_id, '_statement_client_demo', 1 );

		return (int) $post_id;
	}

	/**
	 * Switch WordPress front page safely preserving rollback.
	 */
	private static function switch_front_page( int $home_id, array &$report ): void {
		$prev_show_on_front = get_option( 'show_on_front', 'posts' );
		$prev_page_on_front = (int) get_option( 'page_on_front', 0 );
		$prev_template      = $prev_page_on_front > 0 ? get_post_meta( $prev_page_on_front, '_wp_page_template', true ) : 'default';

		ManifestService::save_rollback(
			array(
				'show_on_front' => $prev_show_on_front,
				'page_on_front' => $prev_page_on_front,
				'template'      => $prev_template,
				'preserved_at'  => gmdate( 'Y-m-d H:i:s' ),
			)
		);

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );

		$report['front_page'] = array(
			'previous' => array(
				'show_on_front' => $prev_show_on_front,
				'page_on_front' => $prev_page_on_front,
			),
			'current'  => array(
				'show_on_front' => 'page',
				'page_on_front' => $home_id,
			),
		);
	}

	/**
	 * Find attachment ID by demo asset key.
	 */
	public static function find_attachment_by_key( string $key ): int {
		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return 0;
		}

		$query = $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_statement_demo_asset_key' AND meta_value = %s LIMIT 1",
			$key
		);
		$id = $wpdb->get_var( $query );

		return $id ? (int) $id : 0;
	}

	/**
	 * Find variation ID by SKU.
	 */
	private static function find_variation_by_sku( int $product_id, string $sku ): int {
		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return 0;
		}

		$query = $wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_parent = %d AND p.post_type = 'product_variation' AND pm.meta_key = '_sku' AND pm.meta_value = %s
			 LIMIT 1",
			$product_id,
			$sku
		);
		$id = $wpdb->get_var( $query );

		return $id ? (int) $id : 0;
	}
}
