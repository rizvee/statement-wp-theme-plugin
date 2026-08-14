<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Handles IP and email rate limiting for public access submissions.
 */
final class RateLimiter {
	public const IP_SHORT_LIMIT = 5;      // 5 attempts per 10 min
	public const IP_LONG_LIMIT = 20;      // 20 attempts per 24 hrs
	public const EMAIL_SHORT_LIMIT = 3;   // 3 attempts per 10 min
	public const EMAIL_LONG_LIMIT = 10;   // 10 attempts per 24 hrs

	public const SHORT_WINDOW_SECONDS = 600;   // 10 mins
	public const LONG_WINDOW_SECONDS = 86400;  // 24 hrs

	/**
	 * Checks whether IP or email is currently rate-limited.
	 *
	 * @return bool True if allowed, false if rate-limited.
	 */
	public static function is_allowed( $wpdb, int $drop_term_id, string $ip_hash, string $email_hash, int $now_ts ): bool {
		if ( ! isset( $wpdb ) ) {
			return true;
		}

		$table = $wpdb->prefix . 'statement_access_rate_limits';
		$short_cutoff_str = date( 'Y-m-d H:i:s', $now_ts - self::SHORT_WINDOW_SECONDS );
		$long_cutoff_str  = date( 'Y-m-d H:i:s', $now_ts - self::LONG_WINDOW_SECONDS );

		// IP short window
		$ip_short_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE drop_term_id = %d AND scope_type = 'ip' AND scope_hash = %s AND attempted_at >= %s",
				$drop_term_id,
				$ip_hash,
				$short_cutoff_str
			)
		);
		if ( $ip_short_count >= self::IP_SHORT_LIMIT ) {
			return false;
		}

		// IP long window
		$ip_long_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE drop_term_id = %d AND scope_type = 'ip' AND scope_hash = %s AND attempted_at >= %s",
				$drop_term_id,
				$ip_hash,
				$long_cutoff_str
			)
		);
		if ( $ip_long_count >= self::IP_LONG_LIMIT ) {
			return false;
		}

		// Email short window
		$email_short_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE drop_term_id = %d AND scope_type = 'email' AND scope_hash = %s AND attempted_at >= %s",
				$drop_term_id,
				$email_hash,
				$short_cutoff_str
			)
		);
		if ( $email_short_count >= self::EMAIL_SHORT_LIMIT ) {
			return false;
		}

		// Email long window
		$email_long_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE drop_term_id = %d AND scope_type = 'email' AND scope_hash = %s AND attempted_at >= %s",
				$drop_term_id,
				$email_hash,
				$long_cutoff_str
			)
		);
		if ( $email_long_count >= self::EMAIL_LONG_LIMIT ) {
			return false;
		}

		return true;
	}

	/**
	 * Records an attempt for both IP and email scopes.
	 */
	public static function record_attempt( $wpdb, int $drop_term_id, string $ip_hash, string $email_hash, int $now_ts ): void {
		if ( ! isset( $wpdb ) ) {
			return;
		}

		$table = $wpdb->prefix . 'statement_access_rate_limits';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$exp_str = date( 'Y-m-d H:i:s', $now_ts + self::LONG_WINDOW_SECONDS );

		$wpdb->insert(
			$table,
			array(
				'drop_term_id' => $drop_term_id,
				'scope_type'   => 'ip',
				'scope_hash'   => $ip_hash,
				'attempted_at' => $now_str,
				'expires_at'   => $exp_str,
			)
		);

		$wpdb->insert(
			$table,
			array(
				'drop_term_id' => $drop_term_id,
				'scope_type'   => 'email',
				'scope_hash'   => $email_hash,
				'attempted_at' => $now_str,
				'expires_at'   => $exp_str,
			)
		);
	}
}
