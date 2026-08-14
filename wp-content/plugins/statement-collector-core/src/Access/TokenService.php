<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Handles single-use return links and unsubscribe tokens.
 */
final class TokenService {
	public const PURPOSE_ACCESS_RETURN = 'access_return';
	public const PURPOSE_MARKETING_UNSUBSCRIBE = 'marketing_unsubscribe';

	public const RESEND_COOLDOWN_SECONDS = 900; // 15 minutes
	public const MAX_ACCESS_RETURN_LIFETIME = 86400; // 24 hours
	public const MAX_UNSUBSCRIBE_LIFETIME = 31536000; // 365 days

	/**
	 * Hashes raw token using sha256.
	 */
	public static function hash_token( string $raw_token ): string {
		return hash( 'sha256', $raw_token );
	}

	/**
	 * Generates a cryptographically random raw token.
	 */
	public static function generate_raw_token(): string {
		return bin2hex( random_bytes( 32 ) );
	}

	/**
	 * Creates a new single-use token.
	 *
	 * @return string Raw token
	 */
	public static function create_token(
		$wpdb,
		string $purpose,
		?int $grant_id,
		?string $subject_email_hash,
		int $expires_at_ts,
		int $now_ts
	): ?string {
		if ( ! isset( $wpdb ) ) {
			return null;
		}

		$raw_token = self::generate_raw_token();
		$token_hash = self::hash_token( $raw_token );
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$exp_str = date( 'Y-m-d H:i:s', $expires_at_ts );
		$table = $wpdb->prefix . 'statement_access_tokens';

		// If purpose is access_return, revoke prior unused access_return tokens for this grant
		if ( self::PURPOSE_ACCESS_RETURN === $purpose && null !== $grant_id ) {
			$wpdb->update(
				$table,
				array( 'revoked_at' => $now_str ),
				array(
					'grant_id'    => $grant_id,
					'purpose'     => self::PURPOSE_ACCESS_RETURN,
					'consumed_at' => null,
					'revoked_at'  => null,
				)
			);
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'grant_id'           => $grant_id,
				'subject_email_hash' => $subject_email_hash,
				'purpose'            => $purpose,
				'token_hash'         => $token_hash,
				'issued_at'          => $now_str,
				'expires_at'         => $exp_str,
			)
		);

		return $inserted ? $raw_token : null;
	}

	/**
	 * Consumes a token if valid, unused, non-revoked, and unexpired.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function consume_token( $wpdb, string $raw_token, string $purpose, int $now_ts ): ?array {
		if ( ! isset( $wpdb ) || '' === trim( $raw_token ) ) {
			return null;
		}

		$token_hash = self::hash_token( $raw_token );
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$table = $wpdb->prefix . 'statement_access_tokens';

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE token_hash = %s AND purpose = %s AND consumed_at IS NULL AND revoked_at IS NULL AND expires_at > %s LIMIT 1",
				$token_hash,
				$purpose,
				$now_str
			),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return null;
		}

		$wpdb->update(
			$table,
			array( 'consumed_at' => $now_str ),
			array( 'id' => $row['id'] )
		);

		$row['consumed_at'] = $now_str;
		return $row;
	}
}
