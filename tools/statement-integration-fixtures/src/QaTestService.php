<?php

namespace Statement\Integration\Fixtures;

use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\GrantService;
use Statement\Collector\Core\Access\SessionService;
use Statement\Collector\Core\Access\TokenService;
use Statement\Collector\Core\Access\RateLimiter;
use Statement\Collector\Core\Access\ConsentService;
use Statement\Collector\Core\Access\OrderAudit;
use Statement\Collector\Core\Order\Provenance;
use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Executes controlled test actions for M13 Private Access and Provenance runtime validation.
 */
class QaTestService {
	public const QA_ORDER_OPTION = 'statement_qa_last_order_id';

	/**
	 * Resolves active test Drop ID and Product ID from PrivateFixtureService manifest or entities.
	 *
	 * @return array{drop_id: int, product_id: int}|null
	 */
	public static function get_test_context(): ?array {
		$manifest = function_exists( 'get_option' ) ? get_option( PrivateFixtureService::MANIFEST_OPTION, false ) : null;
		if ( is_array( $manifest ) && ! empty( $manifest['drop_id'] ) && ! empty( $manifest['product_id'] ) ) {
			return array(
				'drop_id'    => (int) $manifest['drop_id'],
				'product_id' => (int) $manifest['product_id'],
			);
		}

		$drop = PrivateFixtureService::find_existing_drop();
		$prod = PrivateFixtureService::find_existing_product();

		if ( is_object( $drop ) && isset( $drop->term_id ) && is_object( $prod ) && method_exists( $prod, 'get_id' ) ) {
			return array(
				'drop_id'    => (int) $drop->term_id,
				'product_id' => (int) $prod->get_id(),
			);
		}

		return null;
	}

	/**
	 * Finds latest active QA grant for the test Drop.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function find_latest_qa_grant(): ?array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return null;
		}

		$table = $wpdb->prefix . 'statement_access_grants';
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE drop_term_id = %d ORDER BY id DESC LIMIT 1",
				$ctx['drop_id']
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Runs Expiry Contract Test.
	 *
	 * Asserts:
	 * 1. Effective expiry = min(immutable grant_expires_at, drop_close_at)
	 * 2. Moving drop close earlier shortens effective expiry
	 * 3. Moving drop close later does NOT extend immutable grant expiry
	 *
	 * @return array{success: bool, message: string, earlier_shortened: string, later_extended: string}
	 */
	public static function run_expiry_test(): array {
		$ctx = self::get_test_context();
		if ( ! $ctx ) {
			return array(
				'success'           => false,
				'message'           => 'Test context missing: Private fixture not created.',
				'earlier_shortened' => 'NO',
				'later_extended'    => 'NO',
			);
		}

		$grant = self::find_latest_qa_grant();
		if ( ! $grant ) {
			return array(
				'success'           => false,
				'message'           => 'No test grant found for expiry test.',
				'earlier_shortened' => 'NO',
				'later_extended'    => 'NO',
			);
		}

		$drop_id = $ctx['drop_id'];
		$original_config = DropConfig::get_config( $drop_id );
		if ( ! $original_config ) {
			return array(
				'success'           => false,
				'message'           => 'Unable to read original Drop config.',
				'earlier_shortened' => 'NO',
				'later_extended'    => 'NO',
			);
		}

		$grant_exp_ts = strtotime( (string) $grant['grant_expires_at'] . ' UTC' );
		$now_ts       = time();

		// Step A: Calculate baseline effective expiry
		$baseline_effective = GrantService::calculate_effective_expiry( $grant_exp_ts, $original_config['closes_at_ts'] );

		// Step B: Move Drop close earlier (1 hour from now)
		$earlier_close_ts  = $now_ts + 3600;
		$earlier_effective = GrantService::calculate_effective_expiry( $grant_exp_ts, $earlier_close_ts );
		$earlier_shortened = $earlier_effective < $baseline_effective ? 'YES' : 'NO';

		// Step C: Move Drop close later (7 days from now)
		$later_close_ts  = $now_ts + ( 7 * 86400 );
		$later_effective = GrantService::calculate_effective_expiry( $grant_exp_ts, $later_close_ts );
		// The effective expiry MUST NOT exceed immutable grant expiry
		$later_extended = $later_effective > $grant_exp_ts ? 'YES' : 'NO';

		$pass = ( 'YES' === $earlier_shortened && 'NO' === $later_extended );

		return array(
			'success'           => $pass,
			'message'           => $pass ? 'Expiry rules verified: Earlier close shortens authorization; later close does not extend immutable grant.' : 'Expiry contract assertion failed.',
			'earlier_shortened' => $earlier_shortened,
			'later_extended'    => $later_extended,
		);
	}

