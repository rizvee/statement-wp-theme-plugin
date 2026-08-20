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
$gallery_ids   = method_exists( $product, 'get_gallery_image_ids' ) ? $product->get_gallery_image_ids() : array();
$secondary_img = '';
if ( ! empty( $gallery_ids ) ) {
	$sec_id = reset( $gallery_ids );
	if ( (int) $sec_id > 0 ) {
		$secondary_img = wp_get_attachment_image(
			(int) $sec_id,
			'large',
			false,
			array(
				'class'   => 'statement-piece__image statement-piece__image--secondary',
				'loading' => 'lazy',
			)
		);
	}
}

$image_html = (int) $product->get_image_id() > 0
	? $product->get_image(
		'large',
		array(
			'class'   => 'statement-piece__image statement-piece__image--primary',
			'loading' => 'lazy',
		)
	)
	: '';
$edition_label = class_exists( 'Statement\Collector\Core\PublicApi' )
	? \Statement\Collector\Core\PublicApi::get_edition_label( $product )
	: '';
$price_html = $product->get_price_html();
$state = class_exists( 'Statement\Collector\Core\PublicApi' )
	? \Statement\Collector\Core\PublicApi::get_release_state( $product )
	: 'LIVE';
?>
<article class="statement-piece">
	<a class="statement-piece__link" href="<?php echo esc_url( $permalink ); ?>">
		<span class="statement-piece__media">
			<?php if ( is_string( $image_html ) && '' !== $image_html ) : ?>
				<?php echo wp_kses_post( $image_html ); ?>
				<?php if ( is_string( $secondary_img ) && '' !== $secondary_img ) : ?>
					<?php echo wp_kses_post( $secondary_img ); ?>
				<?php endif; ?>
			<?php else : ?>
				<span class="statement-piece__empty" aria-hidden="true"></span>
			<?php endif; ?>
			<?php if ( in_array( $state, array( 'SOLD_OUT', 'ARCHIVED' ), true ) ) : ?>
				<span class="statement-piece__status-tag">
					<span class="statement-badge statement-badge--<?php echo esc_attr( strtolower( $state ) ); ?>"><?php echo esc_html( 'SOLD_OUT' === $state ? __( 'SOLD OUT', 'statement-collector-theme' ) : __( 'ARCHIVED', 'statement-collector-theme' ) ); ?></span>
				</span>
			<?php endif; ?>
		</span>
		<div class="statement-piece__meta">
			<div class="statement-piece__header">
				<?php if ( 2 === $heading_level ) : ?>
					<h2 class="statement-piece__name"><?php echo esc_html( $name ); ?></h2>
				<?php else : ?>
					<h3 class="statement-piece__name"><?php echo esc_html( $name ); ?></h3>
				<?php endif; ?>
				<?php if ( is_string( $price_html ) && '' !== $price_html ) : ?>
					<span class="statement-piece__price"><?php echo wp_kses_post( $price_html ); ?></span>
				<?php endif; ?>
			</div>

			<div class="statement-piece__subtext">
				<?php if ( '' !== $edition_label ) : ?>
					<span class="statement-piece__edition"><?php echo esc_html( $edition_label ); ?></span>
				<?php elseif ( '' !== $drop_name ) : ?>
					<span class="statement-piece__drop"><?php echo esc_html( $drop_name ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</a>
</article>
