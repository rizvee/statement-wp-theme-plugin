<?php

namespace Statement\Collector\Core\Admin;

use Statement\Collector\Core\Product\Metadata;
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
	 * @param \WP_Post $post Current post object.
	 */
	public static function render_meta_box( $post ): void {
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return;
		}

		$product_id = (int) $post->ID;
		$product    = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		$state      = Metadata::get_release_state( $product );
		$stock_qty  = is_object( $product ) ? (int) $product->get_stock_quantity() : 0;
		$drop       = \Statement\Collector\Core\PublicApi::get_drop( $product );
		$drop_name  = is_object( $drop ) && isset( $drop->name ) ? $drop->name : __( 'None', 'statement-collector-core' );

		?>
		<div class="statement-lifecycle-box" style="padding: 6px 0;">
			<p>
				<strong><?php esc_html_e( 'Current Lifecycle State:', 'statement-collector-core' ); ?></strong><br>
				<span class="badge" style="display:inline-block; padding: 3px 8px; background: #23282d; color: #fff; border-radius: 3px; font-weight: 600; margin-top: 4px;">
					<?php echo esc_html( strtoupper( $state ) ); ?>
				</span>
			</p>
			<p>
				<strong><?php esc_html_e( 'Stock Quantity:', 'statement-collector-core' ); ?></strong>
				<span><?php echo esc_html( (string) $stock_qty ); ?></span>
			</p>
			<p>
				<strong><?php esc_html_e( 'Assigned Drop:', 'statement-collector-core' ); ?></strong>
				<span><?php echo esc_html( $drop_name ); ?></span>
			</p>
			<hr style="margin: 12px 0; border: 0; border-top: 1px solid #ddd;">

			<p><strong><?php esc_html_e( 'Privileged Lifecycle Overrides', 'statement-collector-core' ); ?></strong></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="statement_override_lifecycle">
				<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, 'statement_lifecycle_nonce' ); ?>

				<p>
					<label for="statement_target_state"><?php esc_html_e( 'Target State:', 'statement-collector-core' ); ?></label>
					<select name="target_state" id="statement_target_state" style="width: 100%; margin-top: 4px;">
						<option value=""><?php esc_html_e( '— Select Override State —', 'statement-collector-core' ); ?></option>
						<option value="<?php echo esc_attr( ReleaseState::LIVE ); ?>"><?php esc_html_e( 'LIVE (Reopen / Activate)', 'statement-collector-core' ); ?></option>
						<option value="<?php echo esc_attr( ReleaseState::PRIVATE_ACCESS ); ?>"><?php esc_html_e( 'PRIVATE_ACCESS', 'statement-collector-core' ); ?></option>
						<option value="<?php echo esc_attr( ReleaseState::SOLD_OUT ); ?>"><?php esc_html_e( 'SOLD_OUT', 'statement-collector-core' ); ?></option>
						<option value="<?php echo esc_attr( ReleaseState::ARCHIVED ); ?>"><?php esc_html_e( 'ARCHIVED', 'statement-collector-core' ); ?></option>
					</select>
				</p>

				<p>
					<label for="statement_override_reason"><?php esc_html_e( 'Reason / Audit Note:', 'statement-collector-core' ); ?></label>
					<input type="text" name="override_reason" id="statement_override_reason" style="width: 100%; margin-top: 4px;" placeholder="<?php esc_attr_e( 'e.g. Additional batch received', 'statement-collector-core' ); ?>" required>
				</p>

				<p>
					<label>
						<input type="checkbox" name="confirm_override" value="1" required>
						<?php esc_html_e( 'Confirm explicit state change', 'statement-collector-core' ); ?>
					</label>
				</p>

				<button type="submit" class="button button-secondary" style="width: 100%;">
					<?php esc_html_e( 'Apply Lifecycle Override', 'statement-collector-core' ); ?>
				</button>
			</form>
		</div>
		<?php
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

		if ( ! in_array( $target_state, array( ReleaseState::LIVE, ReleaseState::PRIVATE_ACCESS, ReleaseState::SOLD_OUT, ReleaseState::ARCHIVED ), true ) ) {
			wp_die( esc_html__( 'Invalid target release state.', 'statement-collector-core' ) );
		}

		if ( ! $confirmed || '' === trim( $reason ) ) {
			wp_die( esc_html__( 'Confirmation and reason are strictly required for lifecycle overrides.', 'statement-collector-core' ) );
		}

		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! is_object( $product ) ) {
			wp_die( esc_html__( 'Product not found.', 'statement-collector-core' ) );
		}

		$from_state   = Metadata::get_release_state( $product );
		$stock_before = (int) $product->get_stock_quantity();

		// Mutate product release state
		Metadata::set_release_state( $product, $target_state );
		$product->save();

		// Record audit log
		self::record_audit_event(
			array(
				'product_id'   => $product_id,
				'actor_id'     => get_current_user_id(),
				'from_state'   => $from_state,
				'to_state'     => $target_state,
				'reason'       => $reason,
				'stock_before' => $stock_before,
				'stock_after'  => (int) $product->get_stock_quantity(),
				'timestamp'    => gmdate( 'Y-m-d H:i:s' ),
			)
		);

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
				'event_id'  => wp_generate_uuid4(),
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
