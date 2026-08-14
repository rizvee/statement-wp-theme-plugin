<?php
/**
 * Checkout form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>
<section class="statement-checkout statement-container--wide">
	<h1 class="statement-checkout__title"><?php esc_html_e( 'CHECKOUT', 'statement-collector-theme' ); ?></h1>

	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
		<div class="statement-checkout__layout">
			<div class="statement-checkout__customer">
				<?php if ( $checkout->get_checkout_fields() ) : ?>
					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div id="customer_details">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
				<?php endif; ?>
			</div>

			<section class="statement-checkout__summary" aria-labelledby="statement-order-review-heading">
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

				<h2 id="statement-order-review-heading" class="statement-checkout__summary-title"><?php esc_html_e( 'ORDER SUMMARY', 'statement-collector-theme' ); ?></h2>

				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			</section>
		</div>
	</form>
</section>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
