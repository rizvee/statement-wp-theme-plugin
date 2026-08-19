<?php
/**
 * Minimal Statement single-product composition.
 *
 * Upstream: WooCommerce templates/content-single-product.php.
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core form markup.
	return;
}
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'statement-product', $product ); ?>>
	<div class="statement-product__layout statement-container--wide">
		<?php get_template_part( 'template-parts/product/gallery' ); ?>
		<?php get_template_part( 'template-parts/product/summary' ); ?>
	</div>
</article>
<?php do_action( 'woocommerce_after_single_product' ); ?>
