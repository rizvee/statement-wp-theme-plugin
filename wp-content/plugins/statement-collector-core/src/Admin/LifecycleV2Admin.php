<?php

namespace Statement\Collector\Core\Admin;

use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\LifecycleOverrideService;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Admin controls for Inventory Lifecycle v2 explicit state overrides and audit logging.
 */
final class LifecycleV2Admin {
	public const OPTION_AUDIT_LOG = 'statement_lifecycle_audit_log';
	public const NONCE_ACTION     = 'statement_lifecycle_action';

	/** @var bool */
	private static $booted = false;

	/**
	 * Boot Lifecycle v2 admin hooks.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'admin_post_statement_override_lifecycle', array( self::class, 'handle_lifecycle_override' ) );
		add_action( 'add_meta_boxes', array( self::class, 'add_product_lifecycle_meta_box' ) );
		add_action( 'admin_notices', array( self::class, 'render_admin_notices' ) );
	}

	/**
	 * Render dismissible admin notices after lifecycle update.
	 */
	public static function render_admin_notices(): void {
		if ( ! empty( $_GET['statement_lifecycle_updated'] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e( 'Statement Release Lifecycle updated successfully.', 'statement-collector-core' ); ?></strong></p>
			</div>
			<?php
		}
	}

	/**
	 * Register product lifecycle meta box in WooCommerce product admin.
	 */
	public static function add_product_lifecycle_meta_box(): void {
		add_meta_box(
			'statement_product_lifecycle_v2',
			__( 'Statement Release Lifecycle & Overrides', 'statement-collector-core' ),
			array( self::class, 'render_meta_box' ),
			'product',
			'side',
			'high'
		);
	}

	/**
	 * Render lifecycle status and override controls in product editor.
	 *
	 * Note: NO nested <form> is used here. Normal product editing and save_post
	 * remain 100% decoupled from privileged lifecycle state overrides.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_meta_box( $post ): void {
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return;
		}

		$product_id = (int) $post->ID;
		$product    = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		$state      = Metadata::get_release_state( $product );
		$stock_qty  = class_exists( LifecycleOverrideService::class )
			? LifecycleOverrideService::calculate_product_stock( $product )
			: ( is_object( $product ) ? (int) $product->get_stock_quantity() : 0 );
		$drop       = class_exists( PublicApi::class ) ? PublicApi::get_drop( $product ) : null;
		$drop_name  = is_object( $drop ) && isset( $drop->name ) ? $drop->name : __( 'None', 'statement-collector-core' );

		// Resolve commerce status
		$commerce_status = __( 'Available', 'statement-collector-core' );
		if ( ReleaseState::ARCHIVED === $state ) {
			$commerce_status = __( 'Archived (Locked)', 'statement-collector-core' );
		} elseif ( ReleaseState::SOLD_OUT === $state ) {
			$commerce_status = __( 'Sold Out (Locked)', 'statement-collector-core' );
		} elseif ( ReleaseState::PRIVATE_ACCESS === $state ) {
			$commerce_status = __( 'Private Access Only', 'statement-collector-core' );
		} elseif ( ReleaseState::UPCOMING === $state ) {
			$commerce_status = __( 'Upcoming (Not Released)', 'statement-collector-core' );
		} elseif ( $stock_qty <= 0 ) {
			$commerce_status = __( 'Out of Stock', 'statement-collector-core' );
		}

		// Resolve context-aware allowed target states
		$allowed_options = self::get_context_allowed_options( $state );

		$nonce_value = function_exists( 'wp_create_nonce' ) ? wp_create_nonce( self::NONCE_ACTION ) : '';

		?>
		<div class="statement-lifecycle-box" style="padding: 4px 0;">
			<h4 style="margin: 0 0 10px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #1d2327;">
				<?php esc_html_e( 'STATEMENT RELEASE', 'statement-collector-core' ); ?>
			</h4>

			<table style="width: 100%; font-size: 12px; line-height: 1.6; border-collapse: collapse; margin-bottom: 8px;">
				<tr>
					<td style="padding: 2px 0; color: #646970; width: 45%;"><strong><?php esc_html_e( 'State:', 'statement-collector-core' ); ?></strong></td>
					<td style="padding: 2px 0;">
						<span class="badge" style="display:inline-block; padding: 2px 7px; background: #1d2327; color: #fff; border-radius: 3px; font-weight: 600; font-size: 11px;">
							<?php echo esc_html( strtoupper( $state ) ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<td style="padding: 2px 0; color: #646970;"><strong><?php esc_html_e( 'Drop:', 'statement-collector-core' ); ?></strong></td>
					<td style="padding: 2px 0; font-weight: 500;"><?php echo esc_html( $drop_name ); ?></td>
				</tr>
				<tr>
					<td style="padding: 2px 0; color: #646970;"><strong><?php esc_html_e( 'Commerce:', 'statement-collector-core' ); ?></strong></td>
					<td style="padding: 2px 0;"><?php echo esc_html( $commerce_status ); ?></td>
				</tr>
				<tr>
					<td style="padding: 2px 0; color: #646970;"><strong><?php esc_html_e( 'Woo Stock:', 'statement-collector-core' ); ?></strong></td>
					<td style="padding: 2px 0; font-weight: 600;"><?php echo esc_html( (string) $stock_qty ); ?></td>
				</tr>
			</table>

			<hr style="margin: 12px 0; border: 0; border-top: 1px solid #dcdcde;">

			<h4 style="margin: 0 0 6px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #1d2327;">
				<?php esc_html_e( 'RELEASE MANAGEMENT', 'statement-collector-core' ); ?>
			</h4>
			<p style="margin: 0 0 10px; font-size: 11px; color: #646970; font-style: italic;">
				<?php esc_html_e( 'Normal product editing above does not change the Statement release state.', 'statement-collector-core' ); ?>
			</p>

			<div style="background: #f6f7f7; border: 1px solid #c3c4c7; padding: 10px; border-radius: 4px; margin-top: 8px;">
				<p style="margin: 0 0 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #d63638;">
					<?php esc_html_e( 'PRIVILEGED OVERRIDE', 'statement-collector-core' ); ?>
				</p>

				<?php if ( ReleaseState::ARCHIVED === $state ) : ?>
					<div style="background: #fff8e5; border-left: 4px solid #dba617; padding: 6px 8px; margin-bottom: 10px; font-size: 11px; line-height: 1.4;">
						<strong><?php esc_html_e( 'Notice:', 'statement-collector-core' ); ?></strong>
						<?php esc_html_e( 'This piece is ARCHIVED. Reopening requires available inventory and a mandatory audit reason.', 'statement-collector-core' ); ?>
					</div>
				<?php endif; ?>

				<!-- Detached override inputs: NO nested form, NO required attribute blocking #post save -->
				<div class="statement-override-control-group">
					<p style="margin: 0 0 8px;">
						<label for="statement_target_state" style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">
							<?php esc_html_e( 'Change Release State:', 'statement-collector-core' ); ?>
						</label>
						<select id="statement_target_state" name="statement_target_state" style="width: 100%; max-width: 100%;">
							<option value=""><?php esc_html_e( '— Select Action —', 'statement-collector-core' ); ?></option>
							<?php foreach ( $allowed_options as $target_val => $label ) : ?>
								<option value="<?php echo esc_attr( $target_val ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<p style="margin: 0 0 8px;">
						<label for="statement_override_reason" style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">
							<?php esc_html_e( 'Reason / Audit Note:', 'statement-collector-core' ); ?>
						</label>
						<textarea id="statement_override_reason" name="statement_override_reason" rows="2" style="width: 100%; max-width: 100%; font-size: 12px;" placeholder="<?php esc_attr_e( 'e.g. Added inventory / editorial reopen', 'statement-collector-core' ); ?>"></textarea>
					</p>

					<p style="margin: 0 0 10px;">
						<label style="font-size: 11px; display: flex; align-items: flex-start; gap: 6px; cursor: pointer; color: #1d2327;">
							<input type="checkbox" id="statement_confirm_override" name="statement_confirm_override" value="1" style="margin-top: 2px;">
							<span><?php esc_html_e( 'I understand this changes release lifecycle', 'statement-collector-core' ); ?></span>
						</label>
					</p>

					<div id="statement_lifecycle_error_msg" style="display: none; background: #fcf0f1; border-left: 3px solid #d63638; padding: 6px 8px; margin-bottom: 8px; font-size: 11px; color: #d63638; line-height: 1.4;"></div>

					<button type="button" id="statement_apply_override_btn" class="button button-secondary" style="width: 100%; text-align: center;">
						<?php esc_html_e( 'APPLY RELEASE CHANGE', 'statement-collector-core' ); ?>
					</button>

					<input type="hidden" id="statement_lifecycle_product_id" value="<?php echo esc_attr( (string) $product_id ); ?>">
					<input type="hidden" id="statement_lifecycle_nonce" value="<?php echo esc_attr( $nonce_value ); ?>">
					<input type="hidden" id="statement_lifecycle_post_url" value="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				</div>
			</div>

			<script>
			(function() {
				var btn = document.getElementById('statement_apply_override_btn');
				if (!btn) return;

				btn.addEventListener('click', function(e) {
					e.preventDefault();
					var targetSelect = document.getElementById('statement_target_state');
					var reasonInput = document.getElementById('statement_override_reason');
					var confirmBox = document.getElementById('statement_confirm_override');
					var errorBox = document.getElementById('statement_lifecycle_error_msg');
					var productId = document.getElementById('statement_lifecycle_product_id');
					var nonce = document.getElementById('statement_lifecycle_nonce');
					var postUrl = document.getElementById('statement_lifecycle_post_url');

					if (errorBox) {
						errorBox.style.display = 'none';
						errorBox.textContent = '';
					}

					var targetState = targetSelect ? targetSelect.value.trim() : '';
					var reason = reasonInput ? reasonInput.value.trim() : '';
					var confirmed = confirmBox ? confirmBox.checked : false;

					if (!targetState) {
						if (errorBox) {
							errorBox.textContent = 'Please select a target action.';
							errorBox.style.display = 'block';
						}
						if (targetSelect) targetSelect.focus();
						return;
					}

					if (!reason) {
						if (errorBox) {
							errorBox.textContent = 'Please enter a reason/audit note for this override.';
							errorBox.style.display = 'block';
						}
						if (reasonInput) reasonInput.focus();
						return;
					}

					if (!confirmed) {
						if (errorBox) {
							errorBox.textContent = 'Please check the box to confirm this lifecycle change.';
							errorBox.style.display = 'block';
						}
						if (confirmBox) confirmBox.focus();
						return;
					}

					// Build and submit detached form directly to admin-post.php
					var form = document.createElement('form');
					form.method = 'POST';
					form.action = postUrl ? postUrl.value : 'admin-post.php';

					var fields = {
						'action': 'statement_override_lifecycle',
						'product_id': productId ? productId.value : '',
						'statement_lifecycle_nonce': nonce ? nonce.value : '',
						'target_state': targetState,
						'override_reason': reason,
						'confirm_override': '1'
					};

					for (var key in fields) {
						if (fields.hasOwnProperty(key)) {
							var hidden = document.createElement('input');
							hidden.type = 'hidden';
							hidden.name = key;
							hidden.value = fields[key];
							form.appendChild(hidden);
						}
					}

					btn.disabled = true;
					btn.textContent = 'Applying...';
					document.body.appendChild(form);
					form.submit();
				});
			})();
			</script>
		</div>
		<?php
	}

	/**
	 * Returns context-aware allowed target states.
	 *
	 * @param string $current_state Current canonical state.
	 * @return array<string, string> Keyed by ReleaseState value => UI label.
	 */
	public static function get_context_allowed_options( string $current_state ): array {
		switch ( $current_state ) {
			case ReleaseState::UPCOMING:
				return array(
					ReleaseState::LIVE           => __( 'Make LIVE (Activate Piece)', 'statement-collector-core' ),
					ReleaseState::PRIVATE_ACCESS => __( 'Set PRIVATE ACCESS (Drop Required)', 'statement-collector-core' ),
					ReleaseState::SOLD_OUT       => __( 'Mark SOLD OUT', 'statement-collector-core' ),
					ReleaseState::ARCHIVED       => __( 'Archive Piece', 'statement-collector-core' ),
				);
			case ReleaseState::PRIVATE_ACCESS:
				return array(
					ReleaseState::LIVE     => __( 'Make LIVE (Public Release)', 'statement-collector-core' ),
					ReleaseState::SOLD_OUT => __( 'Mark SOLD OUT', 'statement-collector-core' ),
					ReleaseState::ARCHIVED => __( 'Archive Piece', 'statement-collector-core' ),
				);
			case ReleaseState::LIVE:
				return array(
					ReleaseState::SOLD_OUT       => __( 'Mark SOLD OUT (Close Sales)', 'statement-collector-core' ),
					ReleaseState::PRIVATE_ACCESS => __( 'Set PRIVATE ACCESS (Drop Window Required)', 'statement-collector-core' ),
					ReleaseState::ARCHIVED       => __( 'Archive Piece', 'statement-collector-core' ),
				);
			case ReleaseState::SOLD_OUT:
				return array(
					ReleaseState::LIVE           => __( 'Reopen Release -> LIVE (Requires Stock > 0)', 'statement-collector-core' ),
					ReleaseState::ARCHIVED       => __( 'Archive Release -> ARCHIVED', 'statement-collector-core' ),
					ReleaseState::PRIVATE_ACCESS => __( 'Set PRIVATE ACCESS (Requires Drop + Stock > 0)', 'statement-collector-core' ),
				);
			case ReleaseState::ARCHIVED:
				return array(
					ReleaseState::LIVE           => __( 'Reopen Archived Release -> LIVE (Requires Stock > 0)', 'statement-collector-core' ),
					ReleaseState::PRIVATE_ACCESS => __( 'Set PRIVATE ACCESS (Requires Drop + Stock > 0)', 'statement-collector-core' ),
				);
			default:
				return array(
					ReleaseState::LIVE           => __( 'LIVE', 'statement-collector-core' ),
					ReleaseState::PRIVATE_ACCESS => __( 'PRIVATE_ACCESS', 'statement-collector-core' ),
					ReleaseState::SOLD_OUT       => __( 'SOLD_OUT', 'statement-collector-core' ),
					ReleaseState::ARCHIVED       => __( 'ARCHIVED', 'statement-collector-core' ),
				);
		}
	}

	/**
	 * Handle admin lifecycle override request.
	 */
	public static function handle_lifecycle_override(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Unauthorized capability.', 'statement-collector-core' ) );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$nonce      = isset( $_POST['statement_lifecycle_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['statement_lifecycle_nonce'] ) ) : '';

