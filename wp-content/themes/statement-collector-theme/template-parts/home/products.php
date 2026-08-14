<?php

defined( 'ABSPATH' ) || exit;

$products  = isset( $args['products'] ) && is_array( $args['products'] ) ? array_slice( $args['products'], 0, 4 ) : array();
$drop      = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
$drop_name = null !== $drop && isset( $drop->name ) ? trim( (string) $drop->name ) : '';

if ( empty( $products ) ) {
	return;
}
?>
<section class="statement-home-products statement-container--wide" aria-labelledby="statement-home-products-title">
	<header class="statement-home-products__header">
		<h2 id="statement-home-products-title"><?php esc_html_e( 'Selected Pieces', 'statement-collector-theme' ); ?></h2>
	</header>

	<div class="statement-home-products__grid">
		<?php foreach ( $products as $product ) : ?>
			<?php
			if (
				! is_object( $product )
				|| ! method_exists( $product, 'get_image_id' )
				|| ! method_exists( $product, 'get_image' )
				|| ! method_exists( $product, 'get_name' )
				|| ! method_exists( $product, 'get_permalink' )
				|| ! method_exists( $product, 'get_price_html' )
			) {
				continue;
			}

			$name      = trim( (string) $product->get_name() );
			$permalink = $product->get_permalink();
			if ( '' === $name || ! is_string( $permalink ) || '' === $permalink ) {
				continue;
			}

			$image_html = (int) $product->get_image_id() > 0
				? $product->get_image(
					'woocommerce_thumbnail',
					array(
						'class'   => 'statement-home-piece__image',
						'loading' => 'lazy',
					)
				)
				: '';
			$price_html = $product->get_price_html();
			?>
			<article class="statement-home-piece">
				<a class="statement-home-piece__link" href="<?php echo esc_url( $permalink ); ?>">
					<span class="statement-home-piece__media">
						<?php if ( is_string( $image_html ) && '' !== $image_html ) : ?>
							<?php echo wp_kses_post( $image_html ); ?>
						<?php else : ?>
							<span class="statement-home-piece__empty" aria-hidden="true"></span>
						<?php endif; ?>
					</span>
					<h3 class="statement-home-piece__name"><?php echo esc_html( $name ); ?></h3>
				</a>

				<?php if ( '' !== $drop_name ) : ?>
					<p class="statement-home-piece__drop"><?php echo esc_html( $drop_name ); ?></p>
				<?php endif; ?>

				<?php if ( is_string( $price_html ) && '' !== $price_html ) : ?>
					<div class="statement-home-piece__price"><?php echo wp_kses_post( $price_html ); ?></div>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
</section>
