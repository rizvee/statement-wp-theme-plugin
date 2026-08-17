<?php
/**
 * Statement Admin Setup & Health Screen (`Appearance -> Statement`).
 *
 * Provides a luxury dashboard displaying theme status, prerequisite checks,
 * safe setup triggers, and design settings import/export.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Admin;

defined( 'ABSPATH' ) || exit;

final class SetupScreen {
	const MENU_SLUG = 'statement-theme-setup';

	/**
	 * Boot admin menu hooks.
	 */
	public static function boot(): void {
		add_action( 'admin_menu', array( self::class, 'register_admin_menu' ) );
		add_action( 'admin_init', array( self::class, 'handle_form_actions' ) );
	}

	/**
	 * Register Appearance submenu page.
	 */
	public static function register_admin_menu(): void {
		add_theme_page(
			__( 'Statement Setup', 'statement-collector-theme' ),
			__( 'Statement', 'statement-collector-theme' ),
			'manage_options',
			self::MENU_SLUG,
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Handle export, import, reset, and setup actions.
	 */
	public static function handle_form_actions(): void {
		if ( ! isset( $_POST['statement_admin_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( OptionsExport::NONCE_ACTION, 'statement_admin_nonce' );

		$action = sanitize_key( wp_unslash( $_POST['statement_admin_action'] ) );

		// Export Action
		if ( 'export' === $action ) {
			$data = OptionsExport::export();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=statement-theme-settings-' . gmdate( 'Y-m-d' ) . '.json' );
			echo wp_json_encode( $data, JSON_PRETTY_PRINT );
			exit;
		}

		// Import Action
		if ( 'import' === $action && ! empty( $_FILES['statement_import_file']['tmp_name'] ) ) {
			$file_path = sanitize_text_field( wp_unslash( $_FILES['statement_import_file']['tmp_name'] ) );
			if ( is_uploaded_file( $file_path ) ) {
				$json = file_get_contents( $file_path );
				if ( $json ) {
					$result = OptionsExport::import( $json );
					$status = $result['success'] ? 'imported' : 'import_failed';
					wp_safe_redirect( add_query_arg( array( 'page' => self::MENU_SLUG, 'status' => $status ), admin_url( 'themes.php' ) ) );
					exit;
				}
			}
		}

		// Reset Action
		if ( 'reset' === $action ) {
			OptionsExport::reset_defaults();
			wp_safe_redirect( add_query_arg( array( 'page' => self::MENU_SLUG, 'status' => 'reset' ), admin_url( 'themes.php' ) ) );
			exit;
		}

		// Setup Missing Pages Action
		if ( 'create_missing_pages' === $action ) {
			self::create_standard_pages();
			wp_safe_redirect( add_query_arg( array( 'page' => self::MENU_SLUG, 'status' => 'pages_created' ), admin_url( 'themes.php' ) ) );
			exit;
		}
	}

	/**
	 * Create standard pages safely without overwriting existing content.
	 */
	private static function create_standard_pages(): void {
		$standard_pages = array(
			'about'   => array( 'title' => 'About', 'template' => 'page-about.php' ),
			'contact' => array( 'title' => 'Contact', 'template' => 'page-contact.php' ),
			'drops'   => array( 'title' => 'Drops', 'template' => 'page-drops.php' ),
			'archive' => array( 'title' => 'Archive', 'template' => 'page-archive.php' ),
		);

		foreach ( $standard_pages as $slug => $spec ) {
			$existing = get_page_by_path( $slug );
			if ( ! is_object( $existing ) ) {
				$new_id = wp_insert_post(
					array(
						'post_title'   => $spec['title'],
						'post_name'    => $slug,
						'post_status'  => 'publish',
						'post_type'    => 'page',
						'post_content' => '',
					)
				);
				if ( $new_id > 0 && ! empty( $spec['template'] ) ) {
					update_post_meta( $new_id, '_wp_page_template', $spec['template'] );
				}
			}
		}
	}

	/**
	 * Render the Setup & Health Screen.
	 */
	public static function render_page(): void {
		$checks = Health::audit();
		?>
		<div class="wrap statement-admin-wrap" style="max-width: 900px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
			<div style="background: #111111; color: #FFFFFF; padding: 24px 32px; border-radius: 4px; margin-bottom: 24px;">
				<span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; color: #888888;">Statement Collector's Piece</span>
				<h1 style="color: #FFFFFF; font-size: 24px; margin: 4px 0 8px 0; font-weight: 500; letter-spacing: -0.01em;">Theme Framework & System Health</h1>
				<p style="margin: 0; color: #CCCCCC; font-size: 14px;">Operational diagnostics, extension status, and design configuration management.</p>
			</div>

			<?php if ( isset( $_GET['status'] ) ) : ?>
				<?php $st = sanitize_key( wp_unslash( $_GET['status'] ) ); ?>
				<div class="notice notice-success is-dismissible" style="margin-bottom: 20px;">
					<p>
						<?php
						if ( 'imported' === $st ) {
							esc_html_e( 'Theme settings imported successfully.', 'statement-collector-theme' );
						} elseif ( 'reset' === $st ) {
							esc_html_e( 'Theme settings reset to defaults.', 'statement-collector-theme' );
						} elseif ( 'pages_created' === $st ) {
							esc_html_e( 'Standard Statement pages verified and created where missing.', 'statement-collector-theme' );
						}
						?>
					</p>
				</div>
			<?php endif; ?>

			<div style="background: #FFFFFF; border: 1px solid #E5E5E0; border-radius: 4px; padding: 24px; margin-bottom: 24px;">
				<h2 style="font-size: 16px; margin-top: 0; border-bottom: 1px solid #E5E5E0; padding-bottom: 12px;"><?php esc_html_e( 'System Status & Prerequisite Health', 'statement-collector-theme' ); ?></h2>
				<table class="widefat striped" style="border: none; margin-top: 12px;">
					<tbody>
						<?php foreach ( $checks as $key => $check ) : ?>
							<tr>
								<td style="font-weight: 500; width: 30%;"><?php echo esc_html( $check['label'] ); ?></td>
								<td style="color: #444444; width: 35%;"><?php echo esc_html( $check['value'] ); ?></td>
								<td style="width: 35%;">
									<?php if ( 'PASS' === $check['status'] ) : ?>
										<span style="background: #E6F4EA; color: #137333; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600;">PASS</span>
									<?php elseif ( 'REVIEW' === $check['status'] ) : ?>
										<span style="background: #FEF7E0; color: #B06000; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600;">REVIEW</span>
									<?php else : ?>
										<span style="background: #FCE8E6; color: #C5221F; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600;">ACTION</span>
									<?php endif; ?>
									<span style="font-size: 12px; color: #666666; margin-left: 8px;"><?php echo esc_html( $check['message'] ); ?></span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div style="background: #FFFFFF; border: 1px solid #E5E5E0; border-radius: 4px; padding: 24px; margin-bottom: 24px;">
				<h2 style="font-size: 16px; margin-top: 0; border-bottom: 1px solid #E5E5E0; padding-bottom: 12px;"><?php esc_html_e( 'Safe Setup Actions', 'statement-collector-theme' ); ?></h2>
				<p style="font-size: 13px; color: #666666;"><?php esc_html_e( 'Convenience actions will create missing standard pages (About, Contact, Drops, Archive) without altering existing content.', 'statement-collector-theme' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( OptionsExport::NONCE_ACTION, 'statement_admin_nonce' ); ?>
					<input type="hidden" name="statement_admin_action" value="create_missing_pages">
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Verify & Create Missing Standard Pages', 'statement-collector-theme' ); ?></button>
				</form>
			</div>

			<div style="background: #FFFFFF; border: 1px solid #E5E5E0; border-radius: 4px; padding: 24px;">
				<h2 style="font-size: 16px; margin-top: 0; border-bottom: 1px solid #E5E5E0; padding-bottom: 12px;"><?php esc_html_e( 'Design Settings Import / Export', 'statement-collector-theme' ); ?></h2>
				<p style="font-size: 13px; color: #666666;"><?php esc_html_e( 'Export or import your Statement Customizer design settings (colors, container widths, hero slider configuration) as a portable JSON file. Strictly excludes orders, customers, and business secrets.', 'statement-collector-theme' ); ?></p>

				<div style="display: flex; gap: 20px; margin-top: 16px;">
					<form method="post" action="">
						<?php wp_nonce_field( OptionsExport::NONCE_ACTION, 'statement_admin_nonce' ); ?>
						<input type="hidden" name="statement_admin_action" value="export">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Export Theme Settings (JSON)', 'statement-collector-theme' ); ?></button>
					</form>

					<form method="post" action="" enctype="multipart/form-data" style="display: flex; gap: 8px;">
						<?php wp_nonce_field( OptionsExport::NONCE_ACTION, 'statement_admin_nonce' ); ?>
						<input type="hidden" name="statement_admin_action" value="import">
						<input type="file" name="statement_import_file" accept=".json" required>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Import Settings JSON', 'statement-collector-theme' ); ?></button>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
}
