<?php

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

final class ManifestService {

	public const OPTION_MANIFEST = 'statement_client_demo_manifest_v1';
	public const OPTION_ROLLBACK = 'statement_client_demo_rollback';

	/**
	 * Retrieve the current manifest from options.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_manifest(): array {
		$manifest = get_option( self::OPTION_MANIFEST, array() );
		return is_array( $manifest ) ? $manifest : array();
	}

	/**
	 * Save updated manifest.
	 *
	 * @param array<string, mixed> $data Manifest data.
	 */
	public static function save_manifest( array $data ): bool {
		return update_option( self::OPTION_MANIFEST, $data, false );
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
}
