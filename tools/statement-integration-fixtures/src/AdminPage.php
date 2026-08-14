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

		// Handle Form Submissions
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['statement_fixtures_action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_POST['statement_fixtures_action'] ) );

			if ( 'create' === $action ) {
				check_admin_referer( 'statement_fixtures_create' );
				$result_notice = FixtureService::create();
			} elseif ( 'cleanup' === $action ) {
				check_admin_referer( 'statement_fixtures_cleanup' );
				$result_notice = CleanupService::cleanup();
			} elseif ( 'restore_currency' === $action ) {
				check_admin_referer( 'statement_fixtures_restore_currency' );
				$result_notice = CleanupService::restore_currency();
			}
		}

		$env          = FixtureService::is_environment_ready();
		$collisions   = FixtureService::check_collisions();
		$verification = VerificationService::verify();
		?>
		<div class="wrap">
			<h1>Statement Integration Fixtures</h1>
			<p>Temporary administrator-only runtime fixture tool for Statement Atomic integration testing.</p>

			<?php if ( $result_notice ) : ?>
				<div class="notice notice-<?php echo $result_notice['success'] ? 'success' : 'error'; ?> is-dismissible">
					<p><strong><?php echo esc_html( $result_notice['message'] ); ?></strong></p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 900px; margin-top: 20px;">
				<h2>1. Environment Preflight</h2>
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
								<?php if ( $verification['seeded'] ) : ?>
									<span style="color:blue;">SEEDED (Manifest active)</span>
								<?php else : ?>
									<span style="color:gray;">NOT SEEDED</span>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>

				<?php if ( ! empty( $collisions ) && ! $verification['seeded'] ) : ?>
					<div class="notice notice-warning inline">
						<p><strong>Potential Collisions Detected:</strong></p>
						<ul>
							<?php foreach ( $collisions as $col ) : ?>
								<li><?php echo esc_html( $col ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<form method="post" action="" style="margin-top: 15px;">
					<?php wp_nonce_field( 'statement_fixtures_create' ); ?>
					<input type="hidden" name="statement_fixtures_action" value="create">
					<?php
					$disable_create = ! $env['can_create'] || $verification['seeded'] || ! empty( $collisions );
					submit_button(
						'Create Approved Test Fixtures',
						'primary',
						'submit_create',
						true,
						$disable_create ? array( 'disabled' => 'disabled' ) : array()
					);
					?>
				</form>
			</div>

			<?php if ( $verification['seeded'] ) : ?>
				<div class="card" style="max-width: 900px; margin-top: 20px;">
					<h2>2. Verified Fixtures Summary</h2>
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

				<div class="card" style="max-width: 900px; margin-top: 20px; border-left: 4px solid #dc3232;">
					<h2>3. Cleanup & Recovery Actions</h2>
					<form method="post" action="" style="display: inline-block; margin-right: 20px;" onsubmit="return confirm('Are you sure you want to delete all seeded test products, tags, categories, and drops recorded in the manifest?');">
						<?php wp_nonce_field( 'statement_fixtures_cleanup' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="cleanup">
						<?php submit_button( 'Clean Up Test Fixtures', 'delete', 'submit_cleanup', false ); ?>
					</form>

					<form method="post" action="" style="display: inline-block;" onsubmit="return confirm('Restore WooCommerce store currency to USD?');">
						<?php wp_nonce_field( 'statement_fixtures_restore_currency' ); ?>
						<input type="hidden" name="statement_fixtures_action" value="restore_currency">
						<?php submit_button( 'Restore Previous Currency (USD)', 'secondary', 'submit_restore_currency', false ); ?>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
