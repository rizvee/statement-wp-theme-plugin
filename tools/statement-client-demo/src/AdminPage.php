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
		<div class="wrap statement-client-demo-admin">
			<h1><?php esc_html_e( 'Statement — Client Demo Seeder & Importer', 'statement-client-demo' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'One-click creation and update of real Statement brand media, Drop 001, variable products (S/M/L), and light-first editorial pages.', 'statement-client-demo' ); ?>
			</p>

			<?php if ( isset( $_GET['seeded'] ) && is_array( $last_res ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php esc_html_e( 'Client Demo Content Successfully Seeded & Updated!', 'statement-client-demo' ); ?></strong></p>
					<ul>
						<li><?php printf( esc_html__( 'Media Items Imported / Verified: %d', 'statement-client-demo' ), count( $last_res['media'] ) ); ?></li>
						<li><?php printf( esc_html__( 'Drop ID: %d', 'statement-client-demo' ), (int) $last_res['drop_id'] ); ?></li>
						<li><?php printf( esc_html__( 'Products Seeded: %d', 'statement-client-demo' ), count( $last_res['products'] ) ); ?></li>
						<li><?php printf( esc_html__( 'Front Page Set to: Statement Home (ID: %d)', 'statement-client-demo' ), (int) $last_res['pages']['statement_home'] ); ?></li>
					</ul>
				</div>
			<?php endif; ?>

			<div style="display: flex; gap: 20px; margin-top: 20px;">
				<!-- Actions Card -->
				<div class="card" style="max-width: 450px; padding: 20px; flex: 1;">
					<h2><?php esc_html_e( 'Demo Store Actions', 'statement-client-demo' ); ?></h2>
					<p><?php esc_html_e( 'All generated entities receive internal markers (_statement_client_demo = 1, _statement_demo_price = 1).', 'statement-client-demo' ); ?></p>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom: 15px;">
						<?php wp_nonce_field( 'statement_client_demo_action_nonce', 'demo_nonce' ); ?>
						<input type="hidden" name="action" value="statement_client_demo_action" />
						<input type="hidden" name="demo_action" value="seed" />
						<button type="submit" class="button button-primary button-hero" style="width: 100%; text-align: center;">
							<?php esc_html_e( 'SEED / UPDATE CLIENT DEMO', 'statement-client-demo' ); ?>
						</button>
					</form>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'statement_client_demo_action_nonce', 'demo_nonce' ); ?>
						<input type="hidden" name="action" value="statement_client_demo_action" />
						<input type="hidden" name="demo_action" value="dry_run" />
						<button type="submit" class="button button-secondary" style="width: 100%; text-align: center;">
							<?php esc_html_e( 'RUN DRY RUN ANALYSIS', 'statement-client-demo' ); ?>
						</button>
					</form>
				</div>

				<!-- Rollback & Front Page Status -->
				<div class="card" style="max-width: 450px; padding: 20px; flex: 1;">
					<h2><?php esc_html_e( 'Front Page & Rollback Status', 'statement-client-demo' ); ?></h2>
					<table class="widefat striped">
						<tbody>
							<tr>
								<td><strong><?php esc_html_e( 'Current Front Page', 'statement-client-demo' ); ?></strong></td>
								<td><?php echo esc_html( $dry_run_data['front_page_plan']['current_setting'] ); ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Rollback Preserved', 'statement-client-demo' ); ?></strong></td>
								<td><?php echo ! empty( $rollback ) ? '<span style="color:green;">Yes (ID: ' . esc_html( $rollback['page_on_front'] ) . ')</span>' : 'None yet'; ?></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Active Manifest', 'statement-client-demo' ); ?></strong></td>
								<td><?php echo ! empty( $manifest ) ? 'v' . esc_html( $manifest['version'] ) . ' (' . esc_html( $manifest['updated_at'] ) . ')' : 'Not yet seeded'; ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Dry Run / Inventory Plan Table -->
			<div class="card" style="margin-top: 25px; padding: 20px;">
				<h2><?php esc_html_e( 'Client Demo Seeder Plan & Asset Manifest', 'statement-client-demo' ); ?></h2>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 25%;"><strong><?php esc_html_e( 'Entity', 'statement-client-demo' ); ?></strong></th>
							<th style="width: 35%;"><strong><?php esc_html_e( 'Target Details', 'statement-client-demo' ); ?></strong></th>
							<th style="width: 40%;"><strong><?php esc_html_e( 'Status / Action', 'statement-client-demo' ); ?></strong></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Drop 001', 'statement-client-demo' ); ?></strong></td>
							<td><?php echo esc_html( $dry_run_data['drop_plan']['name'] . ' (' . $dry_run_data['drop_plan']['slug'] . ')' ); ?></td>
							<td><?php echo esc_html( $dry_run_data['drop_plan']['status'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Product 01', 'statement-client-demo' ); ?></strong></td>
							<td><?php echo esc_html( $dry_run_data['product_01_plan']['title'] . ' — ' . $dry_run_data['product_01_plan']['demo_price'] . ' (' . $dry_run_data['product_01_plan']['sku'] . ')' ); ?></td>
							<td><?php echo esc_html( $dry_run_data['product_01_plan']['status'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Product 02', 'statement-client-demo' ); ?></strong></td>
							<td><?php echo esc_html( $dry_run_data['product_02_plan']['title'] . ' — ' . $dry_run_data['product_02_plan']['demo_price'] . ' (' . $dry_run_data['product_02_plan']['sku'] . ')' ); ?></td>
							<td><?php echo esc_html( $dry_run_data['product_02_plan']['status'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Statement Home Page', 'statement-client-demo' ); ?></strong></td>
							<td><?php esc_html_e( 'Page: /statement-home/ (Template: default)', 'statement-client-demo' ); ?></td>
							<td><?php echo esc_html( $dry_run_data['pages_plan']['statement_home'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Drops Index Page', 'statement-client-demo' ); ?></strong></td>
							<td><?php esc_html_e( 'Page: /drops/ (Template: page-drops.php)', 'statement-client-demo' ); ?></td>
							<td><?php echo esc_html( $dry_run_data['pages_plan']['drops'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Media Assets (18)', 'statement-client-demo' ); ?></strong></td>
							<td><?php esc_html_e( '18 curated real brand photographs, model shots, macros & logos', 'statement-client-demo' ); ?></td>
							<td><?php printf( esc_html__( '%d total assets defined in AssetRegistry', 'statement-client-demo' ), count( $dry_run_data['assets_plan'] ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
