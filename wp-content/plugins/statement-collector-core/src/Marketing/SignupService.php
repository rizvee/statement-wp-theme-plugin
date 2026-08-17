<?php

namespace Statement\Collector\Core\Marketing;

use Statement\Collector\Core\Access\Crypto;
use Statement\Collector\Core\Access\GrantService;
use Statement\Collector\Core\Access\RateLimiter;
use Statement\Collector\Core\Access\SecretVault;
use Statement\Collector\Core\Access\SessionService;
use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Handles marketing and access email signups across public and private release states.
 */
final class SignupService {
	public const OPTION_SIGNUPS = 'statement_marketing_signups';
	public const NONCE_ACTION   = 'statement_signup_action';
	public const ACTION_SUBMIT  = 'statement_signup_submit';
	public const FIELD_EMAIL    = 'statement_email';

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

		if ( ! isset( $_POST['statement_signup_submit'] ) ) {
			return;
		}

		$redirect_url = self::get_redirect_base_url();

		$nonce = isset( $_POST['statement_signup_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['statement_signup_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'invalid_nonce', $redirect_url ), 303 );
			exit;
		}

		$raw_email = isset( $_POST['statement_email'] ) ? sanitize_email( wp_unslash( $_POST['statement_email'] ) ) : '';
		$email     = strtolower( trim( $raw_email ) );

		if ( '' === $email || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'invalid_email', $redirect_url ), 303 );
			exit;
		}

		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' ) );
		if ( class_exists( RateLimiter::class ) && RateLimiter::is_rate_limited( $ip, 'marketing_signup' ) ) {
			wp_safe_redirect( add_query_arg( 'signup_status', 'rate_limited', $redirect_url ), 303 );
			exit;
		}

		if ( class_exists( RateLimiter::class ) ) {
			RateLimiter::record_attempt( $ip, 'marketing_signup' );
		}

		$current_drop = PublicApi::get_current_drop();
		$drop_state   = is_object( $current_drop ) ? PublicApi::get_drop_state( $current_drop ) : 'none';

		// MODE A: Current Drop is in PRIVATE_ACCESS mode -> Grant private access directly
		if ( is_object( $current_drop ) && 'private_access' === $drop_state && class_exists( GrantService::class ) ) {
			$drop_id = (int) $current_drop->term_id;
			$grant   = GrantService::issue_grant( $drop_id, $email, 'home_access_gate' );

			if ( is_array( $grant ) && ! empty( $grant['grant_id'] ) && class_exists( SessionService::class ) ) {
				$session_token = SessionService::create_session( (int) $grant['grant_id'] );
				if ( '' !== $session_token ) {
					SessionService::set_session_cookie( $session_token );
					$drop_link = get_term_link( $current_drop );
					if ( ! is_wp_error( $drop_link ) ) {
						wp_safe_redirect( add_query_arg( 'access_granted', '1', $drop_link ), 303 );
						exit;
					}
				}
			}
		}

		// MODE B / C: LIVE Drop or General Newsletter Signup -> Store encrypted signup
		self::record_signup( $email, $drop_state, is_object( $current_drop ) ? (int) $current_drop->term_id : 0 );

		wp_safe_redirect( add_query_arg( 'signup_status', 'success', $redirect_url ), 303 );
		exit;
	}

	/**
	 * Safely record marketing signup with HMAC identity and encryption.
	 */
	private static function record_signup( string $email, string $drop_state, int $drop_id ): void {
		$identity_hmac = hash_hmac( 'sha256', $email, wp_salt( 'auth' ) );
		$signups       = get_option( self::OPTION_SIGNUPS, array() );
		$signups       = is_array( $signups ) ? $signups : array();

		// Check for duplicate identity
		foreach ( $signups as $entry ) {
			if ( is_array( $entry ) && isset( $entry['identity_hmac'] ) && $entry['identity_hmac'] === $identity_hmac ) {
				return; // Already registered
			}
		}

		$encrypted_email = $email;
		if ( class_exists( Crypto::class ) && SecretVault::is_available() ) {
			$encrypted = Crypto::encrypt( $email );
			if ( false !== $encrypted ) {
				$encrypted_email = $encrypted;
			}
		}

		$signups[] = array(
			'identity_hmac'    => $identity_hmac,
			'encrypted_email'  => $encrypted_email,
			'drop_state'       => $drop_state,
			'drop_id'          => $drop_id,
			'created_at_gmt'   => gmdate( 'Y-m-d H:i:s' ),
			'consent_version'  => '1.0',
		);

		// Limit bounded stored signups to 5000 records in option storage
		if ( count( $signups ) > 5000 ) {
			$signups = array_slice( $signups, -5000 );
		}

		update_option( self::OPTION_SIGNUPS, $signups, false );
	}

	/**
	 * Return total registered marketing signups count.
	 */
	public static function get_signup_count(): int {
		$signups = get_option( self::OPTION_SIGNUPS, array() );
		return is_array( $signups ) ? count( $signups ) : 0;
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
