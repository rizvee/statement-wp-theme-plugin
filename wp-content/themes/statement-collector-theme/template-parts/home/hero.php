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
	$mob_vid   = get_theme_mod( "statement_hero_slide_{$i}_mobile_video", '' );
	$poster_id = get_theme_mod( "statement_hero_slide_{$i}_video_poster", '' );
	$eyebrow   = get_theme_mod( "statement_hero_slide_{$i}_eyebrow", '' );
	$heading   = get_theme_mod( "statement_hero_slide_{$i}_heading", '' );
	$link      = get_theme_mod( "statement_hero_slide_{$i}_link", '' );
	$cta       = get_theme_mod( "statement_hero_slide_{$i}_cta", '' );
	$focal     = get_theme_mod( "statement_hero_slide_{$i}_focal", 'center 20%' );

	$img_url    = ( 1 === $i && ! empty( $featured_url ) && empty( $img_id ) ) ? $featured_url : '';
	$mob_url    = '';
	$poster_url = '';

	if ( ! empty( $img_id ) && is_numeric( $img_id ) ) {
		$img_url = wp_get_attachment_image_url( (int) $img_id, 'full' );
	}
	if ( ! empty( $mob_id ) && is_numeric( $mob_id ) ) {
		$mob_url = wp_get_attachment_image_url( (int) $mob_id, 'full' );
	}
	if ( ! empty( $poster_id ) && is_numeric( $poster_id ) ) {
		$poster_url = wp_get_attachment_image_url( (int) $poster_id, 'full' );
	}

	if ( ! empty( $img_url ) ) {
		$slides[] = array(
			'image'        => $img_url,
			'mobile_image' => $mob_url,
			'mobile_video' => $mob_vid,
			'poster'       => $poster_url,
			'eyebrow'      => $eyebrow,
			'heading'      => $heading,
			'link'         => ! empty( $link ) ? $link : $shop_url,
			'cta'          => ! empty( $cta ) ? $cta : __( 'EXPLORE RELEASE', 'statement-collector-theme' ),
			'focal'        => $focal,
			'alt'          => ! empty( $heading ) ? $heading : __( 'Statement Campaign Editorial', 'statement-collector-theme' ),
		);
	}
}

