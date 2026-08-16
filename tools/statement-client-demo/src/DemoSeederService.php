<?php

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

final class DemoSeederService {

	/**
	 * Run dry run analysis without mutating the database.
	 *
	 * @return array<string, mixed>
	 */
	public static function dry_run(): array {
		$assets = AssetRegistry::get_assets();
		$manifest = ManifestService::get_manifest();
		$rollback = ManifestService::get_rollback();

		$current_front_type = get_option( 'show_on_front', 'posts' );
		$current_front_id   = (int) get_option( 'page_on_front', 0 );
		$current_front_title = $current_front_id > 0 ? get_the_title( $current_front_id ) : 'Latest Posts';

		$existing_media = array();
		foreach ( $assets as $key => $asset ) {
			$att_id = self::find_attachment_by_key( $key );
			$existing_media[ $key ] = array(
				'file'   => $asset['file'],
				'status' => $att_id > 0 ? "Existing (ID: {$att_id})" : 'Ready to import',
				'id'     => $att_id,
			);
		}

		$drop_term = function_exists( 'get_term_by' ) ? get_term_by( 'slug', 'drop-001-monogram-study', 'statement_drop' ) : null;
		$prod1     = self::find_product_by_slug( 'monogram-jacquard-jacket' );
		$prod2     = self::find_product_by_slug( 'panelled-hood-jacket' );
		$home_page = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'statement-home', OBJECT, array( 'page' ) ) : null;
		$drops_page = function_exists( 'get_page_by_path' ) ? get_page_by_path( 'drops', OBJECT, array( 'page' ) ) : null;

		return array(
			'mode'               => 'DRY_RUN',
			'timestamp'          => gmdate( 'Y-m-d H:i:s' ),
			'assets_total'       => count( $assets ),
			'assets_plan'        => $existing_media,
			'drop_plan'          => array(
				'name'   => 'Drop 001 — Monogram Study',
				'slug'   => 'drop-001-monogram-study',
				'status' => is_object( $drop_term ) ? "Existing (ID: {$drop_term->term_id})" : 'Ready to create (LIVE state)',
			),
			'product_01_plan'    => array(
				'title'      => 'Monogram Jacquard Jacket',
				'slug'       => 'monogram-jacquard-jacket',
				'sku'        => 'STMT-DROP001-01',
				'type'       => 'variable (S, M, L)',
				'demo_price' => 'AUD 295.00',
				'status'     => is_object( $prod1 ) ? "Existing (ID: {$prod1->get_id()})" : 'Ready to create',
			),
			'product_02_plan'    => array(
				'title'      => 'Panelled Hood Jacket',
				'slug'       => 'panelled-hood-jacket',
				'sku'        => 'STMT-DROP001-02',
				'type'       => 'variable (S, M, L)',
				'demo_price' => 'AUD 275.00',
				'status'     => is_object( $prod2 ) ? "Existing (ID: {$prod2->get_id()})" : 'Ready to create',
			),
			'pages_plan'         => array(
				'statement_home' => is_object( $home_page ) ? "Existing (ID: {$home_page->ID})" : 'Ready to create (Theme template: default)',
				'drops'          => is_object( $drops_page ) ? "Existing (ID: {$drops_page->ID})" : 'Ready to create (Template: page-drops.php)',
			),
			'front_page_plan'    => array(
				'current_setting' => "show_on_front='{$current_front_type}', page_on_front={$current_front_id} ('{$current_front_title}')",
				'action'          => "Preserve rollback and switch page_on_front to 'Statement Home' (Theme front-page.php ownership)",
				'rollback_stored' => ! empty( $rollback ),
			),
			'demo_markers'       => array(
				'_statement_client_demo'      => 1,
				'_statement_demo_price'       => 1,
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
			'mode'        => 'SEED_EXECUTE',
			'timestamp'   => gmdate( 'Y-m-d H:i:s' ),
			'media'       => array(),
			'drop_id'     => 0,
			'products'    => array(),
			'pages'       => array(),
			'front_page'  => array(),
			'errors'      => array(),
		);

		// 1. Import or locate Media
		$imported_media = self::import_assets( $report );
		$report['media'] = $imported_media;

		// 2. Create or update Drop Term
		$drop_id = self::seed_drop( $report );
		$report['drop_id'] = $drop_id;

		// 3. Create or update Product 01 (Monogram Jacquard Jacket)
		$p1_id = self::seed_monogram_jacket( $imported_media, $drop_id, $report );
		if ( $p1_id > 0 ) {
			$report['products']['monogram_jacket'] = $p1_id;
		}

		// 4. Create or update Product 02 (Panelled Hood Jacket)
		$p2_id = self::seed_panelled_hood_jacket( $imported_media, $drop_id, $report );
		if ( $p2_id > 0 ) {
			$report['products']['panelled_hood_jacket'] = $p2_id;
		}

		// 5. Create or update Pages
		$home_id  = self::seed_page( 'Statement Home', 'statement-home', 'default', $report );
		$drops_id = self::seed_page( 'Drops', 'drops', 'page-drops.php', $report );
		$archive_id = self::seed_page( 'Archive', 'archive', 'page-archive.php', $report );
		$report['pages'] = array(
			'statement_home' => $home_id,
			'drops'          => $drops_id,
			'archive'        => $archive_id,
		);

		// 6. Front Page Ownership & Rollback preservation
		if ( $home_id > 0 ) {
			self::switch_front_page( $home_id, $report );
		}

		// 7. Save Manifest
		ManifestService::save_manifest(
			array(
				'version'        => STATEMENT_CLIENT_DEMO_VERSION,
				'updated_at'     => gmdate( 'Y-m-d H:i:s' ),
				'media_ids'      => $imported_media,
				'drop_id'        => $drop_id,
				'product_ids'    => array_values( $report['products'] ),
				'page_ids'       => $report['pages'],
				'front_page_id'  => $home_id,
			)
		);

		return $report;
	}

