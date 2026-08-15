<?php

namespace Statement\Integration\Fixtures;

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\Secrets;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

class PrivateFixtureService {
	public const PRIVATE_DROP_SLUG = 'test-private-drop-01';
	public const PRIVATE_DROP_NAME = 'TEST — Private Drop 01';
	public const PRIVATE_PRODUCT_SKU = 'TEST-PD01-PAJ';
	public const PRIVATE_PRODUCT_NAME = 'TEST — Private Access Jacket';
	public const MANIFEST_OPTION = 'statement_private_integration_fixture_manifest';

	/**
	 * Diagnostic check for standard AEAD crypto engine availability.
	 *
	 * @return array{sodium_available: bool, openssl_available: bool, selected_backend: string, ready: bool}
	 */
	public static function get_crypto_diagnostics(): array {
		$sodium  = function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' );
		$openssl = function_exists( 'openssl_encrypt' );

		$selected = 'UNAVAILABLE';
		if ( $sodium ) {
			$selected = 'xchacha20-poly1305';
		} elseif ( $openssl ) {
			$selected = 'aes-256-gcm';
		}

		return array(
			'sodium_available'  => $sodium,
			'openssl_available' => $openssl,
			'selected_backend'  => $selected,
			'ready'             => $sodium || $openssl,
		);
	}

	/**
	 * Diagnostic check for required Private Access secrets in wp-config.
	 *
	 * @return array{has_core: bool, identity_key: bool, rate_limit_key: bool, encryption_active_version: string, encryption_config: bool, all_configured: bool}
	 */
	public static function get_secret_diagnostics(): array {
		$has_core = class_exists( 'Statement\Collector\Core\Access\Secrets' );
		if ( ! $has_core ) {
			return array(
				'has_core'                  => false,
				'identity_key'              => false,
				'rate_limit_key'            => false,
				'encryption_active_version' => '',
				'encryption_config'         => false,
				'all_configured'            => false,
			);
		}

		$identity   = Secrets::has_identity_key();
		$rate_limit = Secrets::has_rate_limit_key();
		$active_ver = Secrets::get_active_key_version();
		$enc_config = Secrets::has_encryption_config();

		return array(
			'has_core'                  => true,
			'identity_key'              => $identity,
			'rate_limit_key'            => $rate_limit,
			'encryption_active_version' => $active_ver,
			'encryption_config'         => $enc_config,
			'all_configured'            => $identity && $rate_limit && $enc_config,
		);
	}

	/**
	 * Diagnostic check for operational database tables and schema version.
	 *
	 * @return array{tables: array<string, string>, db_version: string, all_tables_exist: bool}
	 */
	public static function get_db_diagnostics(): array {
		global $wpdb;

		$expected_tables = array(
			'grants'      => 'statement_access_grants',
			'sessions'    => 'statement_access_sessions',
			'tokens'      => 'statement_access_tokens',
			'rate_limits' => 'statement_access_rate_limits',
			'consent'     => 'statement_consent_events',
		);

		$table_status     = array();
		$all_tables_exist = true;

		if ( isset( $wpdb ) && is_object( $wpdb ) && method_exists( $wpdb, 'get_var' ) ) {
			foreach ( $expected_tables as $key => $table_name ) {
				$full_table_name = $wpdb->prefix . $table_name;
				$query           = $wpdb->prepare( 'SHOW TABLES LIKE %s', $full_table_name );
				$found           = $wpdb->get_var( $query );

				if ( $found === $full_table_name ) {
					$table_status[ $key ] = 'EXISTS';
				} else {
					$table_status[ $key ] = 'MISSING';
					$all_tables_exist     = false;
				}
			}
		} else {
			foreach ( $expected_tables as $key => $table_name ) {
				$table_status[ $key ] = 'UNCHECKED';
			}
			$all_tables_exist = false;
		}

		$db_version = function_exists( 'get_option' ) ? (string) get_option( 'statement_access_db_version', 'MISSING' ) : 'UNCHECKED';

		return array(
			'tables'           => $table_status,
			'db_version'       => $db_version,
			'all_tables_exist' => $all_tables_exist,
		);
	}

	/**
	 * Diagnostic check for private test fixture seeding state.
	 *
	 * @return string 'CREATED' | 'NOT_CREATED'
	 */
	public static function get_private_fixture_state(): string {
		if ( ! function_exists( 'get_option' ) ) {
			return 'NOT_CREATED';
		}

		$manifest = get_option( self::MANIFEST_OPTION, false );
		if ( is_array( $manifest ) && ! empty( $manifest['product_id'] ) && ! empty( $manifest['drop_id'] ) ) {
			return 'CREATED';
		}

		return 'NOT_CREATED';
	}