// Curated default high-fashion 3-chapter cinematic editorial slides
if ( empty( $slides ) ) {
	$slides = array(
		array(
			'image'        => $theme_uri . '/assets/images/statement-black-nwhite-hoodie-n-jacket-product-front.webp',
			'mobile_image' => $theme_uri . '/assets/images/statement-monogram-jacket-model-front.webp',
			'mobile_video' => $theme_uri . '/assets/video/statement-hero-mobile-monogram.mp4',
			'poster'       => $theme_uri . '/assets/images/statement-monogram-jacket-model-front.webp',
			'eyebrow'      => 'RELEASE DOSSIER / DROP 001',
			'heading'      => 'NOT MASS PRODUCED.',
			'link'         => $shop_url,
			'cta'          => __( 'EXPLORE PIECES', 'statement-collector-theme' ),
			'focal'        => 'center 35%',
			'alt'          => __( 'Statement Drop 001 Creative Genesis & Studio Concept', 'statement-collector-theme' ),
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-black-nwhite-hoodie-n-jacket-product-front-02.webp',
			'mobile_image' => $theme_uri . '/assets/images/statement-monogram-jacket-product-front-02.webp',
			'mobile_video' => '',
			'poster'       => '',
			'eyebrow'      => 'DROP 001 / MONOGRAM STUDY',
			'heading'      => 'MONOGRAM JACQUARD & PANELLED HOOD',
			'link'         => $drop_url,
			'cta'          => __( 'VIEW COLLECTION', 'statement-collector-theme' ),
			'focal'        => 'center 30%',
			'alt'          => __( 'Statement Monogram Jacquard Jacket & Panelled Hood Duo Editorial', 'statement-collector-theme' ),
		),
		array(
			'image'        => $theme_uri . '/assets/images/statement-black-nwhite-hoodie-n-jacket-product-front-03.webp',
			'mobile_image' => $theme_uri . '/assets/images/statement-panelled-hood-jacket-model-front.webp',
			'mobile_video' => '',
			'poster'       => '',
			'eyebrow'      => 'EDITION 001 / RELIC STUDY',
			'heading'      => 'CRAFTED. LIMITED. NEVER RESTOCKED.',
			'link'         => $shop_url,
			'cta'          => __( 'DISCOVER PIECES', 'statement-collector-theme' ),
			'focal'        => 'center center',
			'alt'          => __( 'Statement The Object In Focus Relic Tension Study', 'statement-collector-theme' ),
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
			$is_first     = ( 0 === $idx );
			$slide_num    = $idx + 1;
			$has_video    = ! empty( $slide['mobile_video'] );
			$has_content  = ( ! empty( $slide['heading'] ) || ! empty( $slide['eyebrow'] ) || ( ! empty( $slide['link'] ) && ! empty( $slide['cta'] ) ) );
			$aria_label   = sprintf( __( 'Slide %1$d of %2$d', 'statement-collector-theme' ), $slide_num, $total_slides );
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
					<?php if ( $has_video ) : ?>
						<!-- Desktop Wide Image -->
						<picture class="statement-hero-slide__picture statement-hero-slide__desktop-media">
							<img src="<?php echo esc_url( $slide['image'] ); ?>"
								 alt="<?php echo esc_attr( $slide['alt'] ); ?>"
								 class="statement-hero-slide__image"
								 loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
								 fetchpriority="<?php echo $is_first ? 'high' : 'auto'; ?>"
								 style="object-position: <?php echo esc_attr( $slide['focal'] ); ?>;" />
						</picture>

						<!-- Mobile Video with Poster Fallback -->
						<div class="statement-hero-slide__mobile-video statement-hero-slide__mobile-media">
							<video class="statement-hero-slide__video"
								   poster="<?php echo esc_url( ! empty( $slide['poster'] ) ? $slide['poster'] : ( ! empty( $slide['mobile_image'] ) ? $slide['mobile_image'] : $slide['image'] ) ); ?>"
								   playsinline
								   muted
								   loop
								   autoplay
								   preload="metadata"
								   aria-hidden="true">
								<source src="<?php echo esc_url( $slide['mobile_video'] ); ?>" type="video/mp4">
							</video>
						</div>
					<?php else : ?>
						<picture class="statement-hero-slide__picture">
							<?php if ( ! empty( $slide['mobile_image'] ) ) : ?>
								<source media="(max-width: 767px)" srcset="<?php echo esc_url( $slide['mobile_image'] ); ?>">
							<?php endif; ?>
							<img src="<?php echo esc_url( $slide['image'] ); ?>"
								 alt="<?php echo esc_attr( $slide['alt'] ); ?>"
								 class="statement-hero-slide__image"
								 loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
								 fetchpriority="<?php echo $is_first ? 'high' : 'auto'; ?>"
								 style="object-position: <?php echo esc_attr( $slide['focal'] ); ?>;" />
						</picture>
					<?php endif; ?>
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
										<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-hero-slide__arrow' ) ); ?>
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
			<div class="statement-hero-slider__rail statement-hero-slider__pagination" role="tablist" aria-label="<?php esc_attr_e( 'Slide indicators', 'statement-collector-theme' ); ?>">
				<?php for ( $i = 0; $i < $total_slides; $i++ ) : ?>
					<button type="button"
							class="statement-hero-slider__rail-segment statement-hero-slider__dot <?php echo ( 0 === $i ) ? 'is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo ( 0 === $i ) ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'statement-collector-theme' ), $i + 1 ) ); ?>">
						<span class="statement-hero-slider__rail-fill" aria-hidden="true"></span>
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
					<?php render_statement_icon( 'arrow-left' ); ?>
				</button>
				<button type="button"
						class="statement-hero-slider__control statement-hero-slider__control--next"
						aria-label="<?php esc_attr_e( 'Next slide', 'statement-collector-theme' ); ?>">
					<?php render_statement_icon( 'arrow-right' ); ?>
				</button>
			</div>
		</div>

		<div class="statement-hero-slider__live screen-reader-text" aria-live="polite" aria-atomic="true"></div>
	<?php endif; ?>
</section>
