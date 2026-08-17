<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$page_id   = isset( $args['page_id'] ) ? absint( $args['page_id'] ) : 0;
$drop      = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
$drop_url  = isset( $args['drop_url'] ) && is_string( $args['drop_url'] ) && '' !== $args['drop_url'] ? $args['drop_url'] : home_url( '/drops/' );
$theme_uri = get_template_directory_uri();
$has_featured = ( $page_id > 0 && function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $page_id ) );
$featured_url = $has_featured ? get_the_post_thumbnail_url( $page_id, 'full' ) : '';

// Discover configured slides from Theme Customizer or curated defaults
$slides = array();

for ( $i = 1; $i <= 4; $i++ ) {
	$img_id    = get_theme_mod( "statement_hero_slide_{$i}_image", '' );
	$mob_id    = get_theme_mod( "statement_hero_slide_{$i}_mobile_image", '' );
	$eyebrow   = get_theme_mod( "statement_hero_slide_{$i}_eyebrow", '' );
	$heading   = get_theme_mod( "statement_hero_slide_{$i}_heading", '' );
	$link      = get_theme_mod( "statement_hero_slide_{$i}_link", '' );
	$cta       = get_theme_mod( "statement_hero_slide_{$i}_cta", '' );
	$focal     = get_theme_mod( "statement_hero_slide_{$i}_focal", 'center 25%' );

	$img_url = ( 1 === $i && ! empty( $featured_url ) && empty( $img_id ) ) ? $featured_url : '';
	$mob_url = '';

	if ( ! empty( $img_id ) && is_numeric( $img_id ) ) {
		$img_url = wp_get_attachment_image_url( (int) $img_id, 'full' );
	}
	if ( ! empty( $mob_id ) && is_numeric( $mob_id ) ) {
		$mob_url = wp_get_attachment_image_url( (int) $mob_id, 'full' );
	}

	if ( ! empty( $heading ) || ! empty( $img_url ) ) {
		$slides[] = array(
			'image'        => $img_url,
			'mobile_image' => $mob_url,
			'eyebrow'      => $eyebrow,
			'heading'      => $heading,
			'link'         => ! empty( $link ) ? $link : $drop_url,
			'cta'          => ! empty( $cta ) ? $cta : __( 'VIEW RELEASE', 'statement-collector-theme' ),
			'focal'        => $focal,
		);
	}
}

// Curated default slides if Customizer has not been populated
if ( empty( $slides ) ) {
	$slides = array(
		array(
			'image'        => $theme_uri . '/assets/images/statement-panelled-hood-jacket-front.jpg',
			'mobile_image' => '',
			'eyebrow'      => 'DROP 001',
			'heading'      => 'MONOGRAM STUDY',
			'link'         => $drop_url,
			'cta'          => __( 'EXPLORE RELEASE', 'statement-collector-theme' ),
			'focal'        => 'center 25%',
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-monogram-jacket-front.jpg',
			'mobile_image' => '',
			'eyebrow'      => 'PIECE 01',
			'heading'      => 'MONOGRAM JACQUARD',
			'link'         => home_url( '/shop/' ),
			'cta'          => __( 'VIEW PIECE', 'statement-collector-theme' ),
			'focal'        => 'center 25%',
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-panelled-hood-jacket-cathedral-front.jpg',
			'mobile_image' => '',
			'eyebrow'      => 'PIECE 02',
			'heading'      => 'PANELLED HOOD',
			'link'         => home_url( '/shop/' ),
			'cta'          => __( 'VIEW PIECE', 'statement-collector-theme' ),
			'focal'        => 'center 25%',
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-collector-dust-bag.jpg',
			'mobile_image' => '',
			'eyebrow'      => 'EDITION PROVENANCE',
			'heading'      => 'CRAFTED. NOT MASS MADE.',
			'link'         => home_url( '/about/' ),
			'cta'          => __( 'READ ABOUT', 'statement-collector-theme' ),
			'focal'        => 'center 45%',
		),
	);
}

