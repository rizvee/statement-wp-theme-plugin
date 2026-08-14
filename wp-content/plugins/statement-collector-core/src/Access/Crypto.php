<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Handles normalization, HMAC hashing, authenticated encryption/decryption.
 */
final class Crypto {
	/**
	 * Normalizes email address (lowercase, trim).
	 */
	public static function normalize_email( string $email ): string {
		return strtolower( trim( $email ) );
	}

	/**
	 * Hashes normalized email with identity HMAC key.
	 */
	public static function hash_email( string $email ): ?string {
		$key = Secrets::get_identity_key();
		if ( '' === $key ) {
			return null;
		}

		$normalized = self::normalize_email( $email );
		if ( '' === $normalized ) {
			return null;
		}

		return hash_hmac( 'sha256', $normalized, $key );
	}

	/**
	 * Hashes IP address with rate limit HMAC key.
	 */
	public static function hash_ip( string $ip ): ?string {
		$key = Secrets::get_rate_limit_key();
		if ( '' === $key ) {
			return null;
		}

		$normalized = trim( $ip );
		if ( '' === $normalized ) {
			return null;
		}

		return hash_hmac( 'sha256', $normalized, $key );
	}

	/**
	 * Encrypts email using active key version with authenticated encryption.
	 *
	 * @return array{ciphertext: string, algo: string, key_version: string, nonce: string}|null
	 */
	public static function encrypt_email( string $email ): ?array {
		$version = Secrets::get_active_key_version();
		if ( '' === $version ) {
			return null;
		}

		return self::encrypt_email_with_version( $email, $version );
	}

	/**
	 * Encrypts email using specified key version.
	 *
	 * @return array{ciphertext: string, algo: string, key_version: string, nonce: string}|null
	 */
	public static function encrypt_email_with_version( string $email, string $key_version ): ?array {
		$raw_key = Secrets::get_encryption_key( $key_version );
		if ( null === $raw_key || '' === $raw_key ) {
			return null;
		}

		$normalized = self::normalize_email( $email );
		if ( '' === $normalized ) {
			return null;
		}

		// Use sodium XChaCha20-Poly1305 if available, otherwise AES-256-GCM fallback
		if ( function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' ) ) {
			$key = hash( 'sha256', $raw_key, true );
			$nonce = random_bytes( SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES );
			$ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
				$normalized,
				'',
				$nonce,
				$key
			);

			return array(
				'ciphertext'  => base64_encode( $ciphertext ),
				'algo'        => 'xchacha20-poly1305',
				'key_version' => $key_version,
				'nonce'       => base64_encode( $nonce ),
			);
		}

		if ( function_exists( 'openssl_encrypt' ) ) {
			$key = hash( 'sha256', $raw_key, true );
			$iv = random_bytes( 12 );
			$tag = '';
			$ciphertext = openssl_encrypt(
				$normalized,
				'aes-256-gcm',
				$key,
				OPENSSL_RAW_DATA,
				$iv,
				$tag
			);

			if ( false !== $ciphertext && '' !== $tag ) {
				return array(
					'ciphertext'  => base64_encode( $ciphertext ),
					'algo'        => 'aes-256-gcm',
					'key_version' => $key_version,
					'nonce'       => base64_encode( $iv ),
					'tag'         => base64_encode( $tag ),
				);
			}
		}

		// If neither standard AEAD backend is available, fail closed.
		return null;
	}

	/**
	 * Decrypts encrypted email metadata payload.
	 *
	 * @param array|string $payload
	 */
	public static function decrypt_email( $payload ): ?string {
		if ( is_string( $payload ) ) {
			$decoded = json_decode( $payload, true );
			if ( is_array( $decoded ) ) {
				$payload = $decoded;
			}
		}

		if ( ! is_array( $payload ) || empty( $payload['ciphertext'] ) || empty( $payload['key_version'] ) || empty( $payload['nonce'] ) ) {
			return null;
		}

		$key_version = (string) $payload['key_version'];
		$raw_key = Secrets::get_encryption_key( $key_version );
		if ( null === $raw_key || '' === $raw_key ) {
			return null;
		}

		$ciphertext = base64_decode( (string) $payload['ciphertext'], true );
		$nonce_raw = base64_decode( (string) $payload['nonce'], true );
		$algo = isset( $payload['algo'] ) ? (string) $payload['algo'] : 'xchacha20-poly1305';

		if ( false === $ciphertext || false === $nonce_raw ) {
			return null;
		}

		$key = hash( 'sha256', $raw_key, true );

		if ( 'xchacha20-poly1305' === $algo && function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_decrypt' ) ) {
			if ( strlen( $nonce_raw ) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES ) {
				return null;
			}
			$decrypted = @sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
				$ciphertext,
				'',
				$nonce_raw,
				$key
			);
			return false !== $decrypted ? $decrypted : null;
		}

		if ( 'aes-256-gcm' === $algo && function_exists( 'openssl_decrypt' ) ) {
			$iv = substr( $nonce_raw, 0, 12 );
			$tag = isset( $payload['tag'] ) ? base64_decode( (string) $payload['tag'], true ) : substr( $nonce_raw, 12 );
			if ( false === $tag || '' === $tag ) {
				return null;
			}

			$decrypted = @openssl_decrypt(
				$ciphertext,
				'aes-256-gcm',
				$key,
				OPENSSL_RAW_DATA,
				$iv,
				$tag
			);
			return false !== $decrypted ? $decrypted : null;
		}

		return null;
	}
}
