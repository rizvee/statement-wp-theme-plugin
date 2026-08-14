<?php
/**
 * Product details from the authored WooCommerce long description.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_object( $product ) || ! method_exists( $product, 'get_description' ) ) {
	return;
}

$description = $product->get_description();
if ( '' === trim( $description ) ) {
	return;
}
?>
<section class="statement-product__details statement-reading-width" aria-labelledby="statement-product-details-title">
	<h2 id="statement-product-details-title"><?php esc_html_e( 'PRODUCT DETAILS', 'statement-collector-theme' ); ?></h2>
	<div class="statement-product__description">
		<?php echo wp_kses_post( wpautop( $description ) ); ?>
	</div>
</section>
