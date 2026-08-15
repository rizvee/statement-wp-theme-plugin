<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Accesses and validates Private Access secrets via wp-config or encrypted secret vault fallback.
 */
final class Secrets {
	/**
	 * Static cache for decrypted secret vault bundle during a single request.
	 *
	 * @var array{identity_key: string, rate_limit_key: string, encryption_active_version: string, encryption_keys: array<string, string>}|null
	 */
	private static ?array $cached_vault_bundle = null;

	/**
	 * Resets static request cache.
	 */
	public static function reset_cache(): void {
		self::$cached_vault_bundle = null;
	}

	/**
	 * Evaluates current provider precedence and status.
	 *
	 * Providers:
	 * - 'wp_config': All four wp-config constants defined and valid.
	 * - 'encrypted_vault': wp-config absent, valid encrypted vault option exists.
	 * - 'invalid_wp_config': Partial or invalid wp-config constants defined (fail closed).
	 * - 'unavailable': Neither valid wp-config nor valid vault exists.
	 */
	public static function get_provider(): string {
		$has_id_const     = defined( 'STATEMENT_ACCESS_IDENTITY_KEY' );
		$has_rate_const   = defined( 'STATEMENT_ACCESS_RATE_LIMIT_KEY' );
		$has_ver_const    = defined( 'STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION' );
		$has_keys_const   = defined( 'STATEMENT_ACCESS_ENCRYPTION_KEYS' );

		$any_const_defined = $has_id_const || $has_rate_const || $has_ver_const || $has_keys_const;
		$all_const_defined = $has_id_const && $has_rate_const && $has_ver_const && $has_keys_const;

		if ( $any_const_defined ) {
			if ( ! $all_const_defined ) {
				return 'invalid_wp_config';
			}

			// Validate all 4 constants
			$id   = trim( (string) STATEMENT_ACCESS_IDENTITY_KEY );
			$rate = trim( (string) STATEMENT_ACCESS_RATE_LIMIT_KEY );
			$ver  = trim( (string) STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION );
			$keys = self::parse_wp_config_keys();

			if ( '' === $id || '' === $rate || '' === $ver || empty( $keys[ $ver ] ) || '' === trim( (string) $keys[ $ver ] ) ) {
				return 'invalid_wp_config';
			}

			return 'wp_config';
		}

		// Check encrypted vault fallback
		$vault = self::get_vault_bundle();
		if ( null !== $vault ) {
			return 'encrypted_vault';
		}

		return 'unavailable';
	}

	/**
	 * Checks if Private Access secrets are fully configured via an active provider.
	 */
	public static function is_configured(): bool {
		$provider = self::get_provider();
		return 'wp_config' === $provider || 'encrypted_vault' === $provider;
	}

	/**
	 * Helper to get vault bundle with in-request caching.
	 *
	 * @return array{identity_key: string, rate_limit_key: string, encryption_active_version: string, encryption_keys: array<string, string>}|null
	 */
	private static function get_vault_bundle(): ?array {
		if ( null !== self::$cached_vault_bundle ) {
			return self::$cached_vault_bundle;
		}

		if ( class_exists( SecretVault::class ) ) {
			$bundle = SecretVault::decrypt_bundle();
			if ( null !== $bundle ) {
				self::$cached_vault_bundle = $bundle;
				return $bundle;
			}
		}

		return null;
	}

	/**
	 * Parses wp-config STATEMENT_ACCESS_ENCRYPTION_KEYS array or JSON string.
	 *
	 * @return array<string, string>
	 */
	private static function parse_wp_config_keys(): array {
		if ( ! defined( 'STATEMENT_ACCESS_ENCRYPTION_KEYS' ) ) {
			return array();
		}

		$val = STATEMENT_ACCESS_ENCRYPTION_KEYS;
		if ( is_array( $val ) ) {
			return $val;
		}

		if ( is_string( $val ) ) {
			$decoded = json_decode( $val, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		return array();
	}

	/**
	 * Checks if the email identity HMAC key is configured.
	 */
	public static function has_identity_key(): bool {
		return '' !== self::get_identity_key();
	}

	/**
	 * Gets the email identity HMAC key.
	 */
	public static function get_identity_key(): string {
		$provider = self::get_provider();

		if ( 'wp_config' === $provider ) {
			return (string) STATEMENT_ACCESS_IDENTITY_KEY;
		}

		if ( 'encrypted_vault' === $provider ) {
			$bundle = self::get_vault_bundle();
			return $bundle['identity_key'] ?? '';
		}

		return '';
	}

	/**
	 * Checks if the IP rate limit HMAC key is configured.
	 */
	public static function has_rate_limit_key(): bool {
		return '' !== self::get_rate_limit_key();
	}

	/**
	 * Gets the IP rate limit HMAC key.
	 */
	public static function get_rate_limit_key(): string {
		$provider = self::get_provider();

		if ( 'wp_config' === $provider ) {
			return (string) STATEMENT_ACCESS_RATE_LIMIT_KEY;
		}

		if ( 'encrypted_vault' === $provider ) {
			$bundle = self::get_vault_bundle();
			return $bundle['rate_limit_key'] ?? '';
		}

		return '';
	}

	/**
	 * Checks if encryption keyring and active key version are configured.
	 */
	public static function has_encryption_config(): bool {
		$version = self::get_active_key_version();
		if ( '' === $version ) {
			return false;
		}

		$key = self::get_encryption_key( $version );
		return null !== $key && '' !== trim( $key );
	}

	/**
	 * Gets active key version.
	 */
	public static function get_active_key_version(): string {
		$provider = self::get_provider();

		if ( 'wp_config' === $provider ) {
			return trim( (string) STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION );
		}

		if ( 'encrypted_vault' === $provider ) {
			$bundle = self::get_vault_bundle();
			return $bundle['encryption_active_version'] ?? '';
		}

		return '';
	}

	/**
	 * Gets the keyring array of version => secret key.
	 *
	 * @return array<string, string>
	 */
	public static function get_encryption_keys(): array {
		$provider = self::get_provider();

		if ( 'wp_config' === $provider ) {
			return self::parse_wp_config_keys();
		}

		if ( 'encrypted_vault' === $provider ) {
			$bundle = self::get_vault_bundle();
			return $bundle['encryption_keys'] ?? array();
		}

		return array();
	}

	/**
	 * Gets key string by version.
	 */
	public static function get_encryption_key( string $version ): ?string {
		$keys = self::get_encryption_keys();
		return isset( $keys[ $version ] ) ? (string) $keys[ $version ] : null;
	}
}
