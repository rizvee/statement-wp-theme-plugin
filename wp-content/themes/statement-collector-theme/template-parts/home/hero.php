<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$page_id   = isset( $args['page_id'] ) ? absint( $args['page_id'] ) : 0;
$drop      = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
$drop_url  = isset( $args['drop_url'] ) && is_string( $args['drop_url'] ) && '' !== $args['drop_url'] ? $args['drop_url'] : home_url( '/drops/' );
$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$theme_uri = get_template_directory_uri();
$has_featured = ( $page_id > 0 && function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $page_id ) );
$featured_url = $has_featured ? get_the_post_thumbnail_url( $page_id, 'full' ) : '';

// Discover configured slides from Theme Customizer or curated defaults
$slides = array();

for ( $i = 1; $i <= 6; $i++ ) {
	$img_id    = get_theme_mod( "statement_hero_slide_{$i}_image", '' );
	$mob_id    = get_theme_mod( "statement_hero_slide_{$i}_mobile_image", '' );
	$eyebrow   = get_theme_mod( "statement_hero_slide_{$i}_eyebrow", '' );
	$heading   = get_theme_mod( "statement_hero_slide_{$i}_heading", '' );
	$link      = get_theme_mod( "statement_hero_slide_{$i}_link", '' );
	$cta       = get_theme_mod( "statement_hero_slide_{$i}_cta", '' );
	$focal     = get_theme_mod( "statement_hero_slide_{$i}_focal", 'center 20%' );

	$img_url = ( 1 === $i && ! empty( $featured_url ) && empty( $img_id ) ) ? $featured_url : '';
	$mob_url = '';

	if ( ! empty( $img_id ) && is_numeric( $img_id ) ) {
		$img_url = wp_get_attachment_image_url( (int) $img_id, 'full' );
	}
	if ( ! empty( $mob_id ) && is_numeric( $mob_id ) ) {
		$mob_url = wp_get_attachment_image_url( (int) $mob_id, 'full' );
	}

	if ( ! empty( $img_url ) ) {
		$slides[] = array(
			'image'        => $img_url,
			'mobile_image' => $mob_url,
			'eyebrow'      => $eyebrow,
			'heading'      => $heading,
			'link'         => ! empty( $link ) ? $link : $shop_url,
			'cta'          => ! empty( $cta ) ? $cta : __( 'EXPLORE RELEASE', 'statement-collector-theme' ),
			'focal'        => $focal,
			'alt'          => ! empty( $heading ) ? $heading : __( 'Statement Editorial Campaign', 'statement-collector-theme' ),
		);
	}
}

// Curated default image-first slides using new client Drive photography
if ( empty( $slides ) ) {
	$slides = array(
		array(
			'image'        => $theme_uri . '/assets/images/statement-hero-slide-monogram-01.jpg',
			'mobile_image' => '',
			'eyebrow'      => '',
			'heading'      => '',
			'link'         => $shop_url,
			'cta'          => '',
			'focal'        => 'center 20%',
			'alt'          => __( 'Statement Monogram Jacquard Campaign Editorial', 'statement-collector-theme' ),
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-panelled-hood-jacket-front.jpg',
			'mobile_image' => '',
			'eyebrow'      => '',
			'heading'      => '',
			'link'         => $shop_url,
			'cta'          => '',
			'focal'        => 'center 20%',
			'alt'          => __( 'Statement Panelled Hood Jacket Studio Cut', 'statement-collector-theme' ),
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-monogram-jacket-front.jpg',
			'mobile_image' => '',
			'eyebrow'      => '',
			'heading'      => '',
			'link'         => $shop_url,
			'cta'          => '',
			'focal'        => 'center 20%',
			'alt'          => __( 'Statement Monogram Jacquard Jacket Studio Cut', 'statement-collector-theme' ),
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-hero-slide-hood-01.jpg',
			'mobile_image' => '',
			'eyebrow'      => '',
			'heading'      => '',
			'link'         => $drop_url,
			'cta'          => '',
			'focal'        => 'center 25%',
			'alt'          => __( 'Statement Panelled Hood Campaign Atmosphere', 'statement-collector-theme' ),
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-hero-slide-monogram-02.jpg',
			'mobile_image' => '',
			'eyebrow'      => '',
			'heading'      => '',
			'link'         => $shop_url,
			'cta'          => '',
			'focal'        => 'center 25%',
			'alt'          => __( 'Statement Architectural Lookbook Study', 'statement-collector-theme' ),
		),
	);
}

$total_slides = count( $slides );
?>
<section class="statement-hero-slider statement-hero-slider--image-first"
		 role="region"
		 aria-roledescription="carousel"
		 aria-label="<?php esc_attr_e( 'Statement Campaign Showcase', 'statement-collector-theme' ); ?>">

	<div class="statement-hero-slider__track">
		<?php foreach ( $slides as $idx => $slide ) : ?>
			<?php
			$is_first    = ( 0 === $idx );
			$slide_num   = $idx + 1;
			$has_content = ( ! empty( $slide['heading'] ) || ! empty( $slide['eyebrow'] ) || ( ! empty( $slide['link'] ) && ! empty( $slide['cta'] ) ) );
			$aria_label  = sprintf( __( 'Slide %1$d of %2$d', 'statement-collector-theme' ), $slide_num, $total_slides );
			if ( ! empty( $slide['heading'] ) ) {
				$aria_label .= ': ' . esc_attr( $slide['heading'] );
			}
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
							 alt="<?php echo esc_attr( $slide['alt'] ); ?>"
							 class="statement-hero-slide__image"
							 loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
							 fetchpriority="<?php echo $is_first ? 'high' : 'auto'; ?>"
							 style="object-position: <?php echo esc_attr( $slide['focal'] ); ?>;" />
					</picture>
					<div class="statement-hero-slide__overlay" aria-hidden="true"></div>
				</div>

				<?php if ( $has_content ) : ?>
					<div class="statement-hero-slide__content statement-container--wide">
						<div class="statement-hero-slide__inner">
							<?php if ( ! empty( $slide['eyebrow'] ) ) : ?>
								<p class="statement-eyebrow statement-hero-slide__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $slide['heading'] ) ) : ?>
								<h2 class="statement-hero-slide__heading">
									<?php echo esc_html( $slide['heading'] ); ?>
								</h2>
							<?php endif; ?>

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
				<?php endif; ?>
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
