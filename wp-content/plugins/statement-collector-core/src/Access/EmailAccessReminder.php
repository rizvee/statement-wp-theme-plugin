<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Custom WooCommerce email class: Statement — Private Access Reminder.
 */
class EmailAccessReminder {
	public const ID = 'statement_private_access_reminder';

	public static function boot(): void {
		add_filter( 'woocommerce_email_classes', array( self::class, 'register_email' ) );
	}

	public static function register_email( array $emails ): array {
		if ( class_exists( 'WC_Email' ) ) {
			$emails[ self::ID ] = new self();
		}
		return $emails;
	}

	/**
	 * Sends reminder email for a grant.
	 */
	public static function send_reminder( int $grant_id, string $email_raw, int $drop_id ): bool {
		global $wpdb;
		$now_ts = time();

		$unsub_token = TokenService::create_token(
			$wpdb,
			TokenService::PURPOSE_MARKETING_UNSUBSCRIBE,
			$grant_id,
			Crypto::hash_email( $email_raw ),
			$now_ts + TokenService::MAX_UNSUBSCRIBE_LIFETIME,
			$now_ts
		);

		$drop_link = get_term_link( $drop_id, 'statement_drop' );
		$unsub_url = add_query_arg( 'token', $unsub_token, home_url( '/statement-unsubscribe/' ) );

		$subject = __( 'Reminder: Your Private Access to Statement', 'statement-collector-core' );
		$message = sprintf(
			__( "Your private access to Statement is currently active.\n\nView the release:\n%s\n\nTo stop receiving access updates and marketing emails, click here: %s", 'statement-collector-core' ),
			is_wp_error( $drop_link ) ? home_url() : $drop_link,
			$unsub_url
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		$sent = wp_mail( $email_raw, $subject, $message, $headers );

		if ( $sent && isset( $wpdb ) ) {
			$wpdb->update(
				$wpdb->prefix . 'statement_access_grants',
				array( 'reminder_sent_at' => date( 'Y-m-d H:i:s', $now_ts ) ),
				array( 'id' => $grant_id )
			);
		}

		return $sent;
	}
}
