<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Enhances WooCommerce customer order details and Order Received (Thank You) presentation with frozen Statement provenance.
 */
final class CustomerOrderView {
	/** @var bool */
	private static $booted = false;

	/**
	 * Boot customer order view hooks once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'woocommerce_thankyou', array( self::class, 'render_thankyou_header' ), 5 );
		add_action( 'woocommerce_order_item_meta_end', array( self::class, 'render_item_provenance_customer' ), 10, 3 );
	}

	/**
	 * Renders status-aware Statement banner on WooCommerce Order Received page.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function render_thankyou_header( $order_id ): void {
		$order_id = absint( $order_id );
		if ( $order_id < 1 || ! function_exists( 'wc_get_order' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! is_object( $order ) || ! method_exists( $order, 'get_status' ) ) {
			return;
		}

		$status = strtolower( trim( (string) $order->get_status() ) );
		$title  = __( 'Order Received', 'statement-collector-core' );
		$sub    = __( 'Thank you for your order.', 'statement-collector-core' );

		if ( Completion::is_commercially_completed( $order ) ) {
			$title = __( 'Order Confirmed', 'statement-collector-core' );
			$sub   = __( 'Your piece has been secured.', 'statement-collector-core' );
		} elseif ( 'pending' === $status ) {
			$title = __( 'Order Received', 'statement-collector-core' );
			$sub   = __( 'Your order has been received and is awaiting payment.', 'statement-collector-core' );
		} elseif ( 'on-hold' === $status ) {
			$title = __( 'Order Received', 'statement-collector-core' );
			$sub   = __( 'Your order has been received and is currently on hold.', 'statement-collector-core' );
		} elseif ( 'failed' === $status ) {
			$title = __( 'Payment Not Completed', 'statement-collector-core' );
			$sub   = __( 'Your payment attempt was not completed. Please review your order details.', 'statement-collector-core' );
		} elseif ( 'cancelled' === $status ) {
			$title = __( 'Order Cancelled', 'statement-collector-core' );
			$sub   = __( 'This order has been cancelled.', 'statement-collector-core' );
		} elseif ( 'refunded' === $status ) {
			$title = __( 'Order Refunded', 'statement-collector-core' );
			$sub   = __( 'This order has been refunded.', 'statement-collector-core' );
		}

		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : ( function_exists( 'home_url' ) ? home_url( '/' ) : '/' );

		?>
		<div class="statement-thankyou-hero statement-stack" style="margin-bottom: 2rem; padding: 2rem 0; border-bottom: 1px solid var(--wp--preset--color--neutral-200, #e0e0e0);">
			<h1 class="statement-thankyou-title" style="font-size: 1.75rem; font-weight: 400; margin-bottom: 0.5rem; letter-spacing: -0.02em;"><?php echo esc_html( $title ); ?></h1>
			<p class="statement-thankyou-subtitle" style="font-size: 1rem; color: var(--wp--preset--color--neutral-600, #666666); margin: 0;"><?php echo esc_html( $sub ); ?></p>
			<?php if ( '' !== $shop_url ) : ?>
				<div style="margin-top: 1rem;">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="statement-button statement-button--secondary" style="display: inline-block; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid currentColor;"><?php esc_html_e( 'Continue Exploring', 'statement-collector-core' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders frozen Statement provenance metadata in customer order details tables.
	 *
	 * @param int    $item_id Order item ID.
	 * @param object $item    WooCommerce order line item.
	 * @param object $order   WooCommerce order object.
	 */
	public static function render_item_provenance_customer( int $item_id, $item, $order ): void {
		unset( $item_id, $order );

		if ( ! Provenance::is_valid( $item ) ) {
			return;
		}

		$provenance = Provenance::get_provenance( $item );
		if ( empty( $provenance ) ) {
			return;
		}

		?>
		<div class="statement-item-provenance" style="font-size: 0.8125rem; color: #555; margin-top: 0.25rem;">
			<?php if ( ! empty( $provenance['drop_name'] ) ) : ?>
				<span class="statement-item-provenance__drop" style="display: block;"><?php esc_html_e( 'Drop:', 'statement-collector-core' ); ?> <?php echo esc_html( $provenance['drop_name'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $provenance['edition_label'] ) ) : ?>
				<span class="statement-item-provenance__edition" style="display: block;"><?php esc_html_e( 'Edition:', 'statement-collector-core' ); ?> <?php echo esc_html( $provenance['edition_label'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}
}
