<?php

namespace Statement\Collector\Core\Marketing;

use Statement\Collector\Core\Access\ConsentService;
use Statement\Collector\Core\Access\Crypto;
use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\GrantService;
use Statement\Collector\Core\Access\RateLimiter;
use Statement\Collector\Core\Access\Secrets;
use Statement\Collector\Core\Access\SessionService;
use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Handles marketing and access email signups across public and private release states.
 */
final class SignupService {
	public const NONCE_ACTION  = 'statement_signup_action';
	public const ACTION_SUBMIT = 'statement_signup_submit';
	public const FIELD_EMAIL   = 'statement_email';
	public const CONSENT_TEXT  = 'By entering your email, you consent to receive release communications. Unsubscribe at any time.';

	/** @var bool */
	private static $booted = false;

	/**
	 * Boot the signup handler.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'template_redirect', array( self::class, 'handle_submission' ) );
	}

	/**
	 * Process form submission using POST+303 PRG pattern.
	 */
	public static function handle_submission(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::ACTION_SUBMIT ] ) ) {
			return;
		}

		$redirect_url = self::get_redirect_base_url();

		// 1. Nonce validation
		$nonce = isset( $_POST['statement_signup_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['statement_signup_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'invalid', $redirect_url ), 303 );
			exit;
		}

		// 2. Email format validation
		$raw_email = isset( $_POST[ self::FIELD_EMAIL ] ) ? sanitize_email( wp_unslash( $_POST[ self::FIELD_EMAIL ] ) ) : '';
		$email     = strtolower( trim( $raw_email ) );

		if ( '' === $email || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'invalid', $redirect_url ), 303 );
			exit;
		}

		// 3. Database availability check
		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'unavailable', $redirect_url ), 303 );
			exit;
		}

		// 4. Secret Vault / Crypto availability check (fail closed if unavailable)
		if ( ! class_exists( Crypto::class ) || ! class_exists( Secrets::class ) || ! Secrets::is_configured() ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'unavailable', $redirect_url ), 303 );
			exit;
		}

		// 5. Identity & Rate-Limit hashing via dedicated Statement keys
		$ip         = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' ) );
		$ip_hash    = Crypto::hash_ip( $ip );
		$email_hash = Crypto::hash_email( $email );

		if ( null === $ip_hash || null === $email_hash ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'unavailable', $redirect_url ), 303 );
			exit;
		}

		// 6. Current Drop resolution & scope determination
		$current_drop       = class_exists( PublicApi::class ) ? PublicApi::get_current_drop() : null;
		$drop_state         = is_object( $current_drop ) && class_exists( PublicApi::class ) ? PublicApi::get_drop_state( $current_drop ) : 'none';
		$now_ts             = time();
		$rate_limit_drop_id = is_object( $current_drop ) ? (int) $current_drop->term_id : 0;

		// 7. Rate Limiter check (IP + Email scopes)
		if ( class_exists( RateLimiter::class ) ) {
			if ( ! RateLimiter::is_allowed( $wpdb, $rate_limit_drop_id, $ip_hash, $email_hash, $now_ts ) ) {
				wp_safe_redirect( add_query_arg( 'signup_status', 'rate_limited', $redirect_url ), 303 );
				exit;
			}
			RateLimiter::record_attempt( $wpdb, $rate_limit_drop_id, $ip_hash, $email_hash, $now_ts );
		}

		// 8. Encrypted email payload creation (fail closed on encryption failure)
		$encrypted_payload = Crypto::encrypt_email( $email );
		if ( null === $encrypted_payload || ! is_array( $encrypted_payload ) || empty( $encrypted_payload['ciphertext'] ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'unavailable', $redirect_url ), 303 );
			exit;
		}

		// MODE A: Current Drop is in PRIVATE_ACCESS mode -> Grant private access directly
		$is_private_access = is_object( $current_drop ) && ( ReleaseState::PRIVATE_ACCESS === $drop_state || 'private_access' === strtolower( (string) $drop_state ) );
		if ( $is_private_access && class_exists( GrantService::class ) && class_exists( SessionService::class ) && class_exists( DropConfig::class ) ) {
			$drop_id          = (int) $current_drop->term_id;
			$drop_config      = DropConfig::get_config( $drop_id );
			$duration_seconds = (int) ( $drop_config['duration_seconds'] ?? 86400 );
			$drop_close_ts    = (int) ( $drop_config['closes_at_ts'] ?? ( $now_ts + 86400 ) );

			$grant_result = GrantService::get_or_create_public_grant( $wpdb, $drop_id, $email_hash, $encrypted_payload, $duration_seconds, $drop_close_ts, $now_ts );

			if ( is_array( $grant_result ) && ! empty( $grant_result['grant'] ) ) {
				$grant            = $grant_result['grant'];
				$grant_id         = (int) $grant['id'];
				$grant_expires_ts = strtotime( (string) $grant['grant_expires_at'] . ' UTC' );
				if ( false === $grant_expires_ts ) {
					$grant_expires_ts = $now_ts + $duration_seconds;
				}

				$effective_expiry = GrantService::calculate_effective_expiry( $grant_expires_ts, $drop_close_ts );

				if ( class_exists( ConsentService::class ) ) {
					ConsentService::record_consent_granted( $wpdb, $email_hash, $drop_id, $grant_id, self::CONSENT_TEXT, 'home_signup_private_access', $now_ts, $encrypted_payload );
				}

				$session_token = SessionService::create_session( $wpdb, $grant_id, $drop_id, $effective_expiry, $now_ts );
				if ( null !== $session_token && '' !== $session_token ) {
					SessionService::set_session_cookie( $drop_id, $session_token, $effective_expiry );
					$drop_link = get_term_link( $current_drop );
					if ( ! is_wp_error( $drop_link ) ) {
						wp_safe_redirect( add_query_arg( 'access_granted', '1', $drop_link ), 303 );
						exit;
					}
				}
			}

			// If grant creation or session issuance failed (e.g. revoked/expired previous grant)
			wp_safe_redirect( add_query_arg( 'signup_status', 'unavailable', $redirect_url ), 303 );
			exit;
		}

		// MODE B: Current Drop is LIVE -> Marketing list signup with Drop context
		$is_live = is_object( $current_drop ) && ( ReleaseState::LIVE === $drop_state || 'live' === strtolower( (string) $drop_state ) );
		if ( $is_live ) {
			$drop_id = (int) $current_drop->term_id;
			if ( class_exists( ConsentService::class ) ) {
				ConsentService::record_consent_granted( $wpdb, $email_hash, $drop_id, null, self::CONSENT_TEXT, 'home_signup_live_drop', $now_ts, $encrypted_payload );
			}

			wp_safe_redirect( add_query_arg( 'signup_status', 'success', $redirect_url ), 303 );
			exit;
		}

		// MODE C: General Newsletter / No Active Drop -> Marketing list signup without Drop authorization
		if ( class_exists( ConsentService::class ) ) {
			ConsentService::record_consent_granted( $wpdb, $email_hash, null, null, self::CONSENT_TEXT, 'home_signup_generic', $now_ts, $encrypted_payload );
		}

		wp_safe_redirect( add_query_arg( 'signup_status', 'success', $redirect_url ), 303 );
		exit;
	}

	/**
	 * Return total registered marketing signups count from durable consent table.
	 */
	public static function get_signup_count(): int {
		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return 0;
		}

		$table = $wpdb->prefix . 'statement_consent_events';
		$count = $wpdb->get_var( "SELECT COUNT(DISTINCT email_hash) FROM {$table} WHERE event_type = 'consent_granted'" );

		return is_numeric( $count ) ? (int) $count : 0;
	}

	/**
	 * Resolve safe redirect base URL.
	 */
	private static function get_redirect_base_url(): string {
		$referer = wp_get_referer();
		if ( false !== $referer && '' !== $referer ) {
			return remove_query_arg( array( 'signup_status', 'access_granted' ), $referer );
		}
		return home_url( '/' );
	}
}
