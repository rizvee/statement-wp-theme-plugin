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
$state          = $has_public_api ? PublicApi::get_release_state( $product ) : 'LIVE';
$is_terminal    = in_array( $state, array( 'SOLD_OUT', 'ARCHIVED' ), true );
?>
<section class="statement-product__summary" aria-labelledby="statement-product-title">
	<div class="statement-product__summary-inner statement-stack">
		<?php if ( '' !== $edition_label || ( is_object( $drop ) && isset( $drop->name ) ) ) : ?>
			<div class="statement-product__meta-row">
				<span class="statement-meta-code">
					<?php if ( '' !== $edition_label ) : ?>
						<?php echo esc_html( strtoupper( $edition_label ) ); ?>
					<?php elseif ( is_object( $drop ) && isset( $drop->name ) ) : ?>
						<?php echo esc_html( strtoupper( $drop->name ) ); ?>
					<?php endif; ?>
				</span>
			</div>
		<?php endif; ?>

		<h1 id="statement-product-title" class="statement-product__title"><?php echo esc_html( strtoupper( $product->get_name() ) ); ?></h1>
		<div class="statement-product__price"><?php woocommerce_template_single_price(); ?></div>

		<?php if ( '' !== trim( (string) $product->get_short_description() ) ) : ?>
			<div class="statement-product__excerpt"><?php woocommerce_template_single_excerpt(); ?></div>
		<?php endif; ?>

		<div class="statement-drop-divider--subtle"></div>

		<?php if ( $is_terminal ) : ?>
			<div class="statement-product__status-badge statement-status-badge--terminal">
				<span class="statement-badge statement-badge--<?php echo esc_attr( strtolower( $state ) ); ?>"><?php echo esc_html( 'SOLD_OUT' === $state ? __( 'SOLD OUT', 'statement-collector-theme' ) : __( 'ARCHIVED', 'statement-collector-theme' ) ); ?></span>
			</div>
		<?php else : ?>
			<div class="statement-product__purchase">
				<?php woocommerce_template_single_add_to_cart(); ?>
				<?php get_template_part( 'template-parts/product/size-guide' ); ?>
			</div>
		<?php endif; ?>

		<div class="statement-drop-divider--subtle"></div>

		<!-- Details & Specifications Accordions -->
		<?php get_template_part( 'template-parts/product/details' ); ?>
	</div>
</section>
