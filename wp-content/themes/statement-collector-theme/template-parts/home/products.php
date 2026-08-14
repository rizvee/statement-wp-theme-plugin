<?php

defined( 'ABSPATH' ) || exit;

$products = isset( $args['products'] ) && is_array( $args['products'] ) ? array_slice( $args['products'], 0, 4 ) : array();
$drop     = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;

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
			get_template_part(
				'template-parts/product/card',
				null,
				array(
					'product'       => $product,
					'drop'          => $drop,
					'heading_level' => 3,
				)
			);
			?>
		<?php endforeach; ?>
	</div>
</section>
