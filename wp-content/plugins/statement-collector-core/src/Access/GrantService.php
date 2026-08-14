<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Handles creation, lookup, expiry calculations, revocation, and re-granting for Private Access grants.
 */
final class GrantService {
	/**
	 * Calculates immutable grant expiry given granted timestamp, configured duration seconds, and drop close timestamp.
	 */
	public static function calculate_grant_expiry( int $granted_at_ts, int $duration_seconds, int $drop_close_ts ): int {
		$indiv_expires = $granted_at_ts + $duration_seconds;
		return min( $indiv_expires, $drop_close_ts );
	}

	/**
	 * Calculates effective runtime authorization expiry.
	 */
	public static function calculate_effective_expiry( int $grant_expires_at_ts, int $current_drop_close_ts ): int {
		return min( $grant_expires_at_ts, $current_drop_close_ts );
	}

	/**
	 * Finds active valid grant for a drop_term_id and email_hash.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function find_active_grant( $wpdb, int $drop_term_id, string $email_hash, int $now_ts ): ?array {
		if ( ! isset( $wpdb ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'statement_access_grants';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE drop_term_id = %d
				  AND email_hash = %s
				  AND revoked_at IS NULL
				  AND grant_expires_at > %s
				ORDER BY id DESC LIMIT 1",
				$drop_term_id,
				$email_hash,
				$now_str
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Creates or reuses a grant for public email submission.
	 * If an active grant already exists, returns it without extending expiry or modifying granted_at.
	 * If grant is expired or revoked, returns null (public resubmission cannot auto-grant).
	 *
	 * @return array{grant: array<string, mixed>, is_new: bool}|null
	 */
	public static function get_or_create_public_grant(
		$wpdb,
		int $drop_term_id,
		string $email_hash,
		array $encrypted_payload,
		int $duration_seconds,
		int $drop_close_ts,
		int $now_ts
	): ?array {
		if ( ! isset( $wpdb ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'statement_access_grants';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );

		// Check if active grant exists
		$existing = self::find_active_grant( $wpdb, $drop_term_id, $email_hash, $now_ts );
		if ( null !== $existing ) {
			return array(
				'grant'  => $existing,
				'is_new' => false,
			);
		}

		// Check if any previous grant exists for this email+Drop (expired or revoked)
		$previous = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, revoked_at, grant_expires_at FROM {$table} WHERE drop_term_id = %d AND email_hash = %s ORDER BY id DESC LIMIT 1",
				$drop_term_id,
				$email_hash
			),
			ARRAY_A
		);

		if ( is_array( $previous ) ) {
			// If naturally expired or admin revoked => public form cannot issue new grant automatically
			return null;
		}

		// First valid submission: create new grant
		$indiv_expires_ts = $now_ts + $duration_seconds;
		$grant_expires_ts = min( $indiv_expires_ts, $drop_close_ts );

		$data = array(
			'drop_term_id'           => $drop_term_id,
			'email_hash'             => $email_hash,
			'encrypted_email'        => wp_json_encode( $encrypted_payload ),
			'encryption_algo'        => $encrypted_payload['algo'] ?? 'xchacha20-poly1305',
			'key_version'            => $encrypted_payload['key_version'] ?? 'v1',
			'granted_at'             => $now_str,
			'individual_expires_at'  => date( 'Y-m-d H:i:s', $indiv_expires_ts ),
			'drop_close_at_issuance' => date( 'Y-m-d H:i:s', $drop_close_ts ),
			'grant_expires_at'       => date( 'Y-m-d H:i:s', $grant_expires_ts ),
			'source'                 => 'public',
			'created_at'             => $now_str,
			'updated_at'             => $now_str,
		);

		$inserted = $wpdb->insert( $table, $data );
		if ( ! $inserted ) {
			return null;
		}

		$data['id'] = $wpdb->insert_id;
		return array(
			'grant'  => $data,
			'is_new' => true,
		);
	}

	/**
	 * Admin re-grant: creates a new grant row referencing historical grant via supersedes_grant_id.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function create_admin_regrant(
		$wpdb,
		int $drop_term_id,
		string $email_hash,
		array $encrypted_payload,
		int $duration_seconds,
		int $drop_close_ts,
		int $supersedes_grant_id,
		int $now_ts
	): ?array {
		if ( ! isset( $wpdb ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'statement_access_grants';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$indiv_expires_ts = $now_ts + $duration_seconds;
		$grant_expires_ts = min( $indiv_expires_ts, $drop_close_ts );

		$data = array(
			'drop_term_id'           => $drop_term_id,
			'email_hash'             => $email_hash,
			'encrypted_email'        => wp_json_encode( $encrypted_payload ),
			'encryption_algo'        => $encrypted_payload['algo'] ?? 'xchacha20-poly1305',
			'key_version'            => $encrypted_payload['key_version'] ?? 'v1',
			'granted_at'             => $now_str,
			'individual_expires_at'  => date( 'Y-m-d H:i:s', $indiv_expires_ts ),
			'drop_close_at_issuance' => date( 'Y-m-d H:i:s', $drop_close_ts ),
			'grant_expires_at'       => date( 'Y-m-d H:i:s', $grant_expires_ts ),
			'source'                 => 'admin',
			'supersedes_grant_id'    => $supersedes_grant_id,
			'created_at'             => $now_str,
			'updated_at'             => $now_str,
		);

		$inserted = $wpdb->insert( $table, $data );
		if ( ! $inserted ) {
			return null;
		}

		$data['id'] = $wpdb->insert_id;
		return $data;
	}

	/**
	 * Revokes a grant and associated active sessions/tokens.
	 */
	public static function revoke_grant( $wpdb, int $grant_id, string $reason, int $now_ts ): bool {
		if ( ! isset( $wpdb ) ) {
			return false;
		}

		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$grants_table = $wpdb->prefix . 'statement_access_grants';
		$sessions_table = $wpdb->prefix . 'statement_access_sessions';
		$tokens_table = $wpdb->prefix . 'statement_access_tokens';

		$updated = $wpdb->update(
			$grants_table,
			array(
				'revoked_at'     => $now_str,
				'revoked_reason' => $reason,
				'updated_at'     => $now_str,
			),
			array( 'id' => $grant_id )
		);

		// Revoke all active sessions for grant
		$wpdb->update(
			$sessions_table,
			array( 'revoked_at' => $now_str ),
			array(
				'grant_id'   => $grant_id,
				'revoked_at' => null,
			)
		);

		// Revoke return tokens for grant
		$wpdb->update(
			$tokens_table,
			array( 'revoked_at' => $now_str ),
			array(
				'grant_id'   => $grant_id,
				'revoked_at' => null,
			)
		);

		return false !== $updated;
	}
}
