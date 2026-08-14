<?php
/**
 * Native WooCommerce product gallery boundary.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="statement-product__gallery" aria-label="<?php esc_attr_e( 'Product imagery', 'statement-collector-theme' ); ?>">
	<?php woocommerce_show_product_images(); ?>
</section>
