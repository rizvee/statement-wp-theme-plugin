<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Manages global marketing consent audit events and withdrawal.
 */
final class ConsentService {
	public const CONSENT_VERSION = '1.0';
	public const EVENT_GRANTED = 'consent_granted';
	public const EVENT_WITHDRAWN = 'consent_withdrawn';

	public const DEFAULT_CONSENT_TEXT = 'By requesting private access, you agree to receive access updates and marketing emails from Statement. You can unsubscribe at any time.';

	/**
	 * Records a consent_granted event if current state is not already consented to exact text.
	 *
	 * @param mixed                $wpdb Database connection.
	 * @param string               $email_hash Identity HMAC hash of email.
	 * @param int|null             $drop_term_id Drop term ID if drop-scoped.
	 * @param int|null             $grant_id Associated grant ID if Mode A.
	 * @param string               $consent_text Exact legal consent text.
	 * @param string               $source Source channel identifier.
	 * @param int                  $now_ts Unix timestamp.
	 * @param array<string, mixed>|null $encrypted_payload Optional authenticated ciphertext payload.
	 */
	public static function record_consent_granted(
		$wpdb,
		string $email_hash,
		?int $drop_term_id,
		?int $grant_id,
		string $consent_text,
		string $source,
		int $now_ts,
		?array $encrypted_payload = null
	): bool {
		if ( ! isset( $wpdb ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'statement_consent_events';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );
		$text_hash = hash( 'sha256', trim( $consent_text ) );

		// Query latest consent event for email_hash
		$latest = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE email_hash = %s ORDER BY id DESC LIMIT 1",
				$email_hash
			),
			ARRAY_A
		);

		if ( is_array( $latest ) && self::EVENT_GRANTED === $latest['event_type'] && $latest['consent_text_hash'] === $text_hash ) {
			// Already consented to current version; skip duplicate
			return true;
		}

		$data = array(
			'email_hash'         => $email_hash,
			'drop_term_id'       => $drop_term_id,
			'grant_id'           => $grant_id,
			'event_type'         => self::EVENT_GRANTED,
			'consent_version'    => self::CONSENT_VERSION,
			'exact_consent_text' => $consent_text,
			'consent_text_hash'  => $text_hash,
			'source'             => $source,
			'occurred_at'        => $now_str,
			'schema_version'     => '1.0',
		);

		if ( is_array( $encrypted_payload ) && ! empty( $encrypted_payload['ciphertext'] ) ) {
			$data['encrypted_email'] = wp_json_encode( $encrypted_payload );
			$data['encryption_algo'] = $encrypted_payload['algo'] ?? 'xchacha20-poly1305';
			$data['key_version']     = $encrypted_payload['key_version'] ?? 'v1';
		}

		// Append new consent_granted event
		$inserted = $wpdb->insert( $table, $data );

		return (bool) $inserted;
	}

	/**
	 * Records a consent_withdrawn event (unsubscribe).
	 */
	public static function record_consent_withdrawn(
		$wpdb,
		string $email_hash,
		string $source,
		int $now_ts
	): bool {
		if ( ! isset( $wpdb ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'statement_consent_events';
		$now_str = date( 'Y-m-d H:i:s', $now_ts );

		// Query latest consent event
		$latest = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE email_hash = %s ORDER BY id DESC LIMIT 1",
				$email_hash
			),
			ARRAY_A
		);

		if ( is_array( $latest ) && self::EVENT_WITHDRAWN === $latest['event_type'] ) {
			// Already withdrawn; idempotent
			return true;
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'email_hash'        => $email_hash,
				'event_type'        => self::EVENT_WITHDRAWN,
				'consent_version'   => self::CONSENT_VERSION,
				'exact_consent_text' => 'Unsubscribed from Statement marketing communications.',
				'consent_text_hash' => hash( 'sha256', 'Unsubscribed from Statement marketing communications.' ),
				'source'            => $source,
				'occurred_at'       => $now_str,
				'schema_version'    => '1.0',
			)
		);

		return (bool) $inserted;
	}

	/**
	 * Checks if active marketing consent exists for email_hash.
	 */
	public static function has_active_consent( $wpdb, string $email_hash ): bool {
		if ( ! isset( $wpdb ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'statement_consent_events';
		$latest = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT event_type FROM {$table} WHERE email_hash = %s ORDER BY id DESC LIMIT 1",
				$email_hash
			),
			ARRAY_A
		);

		return is_array( $latest ) && self::EVENT_GRANTED === $latest['event_type'];
	}
}
