<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Integrates frozen Statement provenance into WooCommerce customer transactional emails.
 */
final class EmailIntegration {
	/** @var bool */
	private static $booted = false;

	/**
	 * Boot email integration hooks once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'woocommerce_order_item_meta_end', array( self::class, 'render_email_item_provenance' ), 10, 4 );
	}

	/**
	 * Renders frozen Statement provenance metadata in WooCommerce transactional email order item rows.
	 *
	 * @param int    $item_id Order item ID.
	 * @param object $item    WooCommerce order line item.
	 * @param object $order   WooCommerce order object.
	 * @param bool   $plain   Whether plain text output is requested.
	 */
	public static function render_email_item_provenance( int $item_id, $item, $order, bool $plain = false ): void {
		unset( $item_id, $order );

		if ( ! Provenance::is_captured( $item ) ) {
			return;
		}

		$provenance = Provenance::get_provenance( $item );
		if ( empty( $provenance ) ) {
			return;
		}

		if ( $plain ) {
			if ( ! empty( $provenance['drop_name'] ) ) {
				echo "\n  " . esc_html__( 'Drop:', 'statement-collector-core' ) . ' ' . esc_html( $provenance['drop_name'] );
			}
			if ( ! empty( $provenance['edition_label'] ) ) {
				echo "\n  " . esc_html__( 'Edition:', 'statement-collector-core' ) . ' ' . esc_html( $provenance['edition_label'] );
			}
			return;
		}

		?>
		<div class="statement-email-item-provenance" style="font-size: 12px; color: #666666; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-top: 4px; line-height: 1.4;">
			<?php if ( ! empty( $provenance['drop_name'] ) ) : ?>
				<div style="margin-bottom: 2px;"><strong><?php esc_html_e( 'Drop:', 'statement-collector-core' ); ?></strong> <?php echo esc_html( $provenance['drop_name'] ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $provenance['edition_label'] ) ) : ?>
				<div><strong><?php esc_html_e( 'Edition:', 'statement-collector-core' ); ?></strong> <?php echo esc_html( $provenance['edition_label'] ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}
}
