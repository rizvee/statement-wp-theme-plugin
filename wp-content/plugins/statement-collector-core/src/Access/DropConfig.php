<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Reads, validates, and manages taxonomy term meta for Statement Drop private access configurations.
 */
final class DropConfig {
	public const META_CLOSES_AT           = '_statement_private_access_closes_at';
	public const META_DURATION            = '_statement_private_access_duration';
	public const META_DURATION_UNIT       = '_statement_private_access_duration_unit';
	public const META_SEND_ACCESS_EMAIL   = '_statement_send_access_email';
	public const META_REMINDER_ENABLED    = '_statement_reminder_enabled';
	public const META_REMINDER_DELAY      = '_statement_reminder_delay';
	public const META_REMINDER_DELAY_UNIT = '_statement_reminder_delay_unit';

	public const ALLOWED_UNITS = array( 'minutes', 'hours', 'days' );

	/**
	 * Converts duration + unit to seconds.
	 */
	public static function convert_to_seconds( int $duration, string $unit ): int {
		if ( $duration <= 0 || ! in_array( $unit, self::ALLOWED_UNITS, true ) ) {
			return 0;
		}

		switch ( $unit ) {
			case 'minutes':
				return $duration * 60;
			case 'hours':
				return $duration * 3600;
			case 'days':
				return $duration * 86400;
			default:
				return 0;
		}
	}

