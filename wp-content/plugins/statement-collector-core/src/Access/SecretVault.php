<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Manages encrypted secret-vault fallback stored in WordPress options when wp-config constants are unavailable.
 */
final class SecretVault {
	public const OPTION_NAME    = 'statement_access_secret_vault_v1';
	public const PURPOSE_STRING = 'statement-access-secret-vault-v1';

	/**
	 * Derives root wrapping key from WordPress auth salt.
	 */
	public static function get_wrapping_key(): ?string {
		$salt = function_exists( 'wp_salt' ) ? wp_salt( 'auth' ) : null;
		if ( null === $salt || '' === trim( (string) $salt ) ) {
			return null;
		}

		return hash_hmac( 'sha256', self::PURPOSE_STRING, (string) $salt );
	}

	/**
	 * Checks if the vault option is initialized in the database.
	 */
	public static function is_initialized(): bool {
		$val = get_option( self::OPTION_NAME, null );
		return null !== $val && false !== $val && is_array( $val );
	}

	/**
	 * Encrypts secret bundle using derived wrapping key and available AEAD backend.
	 *
	 * @param array{identity_key: string, rate_limit_key: string, encryption_active_version: string, encryption_keys: array<string, string>} $bundle
	 * @return array{schema_version: int, algo: string, nonce: string, ciphertext: string, tag?: string, created_at: string}|null
	 */
	public static function encrypt_bundle( array $bundle ): ?array {
		$wrapping_key = self::get_wrapping_key();
		if ( null === $wrapping_key || '' === $wrapping_key ) {
			return null;
		}

		$plaintext = json_encode( $bundle );
		if ( false === $plaintext ) {
			return null;
		}

		$key = hash( 'sha256', $wrapping_key, true );

		if ( function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' ) ) {
			$nonce = random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES );
			$ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
				$plaintext,
				self::PURPOSE_STRING,
				$nonce,
				$key
			);