	/**
	 * Import asset files from plugin images folder into WP media library.
	 *
	 * @param array<string, mixed> $report Execution report reference.
	 * @return array<string, int> Map of asset_key => attachment_id.
	 */
	private static function import_assets( array &$report ): array {
		$assets = AssetRegistry::get_assets();
		$imported = array();

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$images_dir = STATEMENT_CLIENT_DEMO_DIR . '/assets/images/';

		foreach ( $assets as $key => $asset ) {
			$existing_id = self::find_attachment_by_key( $key );
			if ( $existing_id > 0 ) {
				$imported[ $key ] = $existing_id;
				continue;
			}

			$source_file = $images_dir . $asset['file'];
			if ( ! file_exists( $source_file ) ) {
				$report['errors'][] = "Source asset file not found: {$asset['file']}";
				continue;
			}

			$wp_upload_dir = wp_upload_dir();
			$target_name   = $asset['file'];
			$target_file   = $wp_upload_dir['path'] . '/' . $target_name;

			// Avoid overwriting if file already exists in uploads folder
			$i = 1;
			while ( file_exists( $target_file ) ) {
				$target_name = pathinfo( $asset['file'], PATHINFO_FILENAME ) . '-' . $i . '.' . pathinfo( $asset['file'], PATHINFO_EXTENSION );
				$target_file = $wp_upload_dir['path'] . '/' . $target_name;
				$i++;
			}

			if ( ! copy( $source_file, $target_file ) ) {
				$report['errors'][] = "Failed to copy asset {$asset['file']} to {$target_file}";
				continue;
			}

			$filetype = wp_check_filetype( basename( $target_file ), null );
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $target_file ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_text_field( $asset['title'] ),
				'post_content'   => '',
				'post_excerpt'   => sanitize_text_field( $asset['caption'] ),
				'post_status'    => 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment, $target_file );
			if ( is_wp_error( $attach_id ) || ! ( $attach_id > 0 ) ) {
				$report['errors'][] = "Failed to insert attachment for {$asset['file']}";
				continue;
			}

			$attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			update_post_meta( $attach_id, '_wp_attachment_image_alt', sanitize_text_field( $asset['alt'] ) );
			update_post_meta( $attach_id, '_statement_demo_asset_key', sanitize_key( $key ) );
			update_post_meta( $attach_id, '_statement_client_demo', 1 );

			$imported[ $key ] = (int) $attach_id;
		}

