<?php

defined( 'ABSPATH' ) || exit;

$product = isset( $args['product'] ) && is_object( $args['product'] ) ? $args['product'] : null;
if (
	null === $product
	|| ! method_exists( $product, 'get_image_id' )
	|| ! method_exists( $product, 'get_image' )
	|| ! method_exists( $product, 'get_name' )
	|| ! method_exists( $product, 'get_permalink' )
	|| ! method_exists( $product, 'get_price_html' )
) {
	return;
}

$name      = trim( (string) $product->get_name() );
$permalink = $product->get_permalink();
if ( '' === $name || ! is_string( $permalink ) || '' === $permalink ) {
	return;
}

$drop = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
if ( null === $drop && class_exists( 'Statement\Collector\Core\PublicApi' ) ) {
	$drop = \Statement\Collector\Core\PublicApi::get_drop( $product );
}

$drop_name     = is_object( $drop ) && isset( $drop->name ) ? trim( (string) $drop->name ) : '';
$heading_level = isset( $args['heading_level'] ) && 2 === (int) $args['heading_level'] ? 2 : 3;
$image_html    = (int) $product->get_image_id() > 0
	? $product->get_image(
		'woocommerce_thumbnail',
		array(
			'class'   => 'statement-piece__image',
			'loading' => 'lazy',
		)
	)
	: '';
$price_html = $product->get_price_html();
?>
<article class="statement-piece">
	<a class="statement-piece__link" href="<?php echo esc_url( $permalink ); ?>">
		<span class="statement-piece__media">
			<?php if ( is_string( $image_html ) && '' !== $image_html ) : ?>
				<?php echo wp_kses_post( $image_html ); ?>
			<?php else : ?>
				<span class="statement-piece__empty" aria-hidden="true"></span>
			<?php endif; ?>
		</span>
		<?php if ( 2 === $heading_level ) : ?>
			<h2 class="statement-piece__name"><?php echo esc_html( $name ); ?></h2>
		<?php else : ?>
			<h3 class="statement-piece__name"><?php echo esc_html( $name ); ?></h3>
		<?php endif; ?>
	</a>

	<?php if ( '' !== $drop_name ) : ?>
		<p class="statement-piece__drop"><?php echo esc_html( $drop_name ); ?></p>
	<?php endif; ?>

	<?php if ( is_string( $price_html ) && '' !== $price_html ) : ?>
		<div class="statement-piece__price"><?php echo wp_kses_post( $price_html ); ?></div>
	<?php endif; ?>
</article>
