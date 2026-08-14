<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Handles HTTP requests, gate display, access form POST processing, and response hardening for /drop/<slug>/.
 */
final class PrivateAccessGate {
	public static function boot(): void {
		add_action( 'template_redirect', array( self::class, 'handle_drop_access' ) );
	}

	/**
	 * Main gate handler on template_redirect.
	 */
	public static function handle_drop_access(): void {
		if ( ! is_tax( 'statement_drop' ) ) {
			return;
		}

		$term = get_queried_object();
		if ( ! $term || ! isset( $term->term_id ) ) {
			return;
		}

		$drop_id = (int) $term->term_id;
		$now_ts  = time();

		// Check if Drop contains PRIVATE_ACCESS products
		$drop_products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'statement_drop',
						'field'    => 'term_id',
						'terms'    => $drop_id,
					),
				),
			)
		);

		$has_private_products = false;
		foreach ( $drop_products as $pid ) {
			if ( ReleaseState::PRIVATE_ACCESS === Metadata::get_release_state( (int) $pid ) ) {
				$has_private_products = true;
				break;
			}
		}

		if ( ! $has_private_products ) {
			// Normal public Drop archive handles non-private Drops
			return;
		}

		// Handle Form POST submission
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) && isset( $_POST['statement_access_action'] ) ) {
			self::process_gate_post( $drop_id, $now_ts );
			return;
		}

		// Check visitor authorization
		$is_authorized = EligibilityService::is_commerce_eligible( $drop_products[0] ?? 0, null, $now_ts );
		if ( ! $is_authorized ) {
			self::render_unauthorized_gate( $term );
			exit;
		}

		// Authorized visitor: apply private cache headers & let normal template load authorized products
		self::set_private_cache_headers();
	}

	/**
	 * Sets security & anti-cache headers for private access responses.
	 */
	public static function set_private_cache_headers(): void {
		if ( headers_sent() ) {
			return;
		}

		header( 'Cache-Control: private, no-store, no-cache, max-age=0, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'Vary: Cookie' );
	}

	/**
	 * Processes gate POST form submission with PRG (HTTP 303).
	 */
	private static function process_gate_post( int $drop_id, int $now_ts ): void {
		global $wpdb;

		// 1. Nonce check
		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'statement_private_access_' . $drop_id ) ) {
			wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
			exit;
		}

		// 2. IP rate limit check
		$raw_ip  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
		$ip_hash = Crypto::hash_ip( $raw_ip );
		$email_raw = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$email_hash = Crypto::hash_email( $email_raw );

		if ( ! $ip_hash || ! $email_hash || ! is_email( $email_raw ) ) {
			wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
			exit;
		}

		if ( ! RateLimiter::is_allowed( $wpdb, $drop_id, $ip_hash, $email_hash, $now_ts ) ) {
			// Rate limited: generic PRG redirect (prevents enumeration)
			wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
			exit;
		}

		RateLimiter::record_attempt( $wpdb, $drop_id, $ip_hash, $email_hash, $now_ts );

		// 3. Drop config check
		$config = DropConfig::get_config( $drop_id );
		if ( null === $config || ! DropConfig::is_config_valid( $config, $now_ts ) ) {
			wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
			exit;
		}

		// 4. Encrypt email & issue/retrieve grant
		$encrypted = Crypto::encrypt_email( $email_raw );
		if ( null === $encrypted ) {
			wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
			exit;
		}

		$grant_result = GrantService::get_or_create_public_grant(
			$wpdb,
			$drop_id,
			$email_hash,
			$encrypted,
			$config['duration_seconds'],
			$config['closes_at_ts'],
			$now_ts
		);

		if ( null === $grant_result ) {
			wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
			exit;
		}

		$grant = $grant_result['grant'];
		$grant_id = (int) $grant['id'];

		// 5. Consent recording
		ConsentService::record_consent_granted(
			$wpdb,
			$email_hash,
			$drop_id,
			$grant_id,
			ConsentService::DEFAULT_CONSENT_TEXT,
			'public_gate',
			$now_ts
		);

		// 6. Create browser session & set cookie
		$effective_exp_ts = GrantService::calculate_effective_expiry(
			strtotime( (string) $grant['grant_expires_at'] . ' UTC' ),
			$config['closes_at_ts']
		);

		$raw_token = SessionService::create_session( $wpdb, $grant_id, $drop_id, $effective_exp_ts, $now_ts );
		if ( $raw_token ) {
			SessionService::set_session_cookie( $drop_id, $raw_token, $effective_exp_ts );
		}

		// 7. Optional Access Email & Reminder scheduling
		if ( 'yes' === $config['send_access_email'] ) {
			// Trigger WooCommerce email via action
			do_action( 'statement_send_private_access_email', $grant_id, $email_raw, $drop_id );
		}

		if ( 'yes' === $config['reminder_enabled'] && $config['reminder_delay_seconds'] > 0 ) {
			do_action( 'statement_schedule_private_access_reminder', $grant_id, $email_hash, $drop_id, $now_ts + $config['reminder_delay_seconds'] );
		}

		// 8. PRG 303 Redirect to clean Drop URL
		wp_safe_redirect( get_term_link( $drop_id, 'statement_drop' ), 303 );
		exit;
	}

	/**
	 * Renders the minimal unauthorized access gate.
	 */
	public static function render_unauthorized_gate( $term ): void {
		self::set_private_cache_headers();

		add_action( 'wp_head', function () {
			echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
		} );

		get_header();
		?>
		<main id="main" class="site-main statement-access-gate" role="main">
			<div class="statement-access-gate__container">
				<h1 class="statement-access-gate__title"><?php esc_html_e( 'PRIVATE ACCESS', 'statement-collector-core' ); ?></h1>
				<p class="statement-access-gate__subtitle"><?php esc_html_e( 'Enter your email to access this release.', 'statement-collector-core' ); ?></p>

				<form method="post" action="" class="statement-access-gate__form">
					<?php wp_nonce_field( 'statement_private_access_' . (int) $term->term_id ); ?>
					<input type="hidden" name="statement_access_action" value="request_access" />

					<div class="statement-access-gate__field">
						<label for="statement-access-email" class="screen-reader-text"><?php esc_html_e( 'Email address', 'statement-collector-core' ); ?></label>
						<input type="email" id="statement-access-email" name="email" required placeholder="<?php esc_attr_e( 'EMAIL', 'statement-collector-core' ); ?>" autocomplete="email" class="statement-access-gate__input" />
					</div>

					<button type="submit" class="statement-access-gate__button"><?php esc_html_e( 'ENTER PRIVATE ACCESS', 'statement-collector-core' ); ?></button>

					<p class="statement-access-gate__consent">
						<?php echo esc_html( ConsentService::DEFAULT_CONSENT_TEXT ); ?>
					</p>
				</form>
			</div>
		</main>
		<?php
		get_footer();
	}
}
