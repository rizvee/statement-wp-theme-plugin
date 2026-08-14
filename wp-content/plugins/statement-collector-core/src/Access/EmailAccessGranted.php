<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Custom WooCommerce email class: Statement — Private Access Granted.
 */
class EmailAccessGranted {
	public const ID = 'statement_private_access_granted';

	public static function boot(): void {
		add_filter( 'woocommerce_email_classes', array( self::class, 'register_email' ) );
		add_action( 'statement_send_private_access_email', array( self::class, 'trigger' ), 10, 3 );
	}

	/**
	 * Registers custom WooCommerce email class if WC_Email exists.
	 */
	public static function register_email( array $emails ): array {
		if ( class_exists( 'WC_Email' ) ) {
			$emails[ self::ID ] = new self();
		}
		return $emails;
	}

	/**
	 * Triggers private access return email send.
	 */
	public static function trigger( int $grant_id, string $email_raw, int $drop_id ): bool {
		global $wpdb;
		$now_ts = time();

		$config = DropConfig::get_config( $drop_id );
		$exp_ts = min( $now_ts + TokenService::MAX_ACCESS_RETURN_LIFETIME, $config['closes_at_ts'] ?? ( $now_ts + TokenService::MAX_ACCESS_RETURN_LIFETIME ) );

		$token = TokenService::create_token( $wpdb, TokenService::PURPOSE_ACCESS_RETURN, $grant_id, null, $exp_ts, $now_ts );
		if ( ! $token ) {
			return false;
		}

		$drop_link = get_term_link( $drop_id, 'statement_drop' );
		if ( is_wp_error( $drop_link ) ) {
			return false;
		}

		$access_url = add_query_arg( 'statement_token', $token, $drop_link );
		$subject    = __( 'Your Private Access to Statement', 'statement-collector-core' );
		$message    = sprintf(
			__( "You have been granted private access to Statement.\n\nClick the link below to enter:\n%s\n\nThis link is single-use and will expire shortly.", 'statement-collector-core' ),
			$access_url
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		$sent = wp_mail( $email_raw, $subject, $message, $headers );

		if ( $sent && isset( $wpdb ) ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}statement_access_grants SET access_email_sent_at = %s, access_email_count = access_email_count + 1 WHERE id = %d",
					date( 'Y-m-d H:i:s', $now_ts ),
					$grant_id
				)
			);
		}

		return $sent;
	}
}
