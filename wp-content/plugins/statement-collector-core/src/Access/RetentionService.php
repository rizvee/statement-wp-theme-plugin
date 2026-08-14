<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Scheduled Action Scheduler retention job for rate-limits, expired tokens, sessions, and inactive grant anonymization.
 */
final class RetentionService {
	public const ACTION_HOOK = 'statement_privacy_retention_cleanup';

	public static function boot(): void {
		add_action( self::ACTION_HOOK, array( self::class, 'cleanup_expired_data' ) );

		if ( function_exists( 'as_has_scheduled_action' ) && ! as_has_scheduled_action( self::ACTION_HOOK ) ) {
			as_schedule_recurring_action( time(), 86400, self::ACTION_HOOK, array(), 'statement' );
		}
	}

	/**
	 * Per-day data retention cleanup task.
	 */
	public static function cleanup_expired_data(): void {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return;
		}

		$now_ts = time();
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$day_ago_str = date( 'Y-m-d H:i:s', $now_ts - 86400 );
		$thirty_days_ago_str = date( 'Y-m-d H:i:s', $now_ts - ( 30 * 86400 ) );
		$ninety_days_ago_str = date( 'Y-m-d H:i:s', $now_ts - ( 90 * 86400 ) );

		// 1. Delete expired rate limit attempt rows
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}statement_access_rate_limits WHERE expires_at < %s",
				$now_str
			)
		);

		// 2. Delete consumed or expired access_return tokens (> 24 hrs past expiry)
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}statement_access_tokens WHERE purpose = 'access_return' AND (consumed_at IS NOT NULL OR expires_at < %s)",
				$day_ago_str
			)
		);

		// 3. Delete expired or revoked sessions (> 30 days past expiry/revocation)
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}statement_access_sessions WHERE expires_at < %s OR (revoked_at IS NOT NULL AND revoked_at < %s)",
				$thirty_days_ago_str,
				$thirty_days_ago_str
			)
		);

		// 4. Anonymize inactive grants (> 90 days past expiry/revocation) while preserving non-PII audit skeleton
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}statement_access_grants
				SET encrypted_email = '', anonymized_at = %s
				WHERE anonymized_at IS NULL AND (grant_expires_at < %s OR (revoked_at IS NOT NULL AND revoked_at < %s))",
				$now_str,
				$ninety_days_ago_str,
				$ninety_days_ago_str
			)
		);
	}
}
