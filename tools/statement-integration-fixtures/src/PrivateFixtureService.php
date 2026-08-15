<?php

namespace Statement\Integration\Fixtures;

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\Secrets;
use Statement\Collector\Core\Access\SecretVault;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

class PrivateFixtureService {
	public const PRIVATE_DROP_SLUG    = 'test-private-drop-01';
	public const PRIVATE_DROP_NAME    = 'TEST — Private Drop 01';
	public const PRIVATE_PRODUCT_SKU  = 'TEST-PD01-PAJ';
	public const PRIVATE_PRODUCT_NAME = 'TEST — Private Access Jacket';
	public const MANIFEST_OPTION      = 'statement_private_integration_fixture_manifest';

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
	 * Diagnostic check for required Private Access secrets provider status.
	 *
	 * @return array{has_core: bool, provider: string, vault_initialized: bool, identity_key: bool, rate_limit_key: bool, encryption_active_version: string, encryption_config: bool, all_configured: bool}
	 */
	public static function get_secret_diagnostics(): array {
		$has_core = class_exists( 'Statement\Collector\Core\Access\Secrets' );
		if ( ! $has_core ) {
			return array(
				'has_core'                  => false,
				'provider'                  => 'unavailable',
				'vault_initialized'         => false,
				'identity_key'              => false,
				'rate_limit_key'            => false,
				'encryption_active_version' => '',
				'encryption_config'         => false,
				'all_configured'            => false,
			);
		}

		$provider   = Secrets::get_provider();
		$vault_init = class_exists( SecretVault::class ) && SecretVault::is_initialized();
		$identity   = Secrets::has_identity_key();
		$rate_limit = Secrets::has_rate_limit_key();
		$active_ver = Secrets::get_active_key_version();
		$enc_config = Secrets::has_encryption_config();

		return array(
			'has_core'                  => true,
			'provider'                  => $provider,
			'vault_initialized'         => $vault_init,
			'identity_key'              => $identity,
			'rate_limit_key'            => $rate_limit,
			'encryption_active_version' => $active_ver,
			'encryption_config'         => $enc_config,
			'all_configured'            => Secrets::is_configured(),
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
	 * Checks if active Private Access grant or session data exists in database.
	 */
	public static function has_active_grant_data(): bool {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'get_var' ) ) {
			return false;
		}

		$grants_table   = $wpdb->prefix . 'statement_access_grants';
		$sessions_table = $wpdb->prefix . 'statement_access_sessions';

		$grants_count   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$grants_table}" );
		$sessions_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$sessions_table}" );

		return $grants_count > 0 || $sessions_count > 0;
	}

	/**
	 * Explicit administrator action to initialize Secret Vault fallback.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function init_vault(): array {
		if ( ! class_exists( SecretVault::class ) ) {
			return array(
				'success' => false,
				'message' => 'Statement Core SecretVault class is not available.',
			);
		}

		if ( 'wp_config' === Secrets::get_provider() ) {
			return array(
				'success' => false,
				'message' => 'Valid wp-config secrets are already active; Secret Vault initialization is not needed.',
			);
		}

		$ok = SecretVault::create_vault();
		if ( $ok ) {
			return array(
				'success' => true,
				'message' => 'Private Access Secret Vault initialized and verified successfully.',
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to initialize Secret Vault. Check server crypto extension support.',
		);
	}

	/**
	 * Explicit administrator action to reset test Secret Vault.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function reset_vault(): array {
		if ( ! class_exists( SecretVault::class ) ) {
			return array(
				'success' => false,
				'message' => 'Statement Core SecretVault class is not available.',
			);
		}

		if ( self::has_active_grant_data() ) {
			return array(
				'success' => false,
				'message' => 'Cannot reset Secret Vault: Active Private Access grants or sessions exist in database.',
			);
		}

		SecretVault::delete_vault();
		return array(
			'success' => true,
			'message' => 'Secret Vault reset successfully.',
		);
	}

	/**
	 * Helper to find an existing Product by exact SKU.
	 */
	public static function find_existing_product(): ?object {
		if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
			$product_id = wc_get_product_id_by_sku( self::PRIVATE_PRODUCT_SKU );
			if ( $product_id > 0 && function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $product_id );
				return is_object( $product ) ? $product : null;
			}
		}

		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products(
				array(
					'sku'   => self::PRIVATE_PRODUCT_SKU,
					'limit' => 1,
				)
			);
			if ( ! empty( $products ) && is_object( $products[0] ) ) {
				return $products[0];
			}
		}

		return null;
	}

	/**
	 * Helper to find an existing Drop term by exact slug.
	 */
	public static function find_existing_drop(): ?object {
		if ( function_exists( 'get_term_by' ) ) {
			$term = get_term_by( 'slug', self::PRIVATE_DROP_SLUG, 'statement_drop' );
			return ( $term && ! is_wp_error( $term ) && is_object( $term ) ) ? $term : null;
		}

		return null;
	}

	/**
	 * Diagnostic check for private test fixture seeding state.
	 *
	 * @return string 'NOT_CREATED' | 'PARTIAL' | 'CREATED' | 'RECOVERY_REQUIRED'
	 */
	public static function get_private_fixture_state(): string {
		if ( ! function_exists( 'get_option' ) ) {
			return 'NOT_CREATED';
		}

		$product  = self::find_existing_product();
		$drop     = self::find_existing_drop();
		$manifest = get_option( self::MANIFEST_OPTION, false );

		// Check for collision or unsafe lifecycle
		if ( is_object( $product ) ) {
			$state = class_exists( Metadata::class ) ? Metadata::get_release_state( $product ) : '';
			if ( class_exists( ReleaseState::class ) && ReleaseState::is_terminal( $state ) ) {
				return 'RECOVERY_REQUIRED';
			}
			if ( method_exists( $product, 'get_type' ) && 'simple' !== $product->get_type() ) {
				return 'RECOVERY_REQUIRED';
			}
		}

		// Check if fully verified CREATED with valid manifest
		if ( is_array( $manifest ) && ! empty( $manifest['product_id'] ) && ! empty( $manifest['drop_id'] ) ) {
			if ( is_object( $product ) && is_object( $drop ) ) {
				$pid = method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;
				$did = isset( $drop->term_id ) ? (int) $drop->term_id : 0;

				if ( $pid === (int) $manifest['product_id'] && $did === (int) $manifest['drop_id'] ) {
					$rel_state = class_exists( Metadata::class ) ? Metadata::get_release_state( $product ) : '';
					$edition   = class_exists( Metadata::class ) ? Metadata::get_edition_label( $product ) : '';
					$config    = class_exists( DropConfig::class ) ? DropConfig::get_config( $did ) : null;
					$is_valid  = ( null !== $config && class_exists( DropConfig::class ) && DropConfig::is_config_valid( $config, time() ) );

					if (
						ReleaseState::PRIVATE_ACCESS === $rel_state &&
						'Private Integration Edition' === $edition &&
						$is_valid
					) {
						return 'CREATED';
					}
				}
			}
		}

		// If either entity exists without full valid manifest/config -> PARTIAL
		if ( is_object( $product ) || is_object( $drop ) ) {
			return 'PARTIAL';
		}

		return 'NOT_CREATED';
	}

	/**
	 * Create or recover Private Access test fixture using canonical Statement Core and WooCommerce APIs.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function create_private_fixture(): array {
		$secret_diag = self::get_secret_diagnostics();
		$crypto_diag = self::get_crypto_diagnostics();

		if ( ! $secret_diag['all_configured'] || ! $crypto_diag['ready'] ) {
			return array(
				'success' => false,
				'message' => 'Private Access fixture creation blocked: Secrets provider is unavailable or invalid.',
			);
		}

		$current_state = self::get_private_fixture_state();
		if ( 'CREATED' === $current_state ) {
			return array(
				'success' => false,
				'message' => 'Private Access test fixture is already created and verified.',
			);
		}

		if ( 'RECOVERY_REQUIRED' === $current_state ) {
			return array(
				'success' => false,
				'message' => 'Private Access test fixture requires manual investigation: Collision or terminal state detected.',
			);
		}

		if ( ! function_exists( 'wp_insert_term' ) || ! class_exists( 'WC_Product_Simple' ) ) {
			return array(
				'success' => false,
				'message' => 'Required WordPress or WooCommerce APIs are not available.',
			);
		}

		// 1. Resolve or create Drop term
		$existing_drop = self::find_existing_drop();
		if ( is_object( $existing_drop ) && isset( $existing_drop->term_id ) ) {
			$drop_id = (int) $existing_drop->term_id;
		} else {
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
						'message' => 'Failed to create or resolve Private Drop term: ' . $term_result->get_error_message(),
					);
				}
				$drop_id = (int) $term->term_id;
			} else {
				$drop_id = (int) $term_result['term_id'];
			}
		}

		// 2. Resolve or create Simple Product
		$existing_product = self::find_existing_product();
		$is_adoption      = false;

		if ( is_object( $existing_product ) ) {
			$product     = $existing_product;
			$is_adoption = true;
		} else {
			$product = new \WC_Product_Simple();
		}

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
				'message' => 'Failed to persist Private Access Simple Product.',
			);
		}

		// 3. Assign Drop term
		wp_set_object_terms( $product_id, array( $drop_id ), 'statement_drop' );

		// 4. Set Edition Label and Lifecycle State using WC_Product object
		Metadata::set_edition_label( $product, 'Private Integration Edition' );

		$current_rel = Metadata::get_release_state( $product );
		if ( ReleaseState::UPCOMING === $current_rel ) {
			Metadata::set_release_state( $product, ReleaseState::PRIVATE_ACCESS );
		} elseif ( ReleaseState::PRIVATE_ACCESS !== $current_rel ) {
			if ( ! ReleaseState::can_transition( $current_rel, ReleaseState::PRIVATE_ACCESS ) ) {
				return array(
					'success' => false,
					'message' => sprintf( 'Cannot transition product release state from %s to PRIVATE_ACCESS.', $current_rel ),
				);
			}
			Metadata::set_release_state( $product, ReleaseState::PRIVATE_ACCESS );
		}

		$product->save();

		// Reload fresh product to verify persistence
		$fresh_product = wc_get_product( $product_id );
		if (
			! is_object( $fresh_product ) ||
			Metadata::get_edition_label( $fresh_product ) !== 'Private Integration Edition' ||
			Metadata::get_release_state( $fresh_product ) !== ReleaseState::PRIVATE_ACCESS
		) {
			return array(
				'success' => false,
				'message' => 'Failed to verify persisted product edition or PRIVATE_ACCESS lifecycle state.',
			);
		}

		// 5. Configure Drop Config using canonical DropConfig::save_config API
		$close_timestamp = time() + ( 24 * 3600 );
		$close_utc       = gmdate( 'Y-m-d H:i:s', $close_timestamp );

		$config_saved = DropConfig::save_config(
			$drop_id,
			array(
				'closes_at'           => $close_utc,
				'duration'            => 2,
				'duration_unit'       => 'hours',
				'send_access_email'   => 'no',
				'reminder_enabled'    => 'no',
				'reminder_delay'      => 1,
				'reminder_delay_unit' => 'hours',
			)
		);

		if ( ! $config_saved ) {
			return array(
				'success' => false,
				'message' => 'Failed to save canonical Drop configuration.',
			);
		}

		$saved_config = DropConfig::get_config( $drop_id );
		if ( ! $saved_config || ! DropConfig::is_config_valid( $saved_config, time() ) ) {
			return array(
				'success' => false,
				'message' => 'Saved Drop configuration failed validation check.',
			);
		}

		// 6. Save Manifest LAST
		$manifest = array(
			'schema_version'      => '1.0.0',
			'created_at'          => gmdate( 'c' ),
			'drop_id'             => $drop_id,
			'drop_slug'           => self::PRIVATE_DROP_SLUG,
			'product_id'          => $product_id,
			'sku'                 => self::PRIVATE_PRODUCT_SKU,
			'configured_close'    => $close_utc,
			'individual_duration' => 2,
			'duration_unit'       => 'hours',
			'send_email'          => false,
			'reminder_enabled'    => false,
		);

		update_option( self::MANIFEST_OPTION, $manifest );

		$action_desc = $is_adoption ? 'adopted and recovered' : 'created';

		return array(
			'success' => true,
			'message' => sprintf( 'Private Access test fixture %s successfully (Product ID: %d, Drop ID: %d).', $action_desc, $product_id, $drop_id ),
		);
	}

	/**
	 * Cleanup Private Access test fixture using manifest IDs or exact test identifiers.
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

		$manifest   = get_option( self::MANIFEST_OPTION, false );
		$product_id = is_array( $manifest ) && isset( $manifest['product_id'] ) ? (int) $manifest['product_id'] : 0;
		$drop_id    = is_array( $manifest ) && isset( $manifest['drop_id'] ) ? (int) $manifest['drop_id'] : 0;

		// Clean up product
		if ( $product_id > 0 && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) && self::PRIVATE_PRODUCT_SKU === $product->get_sku() ) {
				$product->delete( true );
			}
		} else {
			$existing_prod = self::find_existing_product();
			if ( is_object( $existing_prod ) && self::PRIVATE_PRODUCT_SKU === $existing_prod->get_sku() ) {
				$existing_prod->delete( true );
			}
		}

		// Clean up drop term
		if ( $drop_id > 0 && function_exists( 'get_term' ) ) {
			$term = get_term( $drop_id, 'statement_drop' );
			if ( is_object( $term ) && ! is_wp_error( $term ) && self::PRIVATE_DROP_SLUG === $term->slug ) {
				wp_delete_term( $drop_id, 'statement_drop' );
			}
		} else {
			$existing_drop = self::find_existing_drop();
			if ( is_object( $existing_drop ) && isset( $existing_drop->term_id ) && self::PRIVATE_DROP_SLUG === $existing_drop->slug ) {
				wp_delete_term( (int) $existing_drop->term_id, 'statement_drop' );
			}
		}

		delete_option( self::MANIFEST_OPTION );

		return array(
			'success' => true,
			'message' => 'Private Access test fixture cleaned up successfully.',
		);
	}
}
