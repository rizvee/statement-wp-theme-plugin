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
		$stock_qty  = class_exists( LifecycleOverrideService::class )
			? LifecycleOverrideService::calculate_product_stock( $product )
			: ( is_object( $product ) ? (int) $product->get_stock_quantity() : 0 );
		$drop       = class_exists( PublicApi::class ) ? PublicApi::get_drop( $product ) : null;
		$drop_name  = is_object( $drop ) && isset( $drop->name ) ? $drop->name : __( 'None', 'statement-collector-core' );

		// Resolve context-aware allowed target states
		$allowed_options = self::get_context_allowed_options( $state );

		?>
		<div class="statement-lifecycle-box" style="padding: 6px 0;">
			<p>
				<strong><?php esc_html_e( 'Current Lifecycle State:', 'statement-collector-core' ); ?></strong><br>
				<span class="badge" style="display:inline-block; padding: 3px 8px; background: #23282d; color: #fff; border-radius: 3px; font-weight: 600; margin-top: 4px;">
					<?php echo esc_html( strtoupper( $state ) ); ?>
				</span>
			</p>
			<p>
				<strong><?php esc_html_e( 'Available Stock:', 'statement-collector-core' ); ?></strong>
				<span><?php echo esc_html( (string) $stock_qty ); ?></span>
			</p>
			<p>
				<strong><?php esc_html_e( 'Assigned Drop:', 'statement-collector-core' ); ?></strong>
				<span><?php echo esc_html( $drop_name ); ?></span>
			</p>
			<hr style="margin: 12px 0; border: 0; border-top: 1px solid #ddd;">

			<p><strong><?php esc_html_e( 'Privileged Lifecycle Override', 'statement-collector-core' ); ?></strong></p>

			<?php if ( ReleaseState::ARCHIVED === $state ) : ?>
				<div style="background: #fff8e5; border-left: 4px solid #ffb900; padding: 8px; margin-bottom: 10px; font-size: 12px;">
					<strong><?php esc_html_e( 'Caution:', 'statement-collector-core' ); ?></strong>
					<?php esc_html_e( 'This piece is ARCHIVED. Reopening is a high-impact historical action requiring available inventory and a mandatory reason.', 'statement-collector-core' ); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="statement_override_lifecycle">
				<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION, 'statement_lifecycle_nonce' ); ?>

				<p>
					<label for="statement_target_state"><strong><?php esc_html_e( 'Target Action:', 'statement-collector-core' ); ?></strong></label>
					<select name="target_state" id="statement_target_state" style="width: 100%; margin-top: 4px;" required>
						<option value=""><?php esc_html_e( '— Select Action —', 'statement-collector-core' ); ?></option>
						<?php foreach ( $allowed_options as $target_val => $label ) : ?>
							<option value="<?php echo esc_attr( $target_val ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p>
					<label for="statement_override_reason"><strong><?php esc_html_e( 'Reason / Audit Note:', 'statement-collector-core' ); ?></strong></label>
					<input type="text" name="override_reason" id="statement_override_reason" style="width: 100%; margin-top: 4px;" placeholder="<?php esc_attr_e( 'e.g. Added inventory / editorial reopen', 'statement-collector-core' ); ?>" required>
				</p>

				<p>
					<label style="font-size: 12px; display: block; margin-top: 8px;">
						<input type="checkbox" name="confirm_override" value="1" required>
						<?php esc_html_e( 'Confirm explicit state override', 'statement-collector-core' ); ?>
					</label>
				</p>

				<button type="submit" class="button button-primary" style="width: 100%; margin-top: 6px;">
					<?php esc_html_e( 'Apply Override', 'statement-collector-core' ); ?>
				</button>
			</form>
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
