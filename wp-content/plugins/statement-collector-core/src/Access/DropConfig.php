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
			'closes_at'           => (string) $closes_at,
			'closes_at_ts'        => $closes_at_ts,
			'duration'            => $duration,
			'duration_unit'       => $duration_unit,
			'duration_seconds'    => self::convert_to_seconds( $duration, $duration_unit ),
			'send_access_email'   => 'no' === $send_access_email ? 'no' : 'yes',
			'reminder_enabled'    => 'yes' === $reminder_enabled ? 'yes' : 'no',
			'reminder_delay'      => $reminder_delay,
			'reminder_delay_unit' => $reminder_delay_unit,
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

		return true;
	}
}
