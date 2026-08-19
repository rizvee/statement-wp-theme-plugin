<?php

namespace Statement\Integration\Fixtures;

defined( 'ABSPATH' ) || exit;

class AdminPage {
	public static function init(): void {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( self::class, 'register_menu' ) );
		}
	}

	public static function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			'Statement Integration Fixtures',
			'Statement Fixtures',
			'manage_woocommerce',
			'statement-integration-fixtures',
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'statement-integration-fixtures' ) );
		}

		$result_notice = null;

		// Handle Form Submissions with Throwable safety
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['statement_fixtures_action'] ) ) {
			try {
				$action = sanitize_text_field( wp_unslash( $_POST['statement_fixtures_action'] ) );

				if ( 'create' === $action ) {
					check_admin_referer( 'statement_fixtures_create' );
					$result_notice = FixtureService::create();
				} elseif ( 'adopt' === $action ) {
					check_admin_referer( 'statement_fixtures_adopt' );
					$result_notice = FixtureService::adopt_existing_fixtures();
				} elseif ( 'cleanup' === $action ) {
					check_admin_referer( 'statement_fixtures_cleanup' );
					$result_notice = CleanupService::cleanup();
				} elseif ( 'restore_currency' === $action ) {
					check_admin_referer( 'statement_fixtures_restore_currency' );
					$result_notice = CleanupService::restore_currency();
				} elseif ( 'init_vault' === $action ) {
					check_admin_referer( 'statement_fixtures_init_vault' );
					$result_notice = PrivateFixtureService::init_vault();
				} elseif ( 'reset_vault' === $action ) {
					check_admin_referer( 'statement_fixtures_reset_vault' );
					$result_notice = PrivateFixtureService::reset_vault();
				} elseif ( 'create_private_fixture' === $action ) {
					check_admin_referer( 'statement_fixtures_create_private' );
					$result_notice = PrivateFixtureService::create_private_fixture();
				} elseif ( 'cleanup_private_fixture' === $action ) {
					check_admin_referer( 'statement_fixtures_cleanup_private' );
					$result_notice = PrivateFixtureService::cleanup_private_fixture();
				} elseif ( 'run_qa_expiry' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_expiry_test();
				} elseif ( 'run_qa_revocation' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_revocation_test();
				} elseif ( 'run_qa_regrant' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::regrant_qa_access();
				} elseif ( 'run_qa_session_cap' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_session_cap_test();
				} elseif ( 'run_qa_rate_limit' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_rate_limit_test();
				} elseif ( 'run_qa_return_token' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_return_token_test();
				} elseif ( 'run_qa_unsubscribe' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_unsubscribe_test();
				} elseif ( 'run_qa_reminder' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_reminder_test();
				} elseif ( 'run_qa_verify_order' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::verify_last_order();
				} elseif ( 'run_qa_test_immutability' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::test_provenance_immutability();
				} elseif ( 'run_qa_access_email' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_access_email_test();
				} elseif ( 'run_qa_terminal_lifecycle' === $action ) {
					check_admin_referer( 'statement_fixtures_qa_action' );
					$result_notice = QaTestService::run_terminal_lifecycle_test();
				} elseif ( 'final_cleanup_dry_run' === $action ) {
					check_admin_referer( 'statement_fixtures_final_cleanup' );
					$dry = FinalCleanupService::dry_run();
					$result_notice = array(
						'success' => $dry['success'] && $dry['is_safe_to_execute'],
						'message' => sprintf(
							'Final Cleanup Dry-Run: %d entity(s) queued for deletion, %d preserved, %d ambiguity(s). Safe: %s',
							$dry['total_entities_to_clean'],
							count( $dry['preserved_entities'] ),
							count( $dry['ambiguities'] ),
							$dry['is_safe_to_execute'] ? 'YES' : 'NO'
						),
					);
				} elseif ( 'final_cleanup' === $action ) {
					check_admin_referer( 'statement_fixtures_final_cleanup' );
					$result_notice = FinalCleanupService::execute_cleanup();
				}
			} catch ( \Throwable $e ) {
				$result_notice = array(
					'success' => false,
					'message' => 'Action failed safely: ' . $e->getMessage(),
				);
			}
		}

		$env               = FixtureService::is_environment_ready();
		$state             = FixtureService::get_seeding_state();
		$collisions        = FixtureService::check_collisions();
		$verification      = VerificationService::verify();
		$crypto_diag       = PrivateFixtureService::get_crypto_diagnostics();
		$secret_diag       = PrivateFixtureService::get_secret_diagnostics();
		$db_diag           = PrivateFixtureService::get_db_diagnostics();
		$private_state     = PrivateFixtureService::get_private_fixture_state();
		$has_active_grants = PrivateFixtureService::has_active_grant_data();
		?>
		<div class="wrap">
			<h1>Statement Integration Fixtures (v0.3.3)</h1>
			<p>Temporary administrator-only runtime fixture tool for Statement Atomic integration testing.</p>


			<?php if ( $result_notice ) : ?>
				<div class="notice notice-<?php echo ! empty( $result_notice['success'] ) ? 'success' : 'error'; ?> is-dismissible">
					<p><strong><?php echo esc_html( $result_notice['message'] ); ?></strong></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $verification['error'] ) ) : ?>
				<div class="notice notice-error inline">
					<p><strong>VERIFICATION FAILED:</strong> <?php echo esc_html( $verification['message'] ); ?></p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 900px; margin-top: 20px;">
				<h2>1. Environment Preflight & Seeding State</h2>
				<table class="widefat striped" style="margin-bottom: 15px;">
					<tbody>
						<tr>
							<td><strong>WooCommerce Plugin:</strong></td>
							<td><?php echo $env['woo_active'] ? '<span style="color:green;">ACTIVE</span>' : '<span style="color:red;">MISSING</span>'; ?></td>
						</tr>
						<tr>
							<td><strong>Statement Core Plugin:</strong></td>
							<td>
								<?php if ( $env['core_active'] ) : ?>
									<span style="color:green;">ACTIVE (Version: <?php echo esc_html( $env['core_ver'] ); ?>)</span>
								<?php else : ?>
									<span style="color:red;">MISSING</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Current Woo Store Currency:</strong></td>
							<td><strong><?php echo esc_html( $env['currency'] ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>Seeding Status:</strong></td>
							<td>
								<?php if ( 'SEEDED' === $state ) : ?>
									<span style="color:blue;"><strong>SEEDED</strong> (Manifest active)</span>
								<?php elseif ( 'RECOVERY_REQUIRED' === $state ) : ?>
									<span style="color:orange;"><strong>RECOVERY REQUIRED</strong> (Orphaned fixtures detected on site)</span>
								<?php else : ?>
									<span style="color:gray;">NOT SEEDED</span>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>

				<?php if ( 'RECOVERY_REQUIRED' === $state ) : ?>
					<div class="notice notice-warning inline" style="margin-bottom: 15px;">
						<p><strong>Existing Fixtures Detected:</strong> The site already contains product records with matching SKUs and terms, but no active manifest option was found. Click <strong>Adopt Existing Test Fixtures</strong> below to discover and verify existing records without duplicating data.</p>
						<form method="post" action="">
							<?php wp_nonce_field( 'statement_fixtures_adopt' ); ?>
							<input type="hidden" name="statement_fixtures_action" value="adopt">
							<?php submit_button( 'Adopt Existing Test Fixtures', 'primary', 'submit_adopt', false ); ?>
						</form>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $collisions ) && 'NOT_SEEDED' === $state ) : ?>
					<div class="notice notice-warning inline">
						<p><strong>Potential Collisions Detected:</strong></p>
						<ul>
							<?php foreach ( $collisions as $col ) : ?>
								<li><?php echo esc_html( $col ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( 'NOT_SEEDED' === $state ) : ?>
					<form method="post" action="" style="margin-top: 15px;">
						<?php wp_nonce_field( 'statement_fixtures_create' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="create">
						<?php
						$disable_create = ! $env['can_create'] || ! empty( $collisions );
						submit_button(
							'Create Approved Test Fixtures',
							'primary',
							'submit_create',
							true,
							$disable_create ? array( 'disabled' => 'disabled' ) : array()
						);
						?>
					</form>
				<?php endif; ?>
			</div>

			<div class="card" style="max-width: 900px; margin-top: 20px;">
				<h2>2. PRIVATE ACCESS RUNTIME PREFLIGHT</h2>
				<table class="widefat striped" style="margin-bottom: 15px;">
					<tbody>
						<tr>
							<td><strong>Secret Provider:</strong></td>
							<td>
								<?php if ( 'wp_config' === $secret_diag['provider'] ) : ?>
									<span style="color:green;"><strong>WP-CONFIG</strong></span>
								<?php elseif ( 'encrypted_vault' === $secret_diag['provider'] ) : ?>
									<span style="color:green;"><strong>ENCRYPTED VAULT</strong></span>
								<?php elseif ( 'invalid_wp_config' === $secret_diag['provider'] ) : ?>
									<span style="color:red;"><strong>INVALID WP-CONFIG (PARTIAL)</strong></span>
								<?php else : ?>
									<span style="color:red;"><strong>UNAVAILABLE</strong></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Secret Vault Status:</strong></td>
							<td>
								<?php if ( $secret_diag['vault_initialized'] ) : ?>
									<span style="color:green;">INITIALIZED</span>
								<?php else : ?>
									<span style="color:gray;">NOT INITIALIZED</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Identity Key:</strong></td>
							<td>
								<?php echo $secret_diag['identity_key'] ? '<span style="color:green;">CONFIGURED</span>' : '<span style="color:red;">MISSING</span>'; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Rate-Limit Key:</strong></td>
							<td>
								<?php echo $secret_diag['rate_limit_key'] ? '<span style="color:green;">CONFIGURED</span>' : '<span style="color:red;">MISSING</span>'; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Encryption Keyring:</strong></td>
							<td>
								<?php echo $secret_diag['encryption_config'] ? '<span style="color:green;">CONFIGURED</span>' : '<span style="color:red;">MISSING / INVALID</span>'; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Active Version:</strong></td>
							<td>
								<?php if ( '' !== $secret_diag['encryption_active_version'] ) : ?>
									<span style="color:green;">CONFIGURED (<?php echo esc_html( $secret_diag['encryption_active_version'] ); ?>)</span>
								<?php else : ?>
									<span style="color:red;">MISSING</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Required Crypto Backend:</strong></td>
							<td>
								<?php if ( $crypto_diag['ready'] ) : ?>
									<span style="color:green;">AVAILABLE (<?php echo esc_html( $crypto_diag['selected_backend'] ); ?>)</span>
								<?php else : ?>
									<span style="color:red;">UNAVAILABLE</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Database / Schema (M10):</strong></td>
							<td>
								<?php if ( $db_diag['all_tables_exist'] ) : ?>
									<span style="color:green;">EXISTS (5 tables, db_version: <?php echo esc_html( $db_diag['db_version'] ); ?>)</span>
								<?php else : ?>
									<span style="color:red;">MISSING</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td><strong>Private Fixture Status:</strong></td>
							<td>
								<?php if ( 'CREATED' === $private_state ) : ?>
									<span style="color:blue;"><strong>CREATED</strong> (Manifest active)</span>
								<?php elseif ( 'PARTIAL' === $private_state ) : ?>
									<span style="color:orange;"><strong>PARTIAL</strong> (Test entities exist without active manifest)</span>
								<?php elseif ( 'RECOVERY_REQUIRED' === $private_state ) : ?>
									<span style="color:red;"><strong>RECOVERY REQUIRED</strong> (Collision or invalid lifecycle state detected)</span>
								<?php else : ?>
									<span style="color:gray;">NOT CREATED</span>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>

				<?php if ( 'PARTIAL' === $private_state ) : ?>
					<div class="notice notice-warning inline" style="margin-bottom: 15px;">
						<p><strong>Partial Private Fixture Detected:</strong> Existing TEST Drop term or Product was found on site without a complete active manifest. Click <strong>Adopt & Recover Private Access Test Fixture</strong> below to adopt the records, configure Drop settings, and complete the fixture manifest.</p>
					</div>
				<?php endif; ?>

				<?php if ( 'RECOVERY_REQUIRED' === $private_state ) : ?>
					<div class="notice notice-error inline" style="margin-bottom: 15px;">
						<p><strong>Private Fixture Conflict Detected:</strong> Existing entities with matching SKU or slug have incompatible product types or terminal lifecycle states. Manual investigation is required.</p>
					</div>
				<?php endif; ?>

				<div style="margin-top: 15px;">
					<?php if ( 'unavailable' === $secret_diag['provider'] && ! $secret_diag['vault_initialized'] ) : ?>
						<form method="post" action="" style="display: inline-block; margin-right: 15px;">
							<?php wp_nonce_field( 'statement_fixtures_init_vault' ); ?>
							<input type="hidden" name="statement_fixtures_action" value="init_vault">
							<?php submit_button( 'INITIALIZE PRIVATE ACCESS SECRET VAULT', 'primary', 'submit_init_vault', false ); ?>
						</form>
					<?php endif; ?>

					<?php if ( 'encrypted_vault' === $secret_diag['provider'] && ! $has_active_grants && 'NOT_CREATED' === $private_state ) : ?>
						<form method="post" action="" style="display: inline-block; margin-right: 15px;" onsubmit="return confirm('Reset Secret Vault? This will delete the encrypted secret vault option.');">
							<?php wp_nonce_field( 'statement_fixtures_reset_vault' ); ?>
							<input type="hidden" name="statement_fixtures_action" value="reset_vault">
							<?php submit_button( 'RESET TEST SECRET VAULT', 'secondary', 'submit_reset_vault', false ); ?>
						</form>
					<?php endif; ?>

					<?php if ( 'NOT_CREATED' === $private_state || 'PARTIAL' === $private_state ) : ?>
						<form method="post" action="" style="display: inline-block;">
							<?php wp_nonce_field( 'statement_fixtures_create_private' ); ?>
							<input type="hidden" name="statement_fixtures_action" value="create_private_fixture">
							<?php
							$can_create_private = $secret_diag['all_configured'] && $crypto_diag['ready'];
							$btn_label          = ( 'PARTIAL' === $private_state )
								? 'Adopt & Recover Private Access Test Fixture'
								: 'Create Private Access Test Fixture';
							submit_button(
								$btn_label,
								'primary',
								'submit_create_private',
								false,
								$can_create_private ? array() : array( 'disabled' => 'disabled' )
							);
							?>
						</form>
					<?php elseif ( 'CREATED' === $private_state ) : ?>
						<form method="post" action="" style="display: inline-block;" onsubmit="return confirm('Clean up Private Access test fixture?');">
							<?php wp_nonce_field( 'statement_fixtures_cleanup_private' ); ?>
							<input type="hidden" name="statement_fixtures_action" value="cleanup_private_fixture">
							<?php submit_button( 'Clean Up Private Access Test Fixture', 'delete', 'submit_cleanup_private', false ); ?>
						</form>
					<?php endif; ?>
				</div>
			</div>

			<?php
			global $wpdb;
			$qa_ctx = QaTestService::get_test_context();
			$qa_drop_id = $qa_ctx['drop_id'] ?? 0;
			$qa_grants_count = 0;
			$qa_sessions_count = 0;
			$qa_consent_status = 'ABSENT';
			$qa_config = $qa_drop_id > 0 ? \Statement\Collector\Core\Access\DropConfig::get_config( $qa_drop_id ) : null;
			$qa_send_email = $qa_config['send_access_email'] ?? 'no';
			$qa_reminder_enabled = $qa_config['reminder_enabled'] ?? 'no';
			$last_qa_order = QaTestService::verify_last_order();

			if ( $qa_drop_id > 0 && isset( $wpdb ) ) {
				$g_table = $wpdb->prefix . 'statement_access_grants';
				$s_table = $wpdb->prefix . 'statement_access_sessions';
				$qa_grants_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$g_table} WHERE drop_term_id = %d AND revoked_at IS NULL", $qa_drop_id ) );
				$qa_sessions_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$s_table} WHERE drop_term_id = %d AND revoked_at IS NULL", $qa_drop_id ) );
				$latest_g = QaTestService::find_latest_qa_grant();
				if ( $latest_g && ! empty( $latest_g['email_hash'] ) ) {
					$has_c = \Statement\Collector\Core\Access\ConsentService::has_active_consent( $wpdb, (string) $latest_g['email_hash'] );
					$qa_consent_status = $has_c ? 'PRESENT' : 'ABSENT';
				}
			}
			?>
			<div class="card" style="max-width: 900px; margin-top: 20px;">
				<h2>3. FINAL M13 PRIVATE ACCESS QA</h2>
				<table class="widefat striped" style="margin-bottom: 15px;">
					<tbody>
						<tr>
							<td><strong>Private Fixture:</strong></td>
							<td><strong><?php echo esc_html( $private_state ); ?></strong></td>
						</tr>
						<tr>
							<td><strong>Active QA Grants:</strong></td>
							<td><?php echo (int) $qa_grants_count; ?></td>
						</tr>
						<tr>
							<td><strong>Active QA Sessions:</strong></td>
							<td><?php echo (int) $qa_sessions_count; ?></td>
						</tr>
						<tr>
							<td><strong>Consent Record:</strong></td>
							<td><?php echo 'PRESENT' === $qa_consent_status ? '<span style="color:green;">PRESENT</span>' : '<span style="color:gray;">ABSENT</span>'; ?></td>
						</tr>
						<tr>
							<td><strong>Send Access Email:</strong></td>
							<td><?php echo 'yes' === $qa_send_email ? '<span style="color:green;">ON</span>' : '<span style="color:gray;">OFF</span>'; ?></td>
						</tr>
						<tr>
							<td><strong>Reminder Email:</strong></td>
							<td><?php echo 'yes' === $qa_reminder_enabled ? '<span style="color:green;">ON</span>' : '<span style="color:gray;">OFF</span>'; ?></td>
						</tr>
						<tr>
							<td><strong>Statement QA Test Gateway:</strong></td>
							<td><span style="color:green;">ENABLED (TEST-PD01-PAJ ONLY)</span></td>
						</tr>
						<tr>
							<td><strong>Controlled QA Order:</strong></td>
							<td>
								<?php if ( ! empty( $last_qa_order['order_id'] ) ) : ?>
									<strong>Order #<?php echo (int) $last_qa_order['order_id']; ?></strong> (Audit: <?php echo esc_html( $last_qa_order['audit_status'] ); ?>, Provenance: <?php echo esc_html( $last_qa_order['provenance_status'] ); ?>, State: <?php echo esc_html( $last_qa_order['release_state'] ); ?>)
								<?php else : ?>
									<span style="color:gray;">NONE</span>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>

				<h3>Deterministic QA Contract Actions</h3>
				<div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_expiry">
						<?php submit_button( 'RUN EXPIRY TEST', 'secondary', 'submit_qa_expiry', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_revocation">
						<?php submit_button( 'RUN REVOCATION TEST', 'secondary', 'submit_qa_revocation', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_regrant">
						<?php submit_button( 'RESTORE QA GRANT', 'secondary', 'submit_qa_regrant', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_session_cap">
						<?php submit_button( 'RUN SESSION CAP TEST', 'secondary', 'submit_qa_session_cap', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_rate_limit">
						<?php submit_button( 'RUN RATE LIMIT TEST', 'secondary', 'submit_qa_rate_limit', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_return_token">
						<?php submit_button( 'RUN RETURN TOKEN TEST', 'secondary', 'submit_qa_return_token', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_unsubscribe">
						<?php submit_button( 'RUN UNSUBSCRIBE TEST', 'secondary', 'submit_qa_unsubscribe', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_reminder">
						<?php submit_button( 'RUN REMINDER TEST', 'secondary', 'submit_qa_reminder', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_verify_order">
						<?php submit_button( 'VERIFY QA ORDER', 'secondary', 'submit_qa_verify_order', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_test_immutability">
						<?php submit_button( 'TEST PROVENANCE IMMUTABILITY', 'secondary', 'submit_qa_test_immutability', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_access_email">
						<?php submit_button( 'RUN ACCESS EMAIL TEST', 'secondary', 'submit_qa_access_email', false ); ?>
					</form>

					<form method="post" action="">
						<?php wp_nonce_field( 'statement_fixtures_qa_action' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="run_qa_terminal_lifecycle">
						<?php submit_button( 'REVALIDATE TERMINAL LIFECYCLE', 'secondary', 'submit_qa_terminal_lifecycle', false ); ?>
					</form>
				</div>
			</div>


			<?php if ( ! empty( $verification['seeded'] ) && ! empty( $verification['products'] ) ) : ?>
				<div class="card" style="max-width: 900px; margin-top: 20px;">
					<h2>4. Verified Live Fixtures Summary</h2>

					<p>Store Currency: <strong><?php echo esc_html( $verification['current_currency'] ); ?></strong> (Previous: <?php echo esc_html( $verification['previous_currency'] ); ?>)</p>
					<p>Category: <strong><?php echo esc_html( $verification['category_name'] ); ?></strong> | Tag: <strong><?php echo esc_html( $verification['product_tag_name'] ); ?></strong> | Drop: <strong><?php echo esc_html( $verification['drop_name'] ); ?></strong></p>

					<table class="widefat striped" style="margin-top: 10px;">
						<thead>
							<tr>
								<th>ID</th>
								<th>Name / SKU</th>
								<th>Woo Type</th>
								<th>Statement State</th>
								<th>Edition</th>
								<th>Price / Stock</th>
								<th>Purchasability</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $verification['products'] as $p ) : ?>
								<tr>
									<td><?php echo esc_html( $p['id'] ); ?></td>
									<td>
										<strong><?php echo esc_html( $p['name'] ); ?></strong><br>
										<code><?php echo esc_html( $p['sku'] ); ?></code>
									</td>
									<td><?php echo esc_html( $p['type'] ); ?></td>
									<td><mark><strong><?php echo esc_html( $p['release_state'] ); ?></strong></mark></td>
									<td><?php echo esc_html( $p['edition_label'] ); ?></td>
									<td>
										AUD <?php echo esc_html( $p['price'] ); ?><br>
										Stock: <?php echo esc_html( $p['stock_qty'] ?? 'N/A' ); ?> (<?php echo esc_html( $p['stock_status'] ?? 'N/A' ); ?>)
									</td>
									<td>
										<strong><?php echo esc_html( $p['purchasable'] ); ?></strong>
										<?php if ( ! empty( $p['variations'] ) ) : ?>
											<ul style="margin: 5px 0 0 0; padding-left: 15px; font-size: 11px;">
												<?php foreach ( $p['variations'] as $var_str ) : ?>
													<li><?php echo esc_html( $var_str ); ?></li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ( 'SEEDED' === $state || 'RECOVERY_REQUIRED' === $state || ! empty( $verification['seeded'] ) ) : ?>
				<div class="card" style="max-width: 900px; margin-top: 20px; border-left: 4px solid #dc3232;">
					<h2>5. Cleanup & Recovery Actions</h2>

					<form method="post" action="" style="display: inline-block; margin-right: 20px;" onsubmit="return confirm('Are you sure you want to delete all seeded test products, tags, categories, and drops recorded in the manifest?');">
						<?php wp_nonce_field( 'statement_fixtures_cleanup' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="cleanup">
						<?php submit_button( 'Clean Up Live Test Fixtures', 'delete', 'submit_cleanup', false ); ?>
					</form>

					<form method="post" action="" style="display: inline-block;" onsubmit="return confirm('Restore WooCommerce store currency to USD?');">
						<?php wp_nonce_field( 'statement_fixtures_restore_currency' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="restore_currency">
						<?php submit_button( 'Restore Previous Currency (USD)', 'secondary', 'submit_restore_currency', false ); ?>
					</form>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 900px; margin-top: 20px; border-left: 4px solid #0073aa;">
				<h2>6. Final Production Cleanup & Audit</h2>
				<p>Perform a non-destructive dry-run audit or execute deterministic cleanup across QA products, QA drops, QA orders, and M10 access tables.</p>

				<form method="post" action="" style="display: inline-block; margin-right: 20px;">
					<?php wp_nonce_field( 'statement_fixtures_final_cleanup' ); ?>
					<input type="hidden" name="statement_fixtures_action" value="final_cleanup_dry_run">
					<?php submit_button( 'Run QA Cleanup Dry-Run', 'primary', 'submit_final_cleanup_dry_run', false ); ?>
				</form>

				<form method="post" action="" style="display: inline-block;" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete all QA products, QA orders, and test access rows? Production pieces (Drop 001, Product 213, Product 271) will be preserved.');">
					<?php wp_nonce_field( 'statement_fixtures_final_cleanup' ); ?>
					<input type="hidden" name="statement_fixtures_action" value="final_cleanup">
					<?php submit_button( 'Execute Final Production Cleanup', 'delete', 'submit_final_cleanup', false ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}
