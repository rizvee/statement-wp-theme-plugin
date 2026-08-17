<?php

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

final class ManifestService {

	public const OPTION_MANIFEST_V1 = 'statement_client_demo_manifest_v1';
	public const OPTION_MANIFEST_V2 = 'statement_client_demo_manifest_v2';
	public const OPTION_ROLLBACK    = 'statement_client_demo_rollback';
	public const OPTION_HASHES      = 'statement_client_demo_hashes';

	/**
	 * Retrieve the current manifest from options (with auto-migration check).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_manifest(): array {
		$manifest_v2 = get_option( self::OPTION_MANIFEST_V2, null );
		if ( is_array( $manifest_v2 ) ) {
			return $manifest_v2;
		}

		$manifest_v1 = get_option( self::OPTION_MANIFEST_V1, null );
		if ( is_array( $manifest_v1 ) ) {
			return $manifest_v1;
		}

		return array();
	}

	/**
	 * Save updated v2 manifest.
	 *
	 * @param array<string, mixed> $data Manifest data.
	 */
	public static function save_manifest( array $data ): bool {
		$data['manifest_version'] = '2.0';
		$data['updated_at']       = gmdate( 'Y-m-d H:i:s' );
		return update_option( self::OPTION_MANIFEST_V2, $data, false );
	}

	/**
	 * Retrieve rollback data.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_rollback(): array {
		$rollback = get_option( self::OPTION_ROLLBACK, array() );
		return is_array( $rollback ) ? $rollback : array();
	}

	/**
	 * Save rollback data if not already preserved.
	 *
	 * @param array<string, mixed> $rollback Rollback data.
	 */
	public static function save_rollback( array $rollback ): bool {
		$existing = self::get_rollback();
		if ( empty( $existing ) ) {
			return update_option( self::OPTION_ROLLBACK, $rollback, false );
		}
		return true;
	}

	/**
	 * Retrieve content hashes.
	 *
	 * @return array<string, string>
	 */
	public static function get_hashes(): array {
		$hashes = get_option( self::OPTION_HASHES, array() );
		return is_array( $hashes ) ? $hashes : array();
	}

	/**
	 * Save content hashes.
	 *
	 * @param array<string, string> $hashes Hashes dictionary.
	 */
	public static function save_hashes( array $hashes ): bool {
		return update_option( self::OPTION_HASHES, $hashes, false );
	}
}
