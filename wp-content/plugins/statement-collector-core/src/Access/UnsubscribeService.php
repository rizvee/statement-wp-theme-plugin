<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Handles marketing email unsubscribe requests via single-use tokens.
 */
final class UnsubscribeService {
	public static function boot(): void {
		add_action( 'template_redirect', array( self::class, 'handle_unsubscribe' ) );
	}

	/**
	 * Processes /statement-unsubscribe/?token=<raw_token> requests.
	 */
	public static function handle_unsubscribe(): void {
		if ( false === strpos( $_SERVER['REQUEST_URI'] ?? '', '/statement-unsubscribe' ) ) {
			return;
		}

		global $wpdb;
		$raw_token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
		$now_ts = time();

		if ( ! empty( $raw_token ) ) {
			$token_row = TokenService::consume_token( $wpdb, $raw_token, TokenService::PURPOSE_MARKETING_UNSUBSCRIBE, $now_ts );
			if ( null !== $token_row && ! empty( $token_row['subject_email_hash'] ) ) {
				$email_hash = (string) $token_row['subject_email_hash'];

				// Record consent withdrawn
				ConsentService::record_consent_withdrawn( $wpdb, $email_hash, 'unsubscribe_token', $now_ts );

				// Cancel all pending Action Scheduler reminders for this email_hash
				if ( isset( $wpdb ) ) {
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}statement_access_grants
							SET reminder_cancelled_at = %s, reminder_cancel_reason = 'consent_withdrawn'
							WHERE email_hash = %s AND reminder_scheduled_at IS NOT NULL AND reminder_sent_at IS NULL AND reminder_cancelled_at IS NULL",
							date( 'Y-m-d H:i:s', $now_ts ),
							$email_hash
						)
					);
				}
			}
		}

		// Render minimal unsubscribe confirmation page
		PrivateAccessGate::set_private_cache_headers();

		add_action( 'wp_head', function () {
			echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
		} );

		get_header();
		?>
		<main id="main" class="site-main statement-unsubscribe-page" role="main">
			<div class="statement-unsubscribe-page__container" style="max-width: 600px; margin: 80px auto; text-align: center; padding: 0 20px;">
				<h1 style="font-size: 24px; font-weight: 300; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 24px;">
					<?php esc_html_e( 'UNSUBSCRIBED', 'statement-collector-core' ); ?>
				</h1>
				<p style="font-size: 14px; color: #666; line-height: 1.6; margin-bottom: 32px;">
					<?php esc_html_e( 'You have been unsubscribed from Statement marketing communications. Your private access rights remain unchanged.', 'statement-collector-core' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button" style="display: inline-block; padding: 12px 24px; background: #000; color: #fff; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em; font-size: 12px;">
					<?php esc_html_e( 'RETURN TO HOMEPAGE', 'statement-collector-core' ); ?>
				</a>
			</div>
		</main>
		<?php
		get_footer();
		exit;
	}
}
