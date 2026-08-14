<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Admin UI for managing Statement Private Access grants under WooCommerce menu.
 */
final class AdminUi {
	public static function boot(): void {
		add_action( 'admin_menu', array( self::class, 'register_admin_menu' ) );
		add_action( 'admin_init', array( self::class, 'handle_admin_actions' ) );
	}

	public static function register_admin_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Statement Access Grants', 'statement-collector-core' ),
			__( 'Statement Access', 'statement-collector-core' ),
			'manage_woocommerce',
			'statement-access-grants',
			array( self::class, 'render_admin_page' )
		);
	}

	/**
	 * Masks an email address for privacy in admin views (e.g. user@example.com -> u***r@e***e.com).
	 */
	public static function mask_email( string $email ): string {
		$parts = explode( '@', $email );
		if ( count( $parts ) !== 2 ) {
			return '***@***';
		}

		$user  = $parts[0];
		$domain_parts = explode( '.', $parts[1] );

		$masked_user = strlen( $user ) <= 2 ? $user[0] . '***' : $user[0] . '***' . substr( $user, -1 );
		$masked_domain = strlen( $domain_parts[0] ) <= 2 ? $domain_parts[0][0] . '***' : $domain_parts[0][0] . '***' . substr( $domain_parts[0], -1 );
		$tld = $domain_parts[1] ?? 'com';

		return $masked_user . '@' . $masked_domain . '.' . $tld;
	}

	public static function handle_admin_actions(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['statement_admin_action'] ) ) {
			return;
		}

		check_admin_referer( 'statement_admin_grant_action' );
		global $wpdb;
		$now_ts = time();
		$action   = sanitize_text_field( wp_unslash( $_POST['statement_admin_action'] ) );
		$grant_id = (int) ( $_POST['grant_id'] ?? 0 );

		if ( 'revoke' === $action && $grant_id > 0 ) {
			GrantService::revoke_grant( $wpdb, $grant_id, 'admin_revoke', $now_ts );
			wp_safe_redirect( admin_url( 'admin.php?page=statement-access-grants&message=revoked' ) );
			exit;
		}

		if ( 'regrant' === $action && $grant_id > 0 ) {
			$grants_table = $wpdb->prefix . 'statement_access_grants';
			$grant = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$grants_table} WHERE id = %d", $grant_id ),
				ARRAY_A
			);

			if ( is_array( $grant ) ) {
				$drop_id = (int) $grant['drop_term_id'];
				$config  = DropConfig::get_config( $drop_id );
				if ( null !== $config && DropConfig::is_config_valid( $config, $now_ts ) ) {
					$payload = json_decode( (string) $grant['encrypted_email'], true ) ?? array();
					GrantService::create_admin_regrant(
						$wpdb,
						$drop_id,
						$grant['email_hash'],
						$payload,
						$config['duration_seconds'],
						$config['closes_at_ts'],
						$grant_id,
						$now_ts
					);
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=statement-access-grants&message=regranted' ) );
			exit;
		}
	}

	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		global $wpdb;
		$grants_table = $wpdb->prefix . 'statement_access_grants';
		$now_str = date( 'Y-m-d H:i:s' );

		$grants = $wpdb->get_results(
			"SELECT * FROM {$grants_table} ORDER BY id DESC LIMIT 50",
			ARRAY_A
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Statement Access Grants', 'statement-collector-core' ); ?></h1>

			<?php if ( isset( $_GET['message'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Action completed successfully.', 'statement-collector-core' ); ?></p>
				</div>
			<?php endif; ?>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'statement-collector-core' ); ?></th>
						<th><?php esc_html_e( 'Drop ID', 'statement-collector-core' ); ?></th>
						<th><?php esc_html_e( 'Masked Email', 'statement-collector-core' ); ?></th>
						<th><?php esc_html_e( 'Granted At', 'statement-collector-core' ); ?></th>
						<th><?php esc_html_e( 'Effective Expiry', 'statement-collector-core' ); ?></th>
						<th><?php esc_html_e( 'Status', 'statement-collector-core' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'statement-collector-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $grants ) ) : ?>
						<tr><td colspan="7"><?php esc_html_e( 'No access grants found.', 'statement-collector-core' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $grants as $g ) : ?>
							<?php
							$decrypted = Crypto::decrypt_email( $g['encrypted_email'] );
							$masked = $decrypted ? self::mask_email( $decrypted ) : '***@***';
							$is_revoked = ! empty( $g['revoked_at'] );
							$is_expired = $g['grant_expires_at'] <= $now_str;
							$status = $is_revoked ? 'Revoked' : ( $is_expired ? 'Expired' : 'Active' );
							?>
							<tr>
								<td><?php echo esc_html( (string) $g['id'] ); ?></td>
								<td><?php echo esc_html( (string) $g['drop_term_id'] ); ?></td>
								<td><code><?php echo esc_html( $masked ); ?></code></td>
								<td><?php echo esc_html( (string) $g['granted_at'] ); ?></td>
								<td><?php echo esc_html( (string) $g['grant_expires_at'] ); ?></td>
								<td><strong><?php echo esc_html( $status ); ?></strong></td>
								<td>
									<?php if ( 'Active' === $status ) : ?>
										<form method="post" style="display:inline;">
											<?php wp_nonce_field( 'statement_admin_grant_action' ); ?>
											<input type="hidden" name="statement_admin_action" value="revoke" />
											<input type="hidden" name="grant_id" value="<?php echo esc_attr( (string) $g['id'] ); ?>" />
											<button type="submit" class="button button-small button-link-delete" onclick="return confirm('Revoke this grant?');"><?php esc_html_e( 'Revoke', 'statement-collector-core' ); ?></button>
										</form>
									<?php else : ?>
										<form method="post" style="display:inline;">
											<?php wp_nonce_field( 'statement_admin_grant_action' ); ?>
											<input type="hidden" name="statement_admin_action" value="regrant" />
											<input type="hidden" name="grant_id" value="<?php echo esc_attr( (string) $g['id'] ); ?>" />
											<button type="submit" class="button button-small"><?php esc_html_e( 'Re-grant', 'statement-collector-core' ); ?></button>
										</form>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
