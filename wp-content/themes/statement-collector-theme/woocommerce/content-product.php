<?php
/**
 * Minimal Statement product-loop presentation.
 *
 * Upstream: WooCommerce templates/content-product.php.
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, 'WC_Product' ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'statement-catalog__item', $product ); ?>>
	<?php
	get_template_part(
		'template-parts/product/card',
		null,
		array(
			'product'       => $product,
			'heading_level' => 2,
		)
	);
	?>
</li>
