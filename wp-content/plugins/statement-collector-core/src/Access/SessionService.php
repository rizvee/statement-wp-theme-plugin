<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Handles browser session cookies, 5-active session limits, and session validation.
 */
final class SessionService {
	public const MAX_ACTIVE_SESSIONS = 5;
	public const COOKIE_PREFIX = 'statement_drop_access_';

	/**
	 * Gets cookie name for a specific drop_term_id.
	 */
	public static function get_cookie_name( int $drop_term_id ): string {
		return self::COOKIE_PREFIX . $drop_term_id;
	}

	/**
	 * Generates a cryptographically random raw session token.
	 */
	public static function generate_raw_token(): string {
		return bin2hex( random_bytes( 32 ) );
	}

	/**
	 * Hashes raw token using sha256.
	 */
	public static function hash_token( string $raw_token ): string {
		return hash( 'sha256', $raw_token );
	}

	/**
	 * Creates a new browser session for a grant, enforcing max 5 active sessions.
	 *
	 * @return string Raw session token
	 */
	public static function create_session( $wpdb, int $grant_id, int $drop_term_id, int $expires_at_ts, int $now_ts ): ?string {
		if ( ! isset( $wpdb ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'statement_access_sessions';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$exp_str = date( 'Y-m-d H:i:s', $expires_at_ts );

		// Enforce max 5 active sessions
		$active_sessions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE grant_id = %d AND revoked_at IS NULL AND expires_at > %s ORDER BY issued_at ASC",
				$grant_id,
				$now_str
			),
			ARRAY_A
		);

		if ( is_array( $active_sessions ) && count( $active_sessions ) >= self::MAX_ACTIVE_SESSIONS ) {
			// Revoke oldest active session(s) until count < 5
			$excess_count = ( count( $active_sessions ) - self::MAX_ACTIVE_SESSIONS ) + 1;
			for ( $i = 0; $i < $excess_count; $i++ ) {
				$oldest_id = $active_sessions[ $i ]['id'];
				$wpdb->update(
					$table,
					array( 'revoked_at' => $now_str ),
					array( 'id' => $oldest_id )
				);
			}
		}

		$raw_token = self::generate_raw_token();
		$token_hash = self::hash_token( $raw_token );

		$inserted = $wpdb->insert(
			$table,
			array(
				'grant_id'     => $grant_id,
				'drop_term_id' => $drop_term_id,
				'token_hash'   => $token_hash,
				'issued_at'    => $now_str,
				'expires_at'   => $exp_str,
			)
		);

		return $inserted ? $raw_token : null;
	}

	/**
	 * Sets HttpOnly, Secure, SameSite=Lax session cookie in browser.
	 */
	public static function set_session_cookie( int $drop_term_id, string $raw_token, int $expires_at_ts ): void {
		$cookie_name = self::get_cookie_name( $drop_term_id );
		$is_secure = is_ssl();

		if ( PHP_VERSION_ID >= 70300 ) {
			@setcookie(
				$cookie_name,
				$raw_token,
				array(
					'expires'  => $expires_at_ts,
					'path'     => '/',
					'secure'   => $is_secure,
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		} else {
			@setcookie(
				$cookie_name,
				$raw_token,
				$expires_at_ts,
				'/',
				'',
				$is_secure,
				true
			);
		}
	}

	/**
	 * Clears session cookie for drop_term_id.
	 */
	public static function clear_session_cookie( int $drop_term_id ): void {
		$cookie_name = self::get_cookie_name( $drop_term_id );
		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			@setcookie( $cookie_name, '', time() - 3600, '/' );
			unset( $_COOKIE[ $cookie_name ] );
		}
	}

	/**
	 * Validates session from raw token or cookie.
	 *
	 * @return array{session: array<string, mixed>, grant: array<string, mixed>}|null
	 */
	public static function validate_session( $wpdb, int $drop_term_id, string $raw_token, int $now_ts, int $current_drop_close_ts = 0 ): ?array {
		if ( ! isset( $wpdb ) || '' === trim( $raw_token ) ) {
			return null;
		}

		$token_hash = self::hash_token( $raw_token );
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$sessions_table = $wpdb->prefix . 'statement_access_sessions';
		$grants_table   = $wpdb->prefix . 'statement_access_grants';

		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$sessions_table} WHERE token_hash = %s AND drop_term_id = %d AND revoked_at IS NULL AND expires_at > %s LIMIT 1",
				$token_hash,
				$drop_term_id,
				$now_str
			),
			ARRAY_A
		);

		if ( ! is_array( $session ) ) {
			return null;
		}

		$grant = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$grants_table} WHERE id = %d AND drop_term_id = %d AND revoked_at IS NULL AND grant_expires_at > %s LIMIT 1",
				$session['grant_id'],
				$drop_term_id,
				$now_str
			),
			ARRAY_A
		);

		if ( ! is_array( $grant ) ) {
			return null;
		}

		// Check current drop close time if provided
		if ( $current_drop_close_ts > 0 && $now_ts >= $current_drop_close_ts ) {
			return null;
		}

		return array(
			'session' => $session,
			'grant'   => $grant,
		);
	}
}
