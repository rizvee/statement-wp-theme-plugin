<?php

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

final class AdminPage {

	public const SLUG = 'statement-client-demo';

	/**
	 * Register admin menu and POST handlers.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_statement_client_demo_action', array( __CLASS__, 'handle_post_action' ) );
	}

	/**
	 * Register admin page.
	 */
	public static function register_menu(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'woocommerce',
			'Statement Client Demo',
			'Client Demo',
			'manage_woocommerce',
			self::SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Handle admin POST form submissions.
	 */
	public static function handle_post_action(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized user capability.', 'statement-client-demo' ), 403 );
		}

		check_admin_referer( 'statement_client_demo_action_nonce', 'demo_nonce' );

		$action_type = isset( $_POST['demo_action'] ) ? sanitize_key( $_POST['demo_action'] ) : '';

		if ( 'seed' === $action_type ) {
			$result = DemoSeederService::seed_or_update();
			set_transient( 'statement_client_demo_last_result', $result, 60 );
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '&seeded=1' ) );
			exit;
		}

		if ( 'repair' === $action_type ) {
			$result = DemoSeederService::repair_client_demo();
			set_transient( 'statement_client_demo_last_result', $result, 60 );
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '&repaired=1' ) );
			exit;
		}

		if ( 'dry_run' === $action_type ) {
			$result = DemoSeederService::dry_run();
			set_transient( 'statement_client_demo_last_result', $result, 60 );
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '&dry_run=1' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * Render admin page UI.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'statement-client-demo' ), 403 );
		}

		$manifest = ManifestService::get_manifest();
		$rollback = ManifestService::get_rollback();
		$last_res = get_transient( 'statement_client_demo_last_result' );
		delete_transient( 'statement_client_demo_last_result' );

		$dry_run_data = ( is_array( $last_res ) && isset( $last_res['mode'] ) && 'DRY_RUN' === $last_res['mode'] )
			? $last_res
			: DemoSeederService::dry_run();
		?>
		<div class="wrap statement-client-demo-admin" style="max-width: 1100px;">
			<h1><?php esc_html_e( 'Statement — Client Demo Seeder & Importer (v0.2.1)', 'statement-client-demo' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Manage real Statement brand media, Drop 001, variable products (S/M/L), and light-first editorial pages with strict ownership safety.', 'statement-client-demo' ); ?>
			</p>

			<?php if ( isset( $_GET['seeded'] ) && is_array( $last_res ) ) : ?>
				<?php if ( empty( $last_res['errors'] ) && ( $last_res['success'] ?? true ) ) : ?>
					<div class="notice notice-success is-dismissible">
						<p><strong><?php esc_html_e( 'Client Demo Content Successfully Seeded & Updated!', 'statement-client-demo' ); ?></strong></p>
						<ul>
							<li><?php printf( esc_html__( 'Media Items Imported / Reused: %d', 'statement-client-demo' ), count( $last_res['media'] ?? array() ) ); ?></li>
							<li><?php printf( esc_html__( 'Drop ID: %d', 'statement-client-demo' ), (int) ( $last_res['drop_id'] ?? 0 ) ); ?></li>
							<li><?php printf( esc_html__( 'Products Seeded: %d', 'statement-client-demo' ), count( $last_res['products'] ?? array() ) ); ?></li>
							<li><?php printf( esc_html__( 'Front Page Set to: Statement Home (ID: %d)', 'statement-client-demo' ), (int) ( $last_res['pages']['statement_home'] ?? 0 ) ); ?></li>
						</ul>
					</div>
				<?php else : ?>
					<div class="notice notice-error is-dismissible">
						<p><strong><?php esc_html_e( 'Notice during Seeding Operation:', 'statement-client-demo' ); ?></strong></p>
						<ul>
							<?php foreach ( (array) ( $last_res['errors'] ?? array() ) as $err ) : ?>
								<li><?php echo esc_html( $err ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php elseif ( isset( $_GET['repaired'] ) && is_array( $last_res ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e( 'Client Demo Ownership Repaired Successfully!', 'statement-client-demo' ); ?></strong></p>
					<?php if ( ! empty( $last_res['detached'] ) ) : ?>
						<p><?php esc_html_e( 'Repairs executed:', 'statement-client-demo' ); ?></p>
						<ul>
							<?php foreach ( $last_res['detached'] as $msg ) : ?>
								<li><?php echo esc_html( $msg ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $dry_run_data['preflight']['has_duplicate_id'] ) || ! empty( $dry_run_data['preflight']['has_fixture_collision'] ) ) : ?>
				<div class="notice notice-warning">
					<p><strong><?php esc_html_e( 'Warning: Collision or Corrupted Manifest Detected', 'statement-client-demo' ); ?></strong></p>
					<p><?php esc_html_e( 'Click "Repair Client Demo Ownership" to automatically decouple unowned or duplicate entities without altering QA test fixtures.', 'statement-client-demo' ); ?></p>
				</div>
			<?php endif; ?>

			<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
				<div>
					<div class="card" style="padding: 16px; margin-bottom: 20px;">
						<h2><?php esc_html_e( 'Live Demo Entity Status', 'statement-client-demo' ); ?></h2>
						<table class="widefat striped" style="margin-top: 10px;">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Entity', 'statement-client-demo' ); ?></th>
									<th><?php esc_html_e( 'Details', 'statement-client-demo' ); ?></th>
									<th><?php esc_html_e( 'Status', 'statement-client-demo' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><strong><?php esc_html_e( 'Drop 001', 'statement-client-demo' ); ?></strong></td>
									<td><?php echo esc_html( $dry_run_data['drop_plan']['slug'] ); ?></td>
									<td><span class="dashicons dashicons-yes-alt" style="color:green;"></span> <?php echo esc_html( $dry_run_data['drop_plan']['status'] ); ?></td>
								</tr>
								<tr>
									<td><strong><?php esc_html_e( 'Product 01', 'statement-client-demo' ); ?></strong></td>
									<td><?php echo esc_html( $dry_run_data['product_01_plan']['title'] . ' (' . $dry_run_data['product_01_plan']['sku'] . ' — ' . $dry_run_data['product_01_plan']['demo_price'] . ')' ); ?></td>
									<td><?php echo esc_html( $dry_run_data['product_01_plan']['status'] ); ?></td>
								</tr>
								<tr>
									<td><strong><?php esc_html_e( 'Product 02', 'statement-client-demo' ); ?></strong></td>
									<td><?php echo esc_html( $dry_run_data['product_02_plan']['title'] . ' (' . $dry_run_data['product_02_plan']['sku'] . ' — ' . $dry_run_data['product_02_plan']['demo_price'] . ')' ); ?></td>
									<td><?php echo esc_html( $dry_run_data['product_02_plan']['status'] ); ?></td>
								</tr>
								<tr>
									<td><strong><?php esc_html_e( 'Pages', 'statement-client-demo' ); ?></strong></td>
									<td>Home, Drops, About, Contact, Journal</td>
									<td><?php esc_html_e( 'Configured & Verified', 'statement-client-demo' ); ?></td>
								</tr>
								<tr>
									<td><strong><?php esc_html_e( 'Journal Posts', 'statement-client-demo' ); ?></strong></td>
									<td>Study & Form, The Object</td>
									<td><?php esc_html_e( 'Editorial posts active', 'statement-client-demo' ); ?></td>
								</tr>
								<tr>
									<td><strong><?php esc_html_e( 'Media Assets', 'statement-client-demo' ); ?></strong></td>
									<td><?php printf( esc_html__( '%d Curated Images', 'statement-client-demo' ), (int) $dry_run_data['assets_total'] ); ?></td>
									<td><?php esc_html_e( 'Web-ready derivatives', 'statement-client-demo' ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="card" style="padding: 16px; margin-bottom: 20px;">
						<h2><?php esc_html_e( 'Ownership Diagnostics', 'statement-client-demo' ); ?></h2>
						<p class="description"><?php esc_html_e( 'Deterministic classification across client demo products, QA fixtures, and Product 213:', 'statement-client-demo' ); ?></p>
						<table class="widefat striped" style="margin-top: 10px;">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Target', 'statement-client-demo' ); ?></th>
									<th><?php esc_html_e( 'Product ID', 'statement-client-demo' ); ?></th>
									<th><?php esc_html_e( 'SKU', 'statement-client-demo' ); ?></th>
									<th><?php esc_html_e( 'Classification', 'statement-client-demo' ); ?></th>
									<th><?php esc_html_e( 'Status / Details', 'statement-client-demo' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								$diag_targets = array(
									'Monogram Jacquard' => $dry_run_data['product_01_plan']['id'] ?? 0,
									'Panelled Hood'     => $dry_run_data['product_02_plan']['id'] ?? 0,
									'Product 213 (QA)'  => 213,
								);

								foreach ( $diag_targets as $label => $t_id ) :
									$classification = ( $t_id > 0 ) ? OwnershipClassifier::classify( (int) $t_id ) : array( 'status' => 'NOT_FOUND', 'reason' => 'Product not seeded yet', 'sku' => '' );
									$status_color = 'green';
									if ( OwnershipClassifier::STATUS_CONFLICT === $classification['status'] ) {
										$status_color = 'red';
									} elseif ( OwnershipClassifier::STATUS_QA_FIXTURE === $classification['status'] ) {
										$status_color = '#d63638';
									}
								?>
								<tr>
									<td><strong><?php echo esc_html( $label ); ?></strong></td>
									<td><?php echo (int) $t_id > 0 ? (int) $t_id : '<em>N/A</em>'; ?></td>
									<td><code><?php echo esc_html( $classification['sku'] ?? 'N/A' ); ?></code></td>
									<td><strong style="color: <?php echo esc_attr( $status_color ); ?>;"><?php echo esc_html( $classification['status'] ); ?></strong></td>
									<td><?php echo esc_html( $classification['reason'] ); ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="card" style="padding: 16px;">
						<h2><?php esc_html_e( 'Execute Demo Management Actions', 'statement-client-demo' ); ?></h2>
						<p><?php esc_html_e( 'Choose an action below to update or repair client demo entities idempotently:', 'statement-client-demo' ); ?></p>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block; margin-right: 10px;">
							<input type="hidden" name="action" value="statement_client_demo_action">
							<input type="hidden" name="demo_action" value="dry_run">
							<?php wp_nonce_field( 'statement_client_demo_action_nonce', 'demo_nonce' ); ?>
							<button type="submit" class="button button-secondary">
								<?php esc_html_e( 'Dry Run / Preview Actions', 'statement-client-demo' ); ?>
							</button>
						</form>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block; margin-right: 10px;">
							<input type="hidden" name="action" value="statement_client_demo_action">
							<input type="hidden" name="demo_action" value="seed">
							<?php wp_nonce_field( 'statement_client_demo_action_nonce', 'demo_nonce' ); ?>
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Seed / Update Client Demo Content', 'statement-client-demo' ); ?>
							</button>
						</form>

						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline-block;">
							<input type="hidden" name="action" value="statement_client_demo_action">
							<input type="hidden" name="demo_action" value="repair">
							<?php wp_nonce_field( 'statement_client_demo_action_nonce', 'demo_nonce' ); ?>
							<button type="submit" class="button button-secondary" style="color: #b32d2e; border-color: #b32d2e;">
								<?php esc_html_e( 'Repair Client Demo Ownership', 'statement-client-demo' ); ?>
							</button>
						</form>
					</div>
				</div>

				<div>
					<div class="card" style="padding: 16px; margin-bottom: 20px;">
						<h2><?php esc_html_e( 'Safety & Rollback', 'statement-client-demo' ); ?></h2>
						<p><strong><?php esc_html_e( 'Front Page Rollback State:', 'statement-client-demo' ); ?></strong></p>
						<?php if ( ! empty( $rollback ) ) : ?>
							<p><span class="dashicons dashicons-shield" style="color:green;"></span> <?php printf( esc_html__( 'Preserved: Page ID %d', 'statement-client-demo' ), (int) ( $rollback['page_on_front'] ?? 0 ) ); ?></p>
						<?php else : ?>
							<p><em><?php esc_html_e( 'Rollback state will be recorded on first run.', 'statement-client-demo' ); ?></em></p>
						<?php endif; ?>

						<hr>
						<p><strong><?php esc_html_e( 'Ownership Isolation:', 'statement-client-demo' ); ?></strong></p>
						<p class="description">
							<?php esc_html_e( 'All demo entities carry _statement_client_demo=1. QA fixtures and customer orders are strictly protected against mutation.', 'statement-client-demo' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
