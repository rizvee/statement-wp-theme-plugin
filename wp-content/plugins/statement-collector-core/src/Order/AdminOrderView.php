<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Displays read-only Statement Provenance on WooCommerce admin order item screens.
 */
final class AdminOrderView {
	/** @var bool */
	private static $booted = false;

	/**
	 * Register admin order item display hooks once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'woocommerce_after_order_itemmeta', array( self::class, 'render_item_provenance' ), 10, 3 );
	}

	/**
	 * Renders read-only Statement Provenance for an order item if captured.
	 *
	 * @param int    $item_id Order item ID.
	 * @param object $item    WooCommerce order line item.
	 * @param object $product WooCommerce product.
	 */
	public static function render_item_provenance( int $item_id, $item, $product ): void {
		unset( $item_id, $product );

		if ( ! Provenance::is_captured( $item ) ) {
			return;
		}

		$provenance = Provenance::get_provenance( $item );
		if ( empty( $provenance ) ) {
			return;
		}

		?>
		<div class="statement-provenance-admin-box" style="margin-top: 8px; padding: 8px 12px; background: #f8f9fa; border-left: 3px solid #111; font-size: 12px; color: #333;">
			<strong style="display: block; margin-bottom: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px;"><?php esc_html_e( 'Statement Provenance', 'statement-collector-core' ); ?></strong>
			<ul style="margin: 0; padding-0; list-style: none; line-height: 1.5;">
				<?php if ( ! empty( $provenance['drop_name'] ) ) : ?>
					<li><strong><?php esc_html_e( 'Drop:', 'statement-collector-core' ); ?></strong> <?php echo esc_html( $provenance['drop_name'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $provenance['edition_label'] ) ) : ?>
					<li><strong><?php esc_html_e( 'Edition:', 'statement-collector-core' ); ?></strong> <?php echo esc_html( $provenance['edition_label'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $provenance['release_state'] ) ) : ?>
					<li><strong><?php esc_html_e( 'State at Purchase:', 'statement-collector-core' ); ?></strong> <?php echo esc_html( $provenance['release_state'] ); ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $provenance['purchased_at'] ) ) : ?>
					<li><strong><?php esc_html_e( 'Purchased At:', 'statement-collector-core' ); ?></strong> <?php echo esc_html( $provenance['purchased_at'] ); ?></li>
				<?php endif; ?>
				<li><small style="color: #666;"><?php esc_html_e( 'Schema Version:', 'statement-collector-core' ); ?> <?php echo esc_html( (string) $provenance['version'] ); ?></small></li>
			</ul>
		</div>
		<?php
	}
}
