<?php

defined( 'ABSPATH' ) || exit;

$drop = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
if ( null === $drop || ! isset( $drop->name ) || '' === trim( (string) $drop->name ) ) {
	return;
}

$products    = isset( $args['products'] ) && is_array( $args['products'] ) ? array_slice( $args['products'], 0, 2 ) : array();
$drop_url    = isset( $args['drop_url'] ) && is_string( $args['drop_url'] ) && '' !== $args['drop_url'] ? $args['drop_url'] : null;
$description = isset( $drop->description ) && is_string( $drop->description ) ? trim( $drop->description ) : '';
?>
<section class="statement-home-drop statement-container--wide" aria-labelledby="statement-home-drop-title">
	<header class="statement-home-drop__header">
		<span class="statement-eyebrow"><?php esc_html_e( 'CURRENT RELEASE', 'statement-collector-theme' ); ?></span>
		<h2 class="statement-home-drop__title" id="statement-home-drop-title"><?php echo esc_html( $drop->name ); ?></h2>
		<?php if ( '' !== $description ) : ?>
			<div class="statement-home-drop__description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
		<?php endif; ?>

		<?php if ( null !== $drop_url ) : ?>
			<a class="statement-home-drop__link" href="<?php echo esc_url( $drop_url ); ?>">
				<span><?php esc_html_e( 'VIEW DROP', 'statement-collector-theme' ); ?></span>
				<span aria-hidden="true">&rarr;</span>
			</a>
		<?php endif; ?>
	</header>

	<?php if ( ! empty( $products ) ) : ?>
		<div class="statement-home-drop__products-grid">
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
	<?php endif; ?>
</section>