	/**
	 * Runs Revocation Test.
	 *
	 * Revokes latest QA grant, asserts sessions are invalidated, and checks self-regrant barrier.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function run_revocation_test(): array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Test context unavailable.' );
		}

		$grant = self::find_latest_qa_grant();
		if ( ! $grant || ! empty( $grant['revoked_at'] ) ) {
			return array( 'success' => false, 'message' => 'No active QA grant found to revoke.' );
		}

		$grant_id = (int) $grant['id'];
		$now_ts   = time();

		// Revoke grant
		$revoked = GrantService::revoke_grant( $wpdb, $grant_id, 'qa_test_revoke', $now_ts );
		if ( ! $revoked ) {
			return array( 'success' => false, 'message' => 'Failed to execute GrantService::revoke_grant().' );
		}

		// Verify sessions are invalidated
		$sessions_table = $wpdb->prefix . 'statement_access_sessions';
		$active_sessions = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$sessions_table} WHERE grant_id = %d AND revoked_at IS NULL",
				$grant_id
			)
		);

		if ( $active_sessions > 0 ) {
			return array( 'success' => false, 'message' => 'Active sessions remained unrevoked after grant revocation.' );
		}

		// Verify public re-submission barrier
		$config = DropConfig::get_config( $ctx['drop_id'] );
		$payload = json_decode( (string) $grant['encrypted_email'], true ) ?? array();
		$re_grant = GrantService::get_or_create_public_grant(
			$wpdb,
			$ctx['drop_id'],
			$grant['email_hash'],
			$payload,
			$config['duration_seconds'] ?? 7200,
			$config['closes_at_ts'] ?? ( $now_ts + 86400 ),
			$now_ts
		);

		if ( null !== $re_grant ) {
			return array( 'success' => false, 'message' => 'Security violation: Public gate re-granted access to a revoked identity.' );
		}

		return array(
			'success' => true,
			'message' => 'Revocation verified: Grant revoked, all sessions revoked, public self-regrant blocked.',
		);
	}

	/**
	 * Restores/re-grants QA access via canonical admin re-grant API.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function regrant_qa_access(): array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Test context unavailable.' );
		}

		$grant = self::find_latest_qa_grant();
		if ( ! $grant ) {
			return array( 'success' => false, 'message' => 'No historical QA grant found to re-grant.' );
		}

		$grant_id = (int) $grant['id'];
		$drop_id  = $ctx['drop_id'];
		$now_ts   = time();
		$config   = DropConfig::get_config( $drop_id );

		if ( ! $config || ! DropConfig::is_config_valid( $config, $now_ts ) ) {
			return array( 'success' => false, 'message' => 'Drop configuration invalid.' );
		}

		$payload = json_decode( (string) $grant['encrypted_email'], true ) ?? array();
		$new_grant = GrantService::create_admin_regrant(
			$wpdb,
			$drop_id,
			$grant['email_hash'],
			$payload,
			$config['duration_seconds'],
			$config['closes_at_ts'],
			$grant_id,
			$now_ts
		);

		if ( ! $new_grant ) {
			return array( 'success' => false, 'message' => 'Admin re-grant creation failed.' );
		}

		// Issue a new active session
		$effective_exp_ts = GrantService::calculate_effective_expiry(
			strtotime( (string) $new_grant['grant_expires_at'] . ' UTC' ),
			$config['closes_at_ts']
		);

		$raw_token = SessionService::create_session( $wpdb, (int) $new_grant['id'], $drop_id, $effective_exp_ts, $now_ts );

		return array(
			'success' => true,
			'message' => sprintf( 'Admin QA re-grant active (Grant ID: %d, Session Created: %s).', (int) $new_grant['id'], $raw_token ? 'YES' : 'NO' ),
		);
	}

	/**
	 * Runs Session Cap Test (Max 5 active sessions).
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function run_session_cap_test(): array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Test context unavailable.' );
		}

		$grant = self::find_latest_qa_grant();
		if ( ! $grant || ! empty( $grant['revoked_at'] ) ) {
			return array( 'success' => false, 'message' => 'No active QA grant found for session cap test.' );
		}

		$grant_id = (int) $grant['id'];
		$drop_id  = $ctx['drop_id'];
		$now_ts   = time();
		$exp_ts   = $now_ts + 7200;

		$table = $wpdb->prefix . 'statement_access_sessions';

		// Clean old test sessions for this grant to start from deterministic baseline
		$wpdb->update(
			$table,
			array( 'revoked_at' => date( 'Y-m-d H:i:s', $now_ts ) ),
			array( 'grant_id' => $grant_id, 'revoked_at' => null )
		);

		// Create 5 legitimate sessions
		$tokens = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$tok = SessionService::create_session( $wpdb, $grant_id, $drop_id, $exp_ts, $now_ts + $i );
			if ( ! $tok ) {
				return array( 'success' => false, 'message' => "Failed to create session {$i}." );
			}
			$tokens[] = $tok;
		}

		$active_count_5 = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE grant_id = %d AND revoked_at IS NULL", $grant_id )
		);

		if ( 5 !== $active_count_5 ) {
			return array( 'success' => false, 'message' => "Expected 5 active sessions, got {$active_count_5}." );
		}

		// Create 6th session -> must revoke 1st (oldest) session
		$token_6 = SessionService::create_session( $wpdb, $grant_id, $drop_id, $exp_ts, $now_ts + 10 );
		if ( ! $token_6 ) {
			return array( 'success' => false, 'message' => 'Failed to create 6th session.' );
		}

		$active_count_6 = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE grant_id = %d AND revoked_at IS NULL", $grant_id )
		);

		// Assert oldest session is revoked
		$first_hash = SessionService::hash_token( $tokens[0] );
		$first_row  = $wpdb->get_row(
			$wpdb->prepare( "SELECT revoked_at FROM {$table} WHERE token_hash = %s", $first_hash ),
			ARRAY_A
		);
		$oldest_revoked = is_array( $first_row ) && ! empty( $first_row['revoked_at'] );

		// Assert newest session is active
		$sixth_hash = SessionService::hash_token( $token_6 );
		$sixth_row  = $wpdb->get_row(
			$wpdb->prepare( "SELECT revoked_at FROM {$table} WHERE token_hash = %s", $sixth_hash ),
			ARRAY_A
		);
		$newest_active = is_array( $sixth_row ) && empty( $sixth_row['revoked_at'] );

		$pass = ( 5 === $active_count_6 && $oldest_revoked && $newest_active );

		return array(
			'success' => $pass,
			'message' => $pass ? 'Session Cap (5 active max) verified: 6th session creation successfully revoked oldest session while keeping 5 active.' : 'Session cap verification failed.',
		);
	}

	/**
	 * Runs Rate Limiter Test on synthetic test identities.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function run_rate_limit_test(): array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Test context unavailable.' );
		}

		$drop_id = $ctx['drop_id'];
		$now_ts  = time();

		$test_ip_hash    = hash( 'sha256', 'qa-rate-limit-test-ip-' . $now_ts );
		$test_email_hash = hash( 'sha256', 'qa-rate-limit-test-email-' . $now_ts );

		// 1. Check initial state -> allowed
		if ( ! RateLimiter::is_allowed( $wpdb, $drop_id, $test_ip_hash, $test_email_hash, $now_ts ) ) {
			return array( 'success' => false, 'message' => 'Rate limiter rejected initial attempt.' );
		}

		// 2. Record 3 email attempts
		RateLimiter::record_attempt( $wpdb, $drop_id, $test_ip_hash, $test_email_hash, $now_ts );
		RateLimiter::record_attempt( $wpdb, $drop_id, $test_ip_hash, $test_email_hash, $now_ts );
		RateLimiter::record_attempt( $wpdb, $drop_id, $test_ip_hash, $test_email_hash, $now_ts );

		// 3. Email short window limit is 3 -> 4th attempt must be blocked
		$blocked = ! RateLimiter::is_allowed( $wpdb, $drop_id, $test_ip_hash, $test_email_hash, $now_ts );

		// Clean up synthetic test rate limit rows
		$table = $wpdb->prefix . 'statement_access_rate_limits';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE drop_term_id = %d AND scope_hash IN (%s, %s)",
				$drop_id,
				$test_ip_hash,
				$test_email_hash
			)
		);

		return array(
			'success' => $blocked,
			'message' => $blocked ? 'Rate Limiter verified: Allowed 3 attempts, 4th attempt blocked at threshold.' : 'Rate limiter threshold enforcement failed.',
		);
	}

	/**
	 * Runs Return Token Self-Test.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function run_return_token_test(): array {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Database unavailable.' );
		}

		$now_ts = time();
		$exp_ts = $now_ts + 3600;

		// 1. Create token
		$token = TokenService::create_token( $wpdb, TokenService::PURPOSE_ACCESS_RETURN, null, null, $exp_ts, $now_ts );
		if ( ! $token ) {
			return array( 'success' => false, 'message' => 'TokenService::create_token failed.' );
		}

		// 2. First consumption -> valid
		$row1 = TokenService::consume_token( $wpdb, $token, TokenService::PURPOSE_ACCESS_RETURN, $now_ts );
		if ( ! $row1 ) {
			return array( 'success' => false, 'message' => 'Failed to consume valid unused token.' );
		}

		// 3. Replay attack -> rejected
		$row2 = TokenService::consume_token( $wpdb, $token, TokenService::PURPOSE_ACCESS_RETURN, $now_ts );
		if ( null !== $row2 ) {
			return array( 'success' => false, 'message' => 'Security failure: Replayed single-use token was accepted.' );
		}

		// 4. Invalid token -> rejected
		$row3 = TokenService::consume_token( $wpdb, 'invalid-token-value-12345', TokenService::PURPOSE_ACCESS_RETURN, $now_ts );
		if ( null !== $row3 ) {
			return array( 'success' => false, 'message' => 'Security failure: Invalid token was accepted.' );
		}

		// 5. Expired token -> rejected
		$exp_token = TokenService::create_token( $wpdb, TokenService::PURPOSE_ACCESS_RETURN, null, null, $now_ts - 10, $now_ts - 20 );
		$row4      = TokenService::consume_token( $wpdb, (string) $exp_token, TokenService::PURPOSE_ACCESS_RETURN, $now_ts );
		if ( null !== $row4 ) {
			return array( 'success' => false, 'message' => 'Security failure: Expired token was accepted.' );
		}

		return array(
			'success' => true,
			'message' => 'Return Token verified: Single-use consumption PASS, replay rejected, invalid rejected, expired rejected.',
		);
	}

	/**
	 * Runs Marketing Unsubscribe Boundary Test.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function run_unsubscribe_test(): array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Test context unavailable.' );
		}

		$grant = self::find_latest_qa_grant();
		if ( ! $grant ) {
			return array( 'success' => false, 'message' => 'No QA grant found for unsubscribe test.' );
		}

		$email_hash = (string) $grant['email_hash'];
		$drop_id    = $ctx['drop_id'];
		$now_ts     = time();

		// Record consent granted
		ConsentService::record_consent_granted( $wpdb, $email_hash, $drop_id, (int) $grant['id'], ConsentService::DEFAULT_CONSENT_TEXT, 'qa_test', $now_ts );
		$has_consent_1 = ConsentService::has_active_consent( $wpdb, $email_hash );

		// Withdraw consent
		ConsentService::record_consent_withdrawn( $wpdb, $email_hash, 'qa_unsubscribe_test', $now_ts + 1 );
		$has_consent_2 = ConsentService::has_active_consent( $wpdb, $email_hash );

		// Verify grant remains unrevoked
		$grants_table = $wpdb->prefix . 'statement_access_grants';
		$grant_row    = $wpdb->get_row(
			$wpdb->prepare( "SELECT revoked_at FROM {$grants_table} WHERE id = %d", (int) $grant['id'] ),
			ARRAY_A
		);
		$grant_still_valid = is_array( $grant_row ) && empty( $grant_row['revoked_at'] );

		// Restore consent for subsequent testing
		ConsentService::record_consent_granted( $wpdb, $email_hash, $drop_id, (int) $grant['id'], ConsentService::DEFAULT_CONSENT_TEXT, 'qa_restore', $now_ts + 2 );

		$pass = ( $has_consent_1 && ! $has_consent_2 && $grant_still_valid );

		return array(
			'success' => $pass,
			'message' => $pass ? 'Unsubscribe Boundary verified: Marketing consent withdrawn while private access grant remains valid.' : 'Unsubscribe boundary verification failed.',
		);
	}

	/**
	 * Runs Reminder & Action Scheduler Test.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function run_reminder_test(): array {
		global $wpdb;
		$ctx = self::get_test_context();
		if ( ! $ctx || ! isset( $wpdb ) ) {
			return array( 'success' => false, 'message' => 'Test context unavailable.' );
		}

		$grant = self::find_latest_qa_grant();
		if ( ! $grant ) {
			return array( 'success' => false, 'message' => 'No QA grant found for reminder test.' );
		}

		$grant_id   = (int) $grant['id'];
		$drop_id    = $ctx['drop_id'];
		$email_hash = (string) $grant['email_hash'];
		$now_ts     = time();
		$sched_ts   = $now_ts + 300;

		// Clean prior reminder record
		$grants_table = $wpdb->prefix . 'statement_access_grants';
		$wpdb->update(
			$grants_table,
			array(
				'reminder_scheduled_at' => null,
				'reminder_sent_at'      => null,
				'reminder_cancelled_at' => null,
			),
			array( 'id' => $grant_id )
		);

		// Schedule reminder via hook
		do_action( 'statement_schedule_private_access_reminder', $grant_id, $email_hash, $drop_id, $sched_ts );

		// Check if scheduled in DB
		$updated_grant = $wpdb->get_row(
			$wpdb->prepare( "SELECT reminder_scheduled_at FROM {$grants_table} WHERE id = %d", $grant_id ),
			ARRAY_A
		);
		$scheduled = is_array( $updated_grant ) && ! empty( $updated_grant['reminder_scheduled_at'] );

		// Test cancellation on Add-to-Bag
		do_action( 'statement_private_access_added_to_cart', $grant_id, $drop_id );

		$cancelled_grant = $wpdb->get_row(
			$wpdb->prepare( "SELECT reminder_cancelled_at, reminder_cancel_reason FROM {$grants_table} WHERE id = %d", $grant_id ),
			ARRAY_A
		);
		$cancelled = is_array( $cancelled_grant ) && ! empty( $cancelled_grant['reminder_cancelled_at'] ) && 'add_to_cart' === $cancelled_grant['reminder_cancel_reason'];

		// Cancel Action Scheduler job if present
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( 'statement_private_access_reminder_action', array( 'grant_id' => $grant_id ) );
		}

		$pass = ( $scheduled && $cancelled );

		return array(
			'success' => $pass,
			'message' => $pass ? 'Reminder Scheduler verified: Action scheduled cleanly and auto-cancelled upon Add to Bag.' : 'Reminder scheduling or cancellation failed.',
		);
	}

	/**
	 * Verifies M10 Order Audit and M12 Provenance for the latest controlled QA order.
	 *
	 * @return array{success: bool, message: string, order_id: int, audit_status: string, provenance_status: string, release_state: string}
	 */
	public static function verify_last_order(): array {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return array(
				'success'           => false,
				'message'           => 'WooCommerce order API unavailable.',
				'order_id'          => 0,
				'audit_status'      => 'UNAVAILABLE',
				'provenance_status' => 'UNAVAILABLE',
				'release_state'     => 'NONE',
			);
		}