			return array(
				'schema_version' => 1,
				'algo'           => 'xchacha20-poly1305',
				'nonce'          => base64_encode( $nonce ),
				'ciphertext'     => base64_encode( $ciphertext ),
				'created_at'     => gmdate( 'c' ),
			);
		}

		if ( function_exists( 'openssl_encrypt' ) ) {
			$iv = random_bytes( 12 );
			$tag = '';
			$ciphertext = openssl_encrypt(
				$plaintext,
				'aes-256-gcm',
				$key,
				OPENSSL_RAW_DATA,
				$iv,
				$tag,
				self::PURPOSE_STRING
			);

			if ( false !== $ciphertext && '' !== $tag ) {
				return array(
					'schema_version' => 1,
					'algo'           => 'aes-256-gcm',
					'nonce'          => base64_encode( $iv ),
					'ciphertext'     => base64_encode( $ciphertext ),
					'tag'            => base64_encode( $tag ),
					'created_at'     => gmdate( 'c' ),
				);
			}
		}

		return null;
	}

	/**
	 * Decrypts and validates encrypted secret bundle from option.
	 *
	 * @return array{identity_key: string, rate_limit_key: string, encryption_active_version: string, encryption_keys: array<string, string>}|null
	 */
	public static function decrypt_bundle(): ?array {
		$payload = get_option( self::OPTION_NAME, null );
		if ( ! is_array( $payload ) || empty( $payload['ciphertext'] ) || empty( $payload['nonce'] ) || empty( $payload['algo'] ) ) {
			return null;
		}

		$wrapping_key = self::get_wrapping_key();
		if ( null === $wrapping_key || '' === $wrapping_key ) {
			return null;
		}

		$ciphertext = base64_decode( (string) $payload['ciphertext'], true );
		$nonce_raw = base64_decode( (string) $payload['nonce'], true );
		$algo = (string) $payload['algo'];

		if ( false === $ciphertext || false === $nonce_raw ) {
			return null;
		}

		$key = hash( 'sha256', $wrapping_key, true );
		$plaintext = null;

		if ( 'xchacha20-poly1305' === $algo && function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_decrypt' ) ) {
			if ( strlen( $nonce_raw ) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES ) {
				return null;
			}
			$decrypted = @sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
				$ciphertext,
				self::PURPOSE_STRING,
				$nonce_raw,
				$key
			);
			if ( false !== $decrypted ) {
				$plaintext = $decrypted;
			}
		} elseif ( 'aes-256-gcm' === $algo && function_exists( 'openssl_decrypt' ) ) {
			$iv = substr( $nonce_raw, 0, 12 );
			$tag = isset( $payload['tag'] ) ? base64_decode( (string) $payload['tag'], true ) : '';
			if ( false !== $tag && '' !== $tag ) {
				$decrypted = @openssl_decrypt(
					$ciphertext,
					'aes-256-gcm',
					$key,
					OPENSSL_RAW_DATA,
					$iv,
					$tag,
					self::PURPOSE_STRING
				);
				if ( false !== $decrypted ) {
					$plaintext = $decrypted;
				}
			}
		}

		if ( null === $plaintext ) {
			return null;
		}

		$bundle = json_decode( $plaintext, true );
		return self::validate_bundle( $bundle ) ? $bundle : null;
	}

	/**
	 * Validates structure and hex string formats of a decrypted secret bundle.
	 *
	 * @param mixed $bundle
	 */
	public static function validate_bundle( $bundle ): bool {
		if ( ! is_array( $bundle ) ) {
			return false;
		}

		if ( empty( $bundle['identity_key'] ) || ! is_string( $bundle['identity_key'] ) || 64 !== strlen( $bundle['identity_key'] ) || ! ctype_xdigit( $bundle['identity_key'] ) ) {
			return false;
		}

		if ( empty( $bundle['rate_limit_key'] ) || ! is_string( $bundle['rate_limit_key'] ) || 64 !== strlen( $bundle['rate_limit_key'] ) || ! ctype_xdigit( $bundle['rate_limit_key'] ) ) {
			return false;
		}

		if ( empty( $bundle['encryption_active_version'] ) || ! is_string( $bundle['encryption_active_version'] ) ) {
			return false;
		}

		if ( empty( $bundle['encryption_keys'] ) || ! is_array( $bundle['encryption_keys'] ) ) {
			return false;
		}

		$active_ver = $bundle['encryption_active_version'];
		if ( empty( $bundle['encryption_keys'][ $active_ver ] ) || ! is_string( $bundle['encryption_keys'][ $active_ver ] ) || 64 !== strlen( $bundle['encryption_keys'][ $active_ver ] ) || ! ctype_xdigit( $bundle['encryption_keys'][ $active_ver ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generates new cryptographically secure secret bundle and saves encrypted payload to option.
	 *
	 * @param array|null $custom_bundle Optional explicit bundle override for testing.
	 */
	public static function create_vault( ?array $custom_bundle = null ): bool {
		if ( null !== $custom_bundle ) {
			$bundle = $custom_bundle;
		} else {
			$bundle = array(
				'identity_key'               => bin2hex( random_bytes( 32 ) ),
				'rate_limit_key'            => bin2hex( random_bytes( 32 ) ),
				'encryption_active_version' => 'v1',
				'encryption_keys'           => array(
					'v1' => bin2hex( random_bytes( 32 ) ),
				),
			);
		}

		if ( ! self::validate_bundle( $bundle ) ) {
			return false;
		}

		$encrypted = self::encrypt_bundle( $bundle );
		if ( null === $encrypted ) {
			return false;
		}

		// Ensure autoload = false ('no')
		$saved = update_option( self::OPTION_NAME, $encrypted, 'no' );
		if ( ! $saved && get_option( self::OPTION_NAME, null ) === null ) {
			$saved = add_option( self::OPTION_NAME, $encrypted, '', 'no' );
		}

		// Round-trip verification
		Secrets::reset_cache();
		$decrypted = self::decrypt_bundle();
		return null !== $decrypted && $decrypted['identity_key'] === $bundle['identity_key'];
	}

	/**
	 * Deletes vault option.
	 */
	public static function delete_vault(): bool {
		Secrets::reset_cache();
		return delete_option( self::OPTION_NAME );
	}
}
