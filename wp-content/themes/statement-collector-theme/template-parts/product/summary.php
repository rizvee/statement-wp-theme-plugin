<?php
/**
 * Statement product purchase summary using native WooCommerce mechanics.
 */

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_object( $product ) ) {
	return;
}

$has_public_api = class_exists( PublicApi::class );
$drop           = $has_public_api ? PublicApi::get_drop( $product ) : null;
$edition_label  = $has_public_api ? PublicApi::get_edition_label( $product ) : '';
?>
<section class="statement-product__summary" aria-labelledby="statement-product-title">
	<div class="statement-product__summary-inner statement-stack">
		<?php if ( is_object( $drop ) || '' !== $edition_label ) : ?>
			<p class="statement-product__provenance">
				<?php if ( is_object( $drop ) && isset( $drop->name ) ) : ?>
					<span><?php echo esc_html( $drop->name ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $edition_label ) : ?>
					<span><?php echo esc_html( $edition_label ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<h1 id="statement-product-title" class="statement-product__title"><?php echo esc_html( $product->get_name() ); ?></h1>
		<div class="statement-product__price"><?php woocommerce_template_single_price(); ?></div>
		<div class="statement-product__excerpt"><?php woocommerce_template_single_excerpt(); ?></div>
		<div class="statement-product__purchase"><?php woocommerce_template_single_add_to_cart(); ?></div>
	</div>
</section>