		$order_id = (int) get_option( self::QA_ORDER_OPTION, 0 );
		if ( $order_id <= 0 ) {
			// Find most recent order with _statement_is_qa_order = 'yes'
			$orders = wc_get_orders(
				array(
					'limit'      => 1,
					'orderby'    => 'date',
					'order'      => 'DESC',
					'meta_key'   => '_statement_is_qa_order',
					'meta_value' => 'yes',
				)
			);
			if ( ! empty( $orders ) && is_object( $orders[0] ) ) {
				$order_id = $orders[0]->get_id();
				update_option( self::QA_ORDER_OPTION, $order_id );
			}
		}

		if ( $order_id <= 0 ) {
			return array(
				'success'           => false,
				'message'           => 'No controlled QA order found.',
				'order_id'          => 0,
				'audit_status'      => 'NONE',
				'provenance_status' => 'NONE',
				'release_state'     => 'NONE',
			);
		}

		$order = wc_get_order( $order_id );
		if ( ! is_object( $order ) ) {
			return array(
				'success'           => false,
				'message'           => "Order #{$order_id} could not be loaded.",
				'order_id'          => $order_id,
				'audit_status'      => 'ERROR',
				'provenance_status' => 'ERROR',
				'release_state'     => 'NONE',
			);
		}

