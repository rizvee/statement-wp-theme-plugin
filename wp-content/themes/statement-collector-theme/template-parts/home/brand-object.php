<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

?>
<section class="statement-home-object statement-container--wide" aria-label="<?php esc_attr_e( 'The Object', 'statement-collector-theme' ); ?>">
	<header class="statement-home-object__header">
		<span class="statement-eyebrow"><?php esc_html_e( 'The Artifact', 'statement-collector-theme' ); ?></span>
		<h2 class="statement-home-object__title"><?php esc_html_e( 'THE OBJECT', 'statement-collector-theme' ); ?></h2>
	</header>

	<div class="statement-home-object__grid">
		<div class="statement-home-object__card">
			<div class="statement-home-object__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-collector-dust-bag.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Canvas Dust Bag Packaging', 'statement-collector-theme' ); ?>"
					 class="statement-home-object__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-object__content">
				<span class="statement-home-object__label"><?php esc_html_e( 'CANVAS PACKAGING', 'statement-collector-theme' ); ?></span>
				<p class="statement-home-object__desc"><?php esc_html_e( 'Each piece is delivered in custom unbleached canvas.', 'statement-collector-theme' ); ?></p>
			</div>
		</div>

		<div class="statement-home-object__card">
			<div class="statement-home-object__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-brand-leather-patch.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Leather Stitched Label', 'statement-collector-theme' ); ?>"
					 class="statement-home-object__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-object__content">
				<span class="statement-home-object__label"><?php esc_html_e( 'PROVENANCE & MARK', 'statement-collector-theme' ); ?></span>
				<p class="statement-home-object__desc"><?php esc_html_e( 'Designed in Victoria. Stitched with individual care.', 'statement-collector-theme' ); ?></p>
			</div>
		</div>

		<div class="statement-home-object__card">
			<div class="statement-home-object__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-collector-patch-palm.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Hexagonal Insignia Patch in Palm', 'statement-collector-theme' ); ?>"
					 class="statement-home-object__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-object__content">
				<span class="statement-home-object__label"><?php esc_html_e( 'HEXAGONAL INSIGNIA', 'statement-collector-theme' ); ?></span>
				<p class="statement-home-object__desc"><?php esc_html_e( 'Tactile dimensional insignia representing limited form.', 'statement-collector-theme' ); ?></p>
			</div>
		</div>
	</div>
</section>