		if ( $product_id < 1 || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Invalid request or expired nonce.', 'statement-collector-core' ) );
		}

		$target_state = isset( $_POST['target_state'] ) ? sanitize_text_field( wp_unslash( $_POST['target_state'] ) ) : '';
		$reason       = isset( $_POST['override_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['override_reason'] ) ) : '';
		$confirmed    = ! empty( $_POST['confirm_override'] );

		if ( ! ReleaseState::is_valid( $target_state ) ) {
			wp_die( esc_html__( 'Invalid target release state.', 'statement-collector-core' ) );
		}

		if ( ! $confirmed || '' === trim( $reason ) ) {
			wp_die( esc_html__( 'Confirmation and reason are strictly required for lifecycle overrides.', 'statement-collector-core' ) );
		}

		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! is_object( $product ) ) {
			wp_die( esc_html__( 'Product not found.', 'statement-collector-core' ) );
		}

		$result = LifecycleOverrideService::override_state(
			$product,
			$target_state,
			get_current_user_id(),
			$reason
		);

		if ( ! ( $result['success'] ?? false ) ) {
			wp_die(
				esc_html( $result['error'] ?? __( 'Lifecycle override failed.', 'statement-collector-core' ) ),
				esc_html__( 'Lifecycle Override Error', 'statement-collector-core' ),
				array( 'back_link' => true )
			);
		}

		$redirect_url = get_edit_post_link( $product_id, 'raw' );
		wp_safe_redirect( add_query_arg( 'statement_lifecycle_updated', '1', $redirect_url ) );
		exit;
	}

	/**
	 * Record a structured audit event.
	 *
	 * @param array<string, mixed> $event Event payload.
	 */
	public static function record_audit_event( array $event ): void {
		$log   = get_option( self::OPTION_AUDIT_LOG, array() );
		$log   = is_array( $log ) ? $log : array();
		$log[] = array_merge(
			array(
				'event_id'  => function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : bin2hex( random_bytes( 16 ) ),
				'timestamp' => gmdate( 'Y-m-d H:i:s' ),
			),
			$event
		);

		if ( count( $log ) > 1000 ) {
			$log = array_slice( $log, -1000 );
		}

		update_option( self::OPTION_AUDIT_LOG, $log, false );
	}

	/**
	 * Retrieve audit log events.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_audit_log(): array {
		$log = get_option( self::OPTION_AUDIT_LOG, array() );
		return is_array( $log ) ? $log : array();
	}
}