		$items = $order->get_items();
		if ( empty( $items ) ) {
			return array(
				'success'           => false,
				'message'           => "Order #{$order_id} has no line items.",
				'order_id'          => $order_id,
				'audit_status'      => 'EMPTY',
				'provenance_status' => 'EMPTY',
				'release_state'     => 'NONE',
			);
		}

		$first_item = reset( $items );

		// Check M10 Order Audit
		$grant_id      = $first_item->get_meta( OrderAudit::META_GRANT_ID, true );
		$drop_id       = $first_item->get_meta( OrderAudit::META_DROP_ID, true );
		$authorized_at = $first_item->get_meta( OrderAudit::META_AUTHORIZED_AT, true );
		$ctx_ver       = $first_item->get_meta( OrderAudit::META_CONTEXT_VERSION, true );

		$audit_ok = ( '' !== (string) $grant_id && '' !== (string) $drop_id && '' !== (string) $authorized_at && '' !== (string) $ctx_ver );

		// Check M12 Provenance
		$prov_status = Provenance::get_snapshot_status( $first_item );
		$rel_state   = (string) $first_item->get_meta( Provenance::META_RELEASE_STATE, true );

		$pass = ( $audit_ok && 'complete' === $prov_status && ReleaseState::PRIVATE_ACCESS === $rel_state );