	/**
	 * Create Private Access test fixture using canonical Statement Core and WooCommerce APIs.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function create_private_fixture(): array {
		$secret_diag = self::get_secret_diagnostics();
		$crypto_diag = self::get_crypto_diagnostics();

		if ( ! $secret_diag['all_configured'] || ! $crypto_diag['ready'] ) {
			return array(
				'success' => false,
				'message' => 'Private Access fixture creation blocked: Required wp-config secrets or crypto backend missing.',
			);
		}

		if ( 'CREATED' === self::get_private_fixture_state() ) {
			return array(
				'success' => false,
				'message' => 'Private Access test fixture is already created.',
			);
		}

		if ( ! function_exists( 'wp_insert_term' ) || ! class_exists( 'WC_Product_Simple' ) ) {
			return array(
				'success' => false,
				'message' => 'Required WordPress or WooCommerce APIs are not available.',
			);
		}

		// 1. Create or retrieve Drop term
		$term_result = wp_insert_term(
			self::PRIVATE_DROP_NAME,
			'statement_drop',
			array( 'slug' => self::PRIVATE_DROP_SLUG )
		);

		if ( is_wp_error( $term_result ) ) {
			$term = get_term_by( 'slug', self::PRIVATE_DROP_SLUG, 'statement_drop' );
			if ( ! $term || is_wp_error( $term ) ) {
				return array(
					'success' => false,
					'message' => 'Failed to create or resolve Private Drop term.',
				);
			}
			$drop_id = (int) $term->term_id;
		} else {
			$drop_id = (int) $term_result['term_id'];
		}

		// 2. Create Simple Product
		$product = new \WC_Product_Simple();
		$product->set_name( self::PRIVATE_PRODUCT_NAME );
		$product->set_sku( self::PRIVATE_PRODUCT_SKU );
		$product->set_regular_price( '310' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 3 );
		$product->set_stock_status( 'instock' );

		$product_id = $product->save();
		if ( ! $product_id ) {
			return array(
				'success' => false,
				'message' => 'Failed to create Private Access Simple Product.',
			);
		}

		// 3. Assign Drop term
		wp_set_object_terms( $product_id, array( $drop_id ), 'statement_drop' );

		// 4. Set Edition Label and Lifecycle State (UPCOMING -> PRIVATE_ACCESS)
		Metadata::set_edition_label( $product_id, 'Private Integration Edition' );
		Metadata::set_release_state( $product_id, ReleaseState::UPCOMING );
		Metadata::set_release_state( $product_id, ReleaseState::PRIVATE_ACCESS );

		// 5. Configure Drop Config
		$close_timestamp = time() + ( 24 * 3600 );
		$close_iso       = gmdate( 'c', $close_timestamp );

		DropConfig::save_config(
			$drop_id,
			array(
				'close_at'            => $close_iso,
				'individual_duration' => 120,
				'send_email'          => false,
				'reminder_enabled'    => false,
				'reminder_delay'      => 60,
			)
		);

		// 6. Save Manifest
		$manifest = array(
			'schema_version'      => '1.0.0',
			'created_at'          => gmdate( 'c' ),
			'drop_id'             => $drop_id,
			'drop_slug'           => self::PRIVATE_DROP_SLUG,
			'product_id'          => $product_id,
			'sku'                 => self::PRIVATE_PRODUCT_SKU,
			'configured_close'    => $close_iso,
			'individual_duration' => 120,
			'send_email'          => false,
			'reminder_enabled'    => false,
		);

		update_option( self::MANIFEST_OPTION, $manifest );

		return array(
			'success' => true,
			'message' => sprintf( 'Private Access test fixture created successfully (Product ID: %d, Drop ID: %d).', $product_id, $drop_id ),
		);
	}

	/**
	 * Cleanup Private Access test fixture using manifest IDs.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function cleanup_private_fixture(): array {
		if ( ! function_exists( 'get_option' ) ) {
			return array(
				'success' => false,
				'message' => 'WordPress API unavailable.',
			);
		}

		$manifest = get_option( self::MANIFEST_OPTION, false );
		if ( ! is_array( $manifest ) ) {
			return array(
				'success' => false,
				'message' => 'No active Private Access test fixture manifest found.',
			);
		}

		$product_id = isset( $manifest['product_id'] ) ? (int) $manifest['product_id'] : 0;
		$drop_id    = isset( $manifest['drop_id'] ) ? (int) $manifest['drop_id'] : 0;

		// Verify ownership before deletion
		if ( $product_id > 0 ) {
			$product = wc_get_product( $product_id );
			if ( $product && self::PRIVATE_PRODUCT_SKU === $product->get_sku() ) {
				$product->delete( true );
			}
		}

		if ( $drop_id > 0 ) {
			$term = get_term( $drop_id, 'statement_drop' );
			if ( $term && ! is_wp_error( $term ) && self::PRIVATE_DROP_SLUG === $term->slug ) {
				wp_delete_term( $drop_id, 'statement_drop' );
			}
		}

		delete_option( self::MANIFEST_OPTION );

		return array(
			'success' => true,
			'message' => 'Private Access test fixture cleaned up successfully.',
		);
	}
}