	/**
	 * Gets parsed config array for a drop term ID.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function get_config( int $term_id ): ?array {
		if ( $term_id <= 0 ) {
			return null;
		}

		$closes_at           = get_term_meta( $term_id, self::META_CLOSES_AT, true );
		$duration            = (int) get_term_meta( $term_id, self::META_DURATION, true );
		$duration_unit       = (string) get_term_meta( $term_id, self::META_DURATION_UNIT, true );
		$send_access_email   = (string) get_term_meta( $term_id, self::META_SEND_ACCESS_EMAIL, true );
		$reminder_enabled    = (string) get_term_meta( $term_id, self::META_REMINDER_ENABLED, true );
		$reminder_delay      = (int) get_term_meta( $term_id, self::META_REMINDER_DELAY, true );
		$reminder_delay_unit = (string) get_term_meta( $term_id, self::META_REMINDER_DELAY_UNIT, true );

		$closes_at_ts = ! empty( $closes_at ) ? strtotime( (string) $closes_at . ' UTC' ) : 0;
		if ( false === $closes_at_ts ) {
			$closes_at_ts = 0;
		}

		return array(
			'closes_at'              => (string) $closes_at,
			'closes_at_ts'           => $closes_at_ts,
			'duration'               => $duration,
			'duration_unit'          => $duration_unit,
			'duration_seconds'       => self::convert_to_seconds( $duration, $duration_unit ),
			'send_access_email'      => 'no' === $send_access_email ? 'no' : 'yes',
			'reminder_enabled'       => 'yes' === $reminder_enabled ? 'yes' : 'no',
			'reminder_delay'         => $reminder_delay,
			'reminder_delay_unit'    => $reminder_delay_unit,
			'reminder_delay_seconds' => self::convert_to_seconds( $reminder_delay, $reminder_delay_unit ),
		);
	}

	/**
	 * Validates drop configuration completeness and invariants.
	 */
	public static function is_config_valid( array $config, int $now_ts ): bool {
		if ( empty( $config['closes_at_ts'] ) || $config['closes_at_ts'] <= $now_ts ) {
			return false;
		}

		if ( empty( $config['duration'] ) || $config['duration'] <= 0 || ! in_array( $config['duration_unit'] ?? '', self::ALLOWED_UNITS, true ) ) {
			return false;
		}

		if ( ! empty( $config['reminder_enabled'] ) && 'yes' === $config['reminder_enabled'] ) {
			if ( empty( $config['reminder_delay'] ) || $config['reminder_delay'] <= 0 || ! in_array( $config['reminder_delay_unit'] ?? '', self::ALLOWED_UNITS, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Persists validated drop configuration to term meta.
	 *
	 * @param int $term_id Statement Drop term ID.
	 * @param array<string, mixed> $config Configuration inputs.
	 * @return bool True on valid persistence, false on invalid input or persistence failure.
	 */
	public static function save_config( int $term_id, array $config ): bool {
		if ( $term_id <= 0 ) {
			return false;
		}

		// 1. Validate and normalize closes_at
		if ( empty( $config['closes_at'] ) ) {
			return false;
		}

		$raw_closes = $config['closes_at'];
		$closes_ts  = 0;
		if ( is_numeric( $raw_closes ) ) {
			$closes_ts = (int) $raw_closes;
		} elseif ( is_string( $raw_closes ) ) {
			$trimmed = trim( $raw_closes );
			if ( preg_match( '/[Zz]|[+-]\d{2}:?\d{2}$/', $trimmed ) ) {
				$closes_ts = strtotime( $trimmed );
			} else {
				$closes_ts = strtotime( $trimmed . ' UTC' );
			}
		}

		if ( false === $closes_ts || $closes_ts <= 0 ) {
			return false;
		}

		$formatted_closes_at = gmdate( 'Y-m-d H:i:s', $closes_ts );

		// 2. Validate duration & unit
		$duration = isset( $config['duration'] ) ? (int) $config['duration'] : 0;
		if ( $duration <= 0 ) {
			return false;
		}

		$duration_unit = isset( $config['duration_unit'] ) ? (string) $config['duration_unit'] : '';
		if ( ! in_array( $duration_unit, self::ALLOWED_UNITS, true ) ) {
			return false;
		}

		// 3. Validate flags
		$send_email_raw = $config['send_access_email'] ?? 'yes';
		$send_email     = ( false === $send_email_raw || 'no' === $send_email_raw || 0 === $send_email_raw || '0' === $send_email_raw ) ? 'no' : 'yes';

		$reminder_raw = $config['reminder_enabled'] ?? 'no';
		$reminder_on  = ( true === $reminder_raw || 'yes' === $reminder_raw || 1 === $reminder_raw || '1' === $reminder_raw ) ? 'yes' : 'no';

		// 4. Validate reminder delay & unit
		$reminder_delay      = isset( $config['reminder_delay'] ) ? (int) $config['reminder_delay'] : 0;
		$reminder_delay_unit = isset( $config['reminder_delay_unit'] ) ? (string) $config['reminder_delay_unit'] : 'hours';

		if ( 'yes' === $reminder_on ) {
			if ( $reminder_delay <= 0 || ! in_array( $reminder_delay_unit, self::ALLOWED_UNITS, true ) ) {
				return false;
			}
		} else {
			if ( ! in_array( $reminder_delay_unit, self::ALLOWED_UNITS, true ) ) {
				$reminder_delay_unit = 'hours';
			}
			if ( $reminder_delay < 0 ) {
				$reminder_delay = 0;
			}
		}

		// Snapshot previous values for atomic rollback if needed
		$previous_meta = array(
			self::META_CLOSES_AT           => get_term_meta( $term_id, self::META_CLOSES_AT, true ),
			self::META_DURATION            => get_term_meta( $term_id, self::META_DURATION, true ),
			self::META_DURATION_UNIT       => get_term_meta( $term_id, self::META_DURATION_UNIT, true ),
			self::META_SEND_ACCESS_EMAIL   => get_term_meta( $term_id, self::META_SEND_ACCESS_EMAIL, true ),
			self::META_REMINDER_ENABLED    => get_term_meta( $term_id, self::META_REMINDER_ENABLED, true ),
			self::META_REMINDER_DELAY      => get_term_meta( $term_id, self::META_REMINDER_DELAY, true ),
			self::META_REMINDER_DELAY_UNIT => get_term_meta( $term_id, self::META_REMINDER_DELAY_UNIT, true ),
		);

		update_term_meta( $term_id, self::META_CLOSES_AT, $formatted_closes_at );
		update_term_meta( $term_id, self::META_DURATION, $duration );
		update_term_meta( $term_id, self::META_DURATION_UNIT, $duration_unit );
		update_term_meta( $term_id, self::META_SEND_ACCESS_EMAIL, $send_email );
		update_term_meta( $term_id, self::META_REMINDER_ENABLED, $reminder_on );
		update_term_meta( $term_id, self::META_REMINDER_DELAY, $reminder_delay );
		update_term_meta( $term_id, self::META_REMINDER_DELAY_UNIT, $reminder_delay_unit );

		// Read back and verify exact values
		$saved = self::get_config( $term_id );
		if (
			null === $saved ||
			$saved['closes_at'] !== $formatted_closes_at ||
			$saved['duration'] !== $duration ||
			$saved['duration_unit'] !== $duration_unit ||
			$saved['send_access_email'] !== $send_email ||
			$saved['reminder_enabled'] !== $reminder_on ||
			$saved['reminder_delay'] !== $reminder_delay ||
			$saved['reminder_delay_unit'] !== $reminder_delay_unit
		) {
			// Rollback to previous snapshot
			foreach ( $previous_meta as $k => $v ) {
				if ( '' === $v || false === $v ) {
					delete_term_meta( $term_id, $k );
				} else {
					update_term_meta( $term_id, $k, $v );
				}
			}
			return false;
		}

		return true;
	}
}