$total_slides = count( $slides );
?>
<section class="statement-hero-slider"
		 role="region"
		 aria-roledescription="carousel"
		 aria-label="<?php esc_attr_e( 'Statement Campaign Releases', 'statement-collector-theme' ); ?>">

	<div class="statement-hero-slider__track">
		<?php foreach ( $slides as $idx => $slide ) : ?>
			<?php
			$is_first   = ( 0 === $idx );
			$slide_num  = $idx + 1;
			$aria_label = sprintf( __( 'Slide %1$d of %2$d: %3$s', 'statement-collector-theme' ), $slide_num, $total_slides, esc_attr( $slide['heading'] ) );
			?>
			<div class="statement-hero-slide <?php echo $is_first ? 'is-active' : ''; ?>"
				 role="group"
				 aria-roledescription="slide"
				 aria-label="<?php echo esc_attr( $aria_label ); ?>"
				 aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
				 tabindex="<?php echo $is_first ? '0' : '-1'; ?>">

				<div class="statement-hero-slide__media">
					<picture>
						<?php if ( ! empty( $slide['mobile_image'] ) ) : ?>
							<source media="(max-width: 640px)" srcset="<?php echo esc_url( $slide['mobile_image'] ); ?>">
						<?php endif; ?>
						<img src="<?php echo esc_url( $slide['image'] ); ?>"
							 alt="<?php echo esc_attr( $slide['heading'] . ( ! empty( $slide['eyebrow'] ) ? ' — ' . $slide['eyebrow'] : '' ) ); ?>"
							 class="statement-hero-slide__image"
							 loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
							 fetchpriority="<?php echo $is_first ? 'high' : 'auto'; ?>"
							 style="object-position: <?php echo esc_attr( $slide['focal'] ); ?>;" />
					</picture>
					<div class="statement-hero-slide__overlay" aria-hidden="true"></div>
				</div>

				<div class="statement-hero-slide__content statement-container--wide">
					<div class="statement-hero-slide__inner">
						<?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
							<p class="statement-eyebrow statement-hero-slide__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></p>
						<?php endif; ?>

						<h2 class="statement-hero-slide__heading">
							<?php echo esc_html( $slide['heading'] ); ?>
						</h2>

						<?php if ( ! empty( $slide['link'] ) && ! empty( $slide['cta'] ) ) : ?>
							<div class="statement-hero-slide__actions">
								<a class="statement-hero-slide__cta" href="<?php echo esc_url( $slide['link'] ); ?>">
									<span><?php echo esc_html( $slide['cta'] ); ?></span>
									<span aria-hidden="true" class="statement-hero-slide__arrow">&rarr;</span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $total_slides > 1 ) : ?>
		<div class="statement-hero-slider__navigation statement-container--wide" aria-label="<?php esc_attr_e( 'Carousel navigation controls', 'statement-collector-theme' ); ?>">
			<div class="statement-hero-slider__pagination" role="tablist" aria-label="<?php esc_attr_e( 'Slide indicators', 'statement-collector-theme' ); ?>">
				<?php for ( $i = 0; $i < $total_slides; $i++ ) : ?>
					<button type="button"
							class="statement-hero-slider__dot <?php echo ( 0 === $i ) ? 'is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo ( 0 === $i ) ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'statement-collector-theme' ), $i + 1 ) ); ?>">
					</button>
				<?php endfor; ?>
			</div>

			<div class="statement-hero-slider__counter">
				<span class="statement-hero-slider__counter-current">01</span>
				<span class="statement-hero-slider__counter-sep">/</span>
				<span class="statement-hero-slider__counter-total"><?php echo esc_html( str_pad( (string) $total_slides, 2, '0', STR_PAD_LEFT ) ); ?></span>
			</div>

			<div class="statement-hero-slider__controls">
				<button type="button"
						class="statement-hero-slider__control statement-hero-slider__control--prev"
						aria-label="<?php esc_attr_e( 'Previous slide', 'statement-collector-theme' ); ?>">
					<span aria-hidden="true">&larr;</span>
				</button>
				<button type="button"
						class="statement-hero-slider__control statement-hero-slider__control--next"
						aria-label="<?php esc_attr_e( 'Next slide', 'statement-collector-theme' ); ?>">
					<span aria-hidden="true">&rarr;</span>
				</button>
			</div>
		</div>

		<div class="statement-hero-slider__live screen-reader-text" aria-live="polite" aria-atomic="true"></div>
	<?php endif; ?>
</section>
