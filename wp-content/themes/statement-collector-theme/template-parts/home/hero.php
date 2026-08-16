<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$page_id   = isset( $args['page_id'] ) ? absint( $args['page_id'] ) : 0;
$drop      = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
$drop_url  = isset( $args['drop_url'] ) && is_string( $args['drop_url'] ) && '' !== $args['drop_url'] ? $args['drop_url'] : null;
$has_image = $page_id > 0 && has_post_thumbnail( $page_id );
$site_name = get_bloginfo( 'name' );
$title     = is_object( $drop ) && isset( $drop->name ) && '' !== trim( (string) $drop->name ) ? trim( (string) $drop->name ) : $site_name;
?>
<section class="statement-home-hero statement-home-hero--image" aria-labelledby="statement-home-title">
	<div class="statement-home-hero__media">
		<?php if ( $has_image ) : ?>
			<?php
			echo wp_kses_post(
				get_the_post_thumbnail(
					$page_id,
					'full',
					array(
						'class'         => 'statement-home-hero__image',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '100vw',
					)
				)
			);
			?>
		<?php else : ?>
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-monogram-jacket-front.jpg' ); ?>"
				 alt="<?php esc_attr_e( 'Statement Monogram Jacket Campaign', 'statement-collector-theme' ); ?>"
				 class="statement-home-hero__image"
				 loading="eager"
				 fetchpriority="high" />
		<?php endif; ?>
	</div>

	<div class="statement-home-hero__content statement-container--wide">
		<div class="statement-home-hero__inner">
			<p class="statement-eyebrow statement-home-hero__eyebrow"><?php esc_html_e( 'STATEMENT / CURRENT RELEASE', 'statement-collector-theme' ); ?></p>
			<h1 class="statement-home-hero__title" id="statement-home-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( null !== $drop_url ) : ?>
				<div class="statement-home-hero__actions">
					<a class="statement-home-hero__cta" href="<?php echo esc_url( $drop_url ); ?>">
						<span><?php esc_html_e( 'ENTER DROP', 'statement-collector-theme' ); ?></span>
						<span aria-hidden="true" class="statement-home-hero__arrow">&rarr;</span>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