		return $imported;
	}

	/**
	 * Seed Drop 001 taxonomy term.
	 *
	 * @param array<string, mixed> $report Execution report.
	 */
	private static function seed_drop( array &$report ): int {
		$taxonomy = 'statement_drop';
		if ( ! taxonomy_exists( $taxonomy ) ) {
			$report['errors'][] = "Taxonomy {$taxonomy} does not exist in Core.";
			return 0;
		}

		$slug = 'drop-001-monogram-study';
		$term = get_term_by( 'slug', $slug, $taxonomy );

		if ( is_object( $term ) && isset( $term->term_id ) ) {
			$term_id = (int) $term->term_id;
			wp_update_term(
				$term_id,
				$taxonomy,
				array(
					'name'        => 'Drop 001 — Monogram Study',
					'description' => 'A study in repeating surface, restrained geometry, and structural wool.',
				)
			);
		} else {
			$created = wp_insert_term(
				'Drop 001 — Monogram Study',
				$taxonomy,
				array(
					'slug'        => $slug,
					'description' => 'A study in repeating surface, restrained geometry, and structural wool.',
				)
			);
			if ( is_wp_error( $created ) || ! isset( $created['term_id'] ) ) {
				$report['errors'][] = 'Failed to create Drop 001 term.';
				return 0;
			}
			$term_id = (int) $created['term_id'];
		}

		// Ensure LIVE state for visual review
		update_term_meta( $term_id, 'statement_drop_state', 'live' );
		update_term_meta( $term_id, '_statement_client_demo', 1 );
		update_term_meta( $term_id, '_statement_seo_title', 'Drop 001 — Monogram Study | Statement' );
		update_term_meta( $term_id, '_statement_seo_description', 'A study in repeating surface, restrained geometry, and structural wool.' );

		return $term_id;
	}

	/**
	 * Seed Product 01: Monogram Jacquard Jacket
	 *
	 * @param array<string, int>   $media Imported media IDs.
	 * @param int                  $drop_id Drop term ID.
	 * @param array<string, mixed> $report Execution report.
	 */
	private static function seed_monogram_jacket( array $media, int $drop_id, array &$report ): int {
		if ( ! class_exists( 'WC_Product_Variable' ) ) {
			$report['errors'][] = 'WooCommerce WC_Product_Variable is not available.';
			return 0;
		}

		$slug = 'monogram-jacquard-jacket';
		$product = self::find_product_by_slug( $slug );
		if ( ! is_object( $product ) ) {
			$product = new \WC_Product_Variable();
			$product->set_slug( $slug );
		}

		$product->set_name( 'Monogram Jacquard Jacket' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_sku( 'STMT-DROP001-01' );
		$product->set_short_description( "Statement monogram outer layer.\nDrop 001 — Monogram Study." );
		$product->set_description( "A structured outer layer defined by Statement's repeating monogram and a restrained silhouette. Graphic in surface, controlled in form." );

		// Set primary image and gallery
		if ( isset( $media['monogram_front'] ) ) {
			$product->set_image_id( $media['monogram_front'] );
		}
		$gallery = array();
		foreach ( array( 'monogram_back', 'monogram_concrete', 'monogram_collar', 'monogram_slate' ) as $gk ) {
			if ( isset( $media[ $gk ] ) ) {
				$gallery[] = $media[ $gk ];
			}
		}
		$product->set_gallery_image_ids( $gallery );

		// Configure size attribute
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'Size' );
		$attribute->set_options( array( 'S', 'M', 'L' ) );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );

		$product_id = $product->save();
		if ( ! ( $product_id > 0 ) ) {
			$report['errors'][] = 'Failed to save Monogram Jacquard Jacket.';
			return 0;
		}

		// Assign Drop Term
		if ( $drop_id > 0 ) {
			wp_set_object_terms( $product_id, array( $drop_id ), 'statement_drop' );
		}

		// Meta markers
		update_post_meta( $product_id, '_statement_client_demo', 1 );
		update_post_meta( $product_id, '_statement_demo_price', 1 );
		update_post_meta( $product_id, '_statement_demo_measurements', 1 );
		update_post_meta( $product_id, '_statement_edition_label', 'MONOGRAM STUDY / DROP 001' );
		update_post_meta( $product_id, '_statement_seo_title', 'Monogram Jacquard Jacket | Statement' );
		update_post_meta( $product_id, '_statement_seo_description', 'Structured outer layer defined by Statement repeating monogram and restrained silhouette.' );

		// Create S, M, L variations
		self::seed_product_variations( $product_id, 'STMT-DROP001-01', 295.00 );

		return $product_id;
	}

	/**
	 * Seed Product 02: Panelled Hood Jacket
	 *
	 * @param array<string, int>   $media Imported media IDs.
	 * @param int                  $drop_id Drop term ID.
	 * @param array<string, mixed> $report Execution report.
	 */
	private static function seed_panelled_hood_jacket( array $media, int $drop_id, array &$report ): int {
		if ( ! class_exists( 'WC_Product_Variable' ) ) {
			$report['errors'][] = 'WooCommerce WC_Product_Variable is not available.';
			return 0;
		}

		$slug = 'panelled-hood-jacket';
		$product = self::find_product_by_slug( $slug );
		if ( ! is_object( $product ) ) {
			$product = new \WC_Product_Variable();
			$product->set_slug( $slug );
		}

		$product->set_name( 'Panelled Hood Jacket' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_sku( 'STMT-DROP001-02' );
		$product->set_short_description( "Panelled hood layer with patterned sleeves.\nDrop 001 — Monogram Study." );
		$product->set_description( 'A light-bodied hooded layer framed by patterned sleeve panels and the Statement insignia. Clean contrast, minimal interruption.' );

		// Set primary image and gallery
		if ( isset( $media['hood_front'] ) ) {
			$product->set_image_id( $media['hood_front'] );
		}
		$gallery = array();
		foreach ( array( 'hood_back', 'hood_cathedral', 'hood_embroidery', 'hood_night' ) as $gk ) {
			if ( isset( $media[ $gk ] ) ) {
				$gallery[] = $media[ $gk ];
			}
		}
		$product->set_gallery_image_ids( $gallery );

		// Configure size attribute
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'Size' );
		$attribute->set_options( array( 'S', 'M', 'L' ) );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );

		$product_id = $product->save();
		if ( ! ( $product_id > 0 ) ) {
			$report['errors'][] = 'Failed to save Panelled Hood Jacket.';
			return 0;
		}

		// Assign Drop Term
		if ( $drop_id > 0 ) {
			wp_set_object_terms( $product_id, array( $drop_id ), 'statement_drop' );
		}

		// Meta markers
		update_post_meta( $product_id, '_statement_client_demo', 1 );
		update_post_meta( $product_id, '_statement_demo_price', 1 );
		update_post_meta( $product_id, '_statement_demo_measurements', 1 );
		update_post_meta( $product_id, '_statement_edition_label', 'MONOGRAM STUDY / DROP 001' );
		update_post_meta( $product_id, '_statement_seo_title', 'Panelled Hood Jacket | Statement' );
		update_post_meta( $product_id, '_statement_seo_description', 'Light-bodied hooded layer framed by patterned sleeve panels and Statement insignia.' );

		// Create S, M, L variations
		self::seed_product_variations( $product_id, 'STMT-DROP001-02', 275.00 );

		return $product_id;
	}

	/**
	 * Seed S, M, L variations for a variable product.
	 *
	 * @param int    $product_id Parent product ID.
	 * @param string $base_sku Base SKU prefix.
	 * @param float  $price Demo price.
	 */
	private static function seed_product_variations( int $product_id, string $base_sku, float $price ): void {
		$sizes = array( 'S', 'M', 'L' );

		foreach ( $sizes as $size ) {
			$var_sku = "{$base_sku}-{$size}";
			$var_id  = self::find_variation_by_sku( $product_id, $var_sku );

			if ( $var_id > 0 && class_exists( 'WC_Product_Variation' ) ) {
				$variation = new \WC_Product_Variation( $var_id );
			} elseif ( class_exists( 'WC_Product_Variation' ) ) {
				$variation = new \WC_Product_Variation();
				$variation->set_parent_id( $product_id );
			} else {
				continue;
			}

			$variation->set_sku( $var_sku );
			$variation->set_regular_price( (string) $price );
			$variation->set_price( (string) $price );
			$variation->set_manage_stock( true );
			$variation->set_stock_quantity( 10 );
			$variation->set_stock_status( 'instock' );
			$variation->set_attributes( array( 'size' => $size, 'pa_size' => $size ) );
			$variation_id = $variation->save();

			if ( $variation_id > 0 ) {
				update_post_meta( $variation_id, '_statement_client_demo', 1 );
				update_post_meta( $variation_id, '_statement_demo_price', 1 );
			}
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients( $product_id );
		}
	}

	/**
	 * Create or update a static page.
	 *
	 * @param string               $title Page title.
	 * @param string               $slug Page slug.
	 * @param string               $template Template filename or 'default'.
	 * @param array<string, mixed> $report Execution report.
	 */
	private static function seed_page( string $title, string $slug, string $template, array &$report ): int {
		$page = function_exists( 'get_page_by_path' ) ? get_page_by_path( $slug, OBJECT, array( 'page' ) ) : null;

		if ( is_object( $page ) && isset( $page->ID ) ) {
			$page_id = (int) $page->ID;
			wp_update_post(
				array(
					'ID'          => $page_id,
					'post_title'  => $title,
					'post_status' => 'publish',
				)
			);
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'  => $title,
					'post_name'   => $slug,
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_content'=> '',
				)
			);
			if ( is_wp_error( $page_id ) || ! ( $page_id > 0 ) ) {
				$report['errors'][] = "Failed to create page {$title} ({$slug}).";
				return 0;
			}
		}

		update_post_meta( $page_id, '_wp_page_template', $template );
		update_post_meta( $page_id, '_statement_client_demo', 1 );

		return (int) $page_id;
	}

	/**
	 * Switch WordPress front page to Statement Home while preserving rollback metadata.
	 *
	 * @param int                  $home_id New front page ID.
	 * @param array<string, mixed> $report Execution report.
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
				'template'      => $prev_template,
			),
			'current'  => array(
				'show_on_front' => 'page',
				'page_on_front' => $home_id,
				'template'      => 'default',
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
	 * Find WooCommerce product by slug.
	 */
	public static function find_product_by_slug( string $slug ): ?object {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return null;
		}

		$prods = wc_get_products(
			array(
				'slug'   => $slug,
				'limit'  => 1,
				'return' => 'objects',
			)
		);

		return ( is_array( $prods ) && ! empty( $prods ) && is_object( $prods[0] ) ) ? $prods[0] : null;
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