		return array(
			'success'           => $pass,
			'message'           => $pass ? sprintf( 'Order #%d verified: M10 audit attached, M12 frozen provenance complete, release_state=PRIVATE_ACCESS.', $order_id ) : 'Order audit or provenance validation incomplete.',
			'order_id'          => $order_id,
			'audit_status'      => $audit_ok ? 'PASS' : 'INCOMPLETE',
			'provenance_status' => $prov_status,
			'release_state'     => $rel_state,
		);
	}

	/**
	 * Tests Provenance Immutability across reversible source product edits.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function test_provenance_immutability(): array {
		$order_ver = self::verify_last_order();
		if ( ! $order_ver['success'] ) {
			return array( 'success' => false, 'message' => 'Controlled order must be verified before testing immutability.' );
		}

		$order = wc_get_order( $order_ver['order_id'] );
		$items = $order->get_items();
		$item  = reset( $items );

		$frozen_edition = (string) $item->get_meta( Provenance::META_EDITION_LABEL, true );
		$product_id     = (int) $item->get_meta( Provenance::META_PRODUCT_ID, true );

		$product = wc_get_product( $product_id );
		if ( ! is_object( $product ) ) {
			return array( 'success' => false, 'message' => 'Source product not found.' );
		}

		// Reversible edit: change edition label on source product
		$original_edition = Metadata::get_edition_label( $product );
		Metadata::set_edition_label( $product, 'Mutated Test Edition Label' );
		$product->save();

		// Re-read order item metadata
		$order_fresh = wc_get_order( $order_ver['order_id'] );
		$items_fresh = $order_fresh->get_items();
		$item_fresh  = reset( $items_fresh );
		$frozen_after = (string) $item_fresh->get_meta( Provenance::META_EDITION_LABEL, true );

		// Restore original edition label on source product
		Metadata::set_edition_label( $product, $original_edition );
		$product->save();

		$immutable = ( $frozen_edition === $frozen_after && 'Mutated Test Edition Label' !== $frozen_after );

		return array(
			'success' => $immutable,
			'message' => $immutable ? 'Provenance Immutability verified: Order line item snapshot remained unchanged after source product metadata modification.' : 'Provenance immutability violated.',
		);
	}
}
