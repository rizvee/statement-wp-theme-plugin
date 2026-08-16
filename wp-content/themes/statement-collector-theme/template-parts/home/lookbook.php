<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

?>
<section class="statement-home-lookbook statement-container--wide" aria-label="<?php esc_attr_e( 'Lookbook', 'statement-collector-theme' ); ?>">
	<header class="statement-home-lookbook__header">
		<span class="statement-eyebrow"><?php esc_html_e( 'Visual Record', 'statement-collector-theme' ); ?></span>
		<h2 class="statement-home-lookbook__title"><?php esc_html_e( 'STUDY & FORM', 'statement-collector-theme' ); ?></h2>
	</header>

	<div class="statement-home-lookbook__grid">
		<div class="statement-home-lookbook__item statement-home-lookbook__item--primary">
			<div class="statement-home-lookbook__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-monogram-jacket-front.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Monogram Jacquard Jacket Studio Model Front', 'statement-collector-theme' ); ?>"
					 class="statement-home-lookbook__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-lookbook__meta">
				<span class="statement-home-lookbook__label"><?php esc_html_e( '01 / MONOGRAM JACQUARD', 'statement-collector-theme' ); ?></span>
			</div>
		</div>

		<div class="statement-home-lookbook__item statement-home-lookbook__item--offset">
			<div class="statement-home-lookbook__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-monogram-jacket-flatlay-concrete.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Monogram Jacquard Jacket Concrete Flat Lay', 'statement-collector-theme' ); ?>"
					 class="statement-home-lookbook__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-lookbook__meta">
				<span class="statement-home-lookbook__label"><?php esc_html_e( '02 / SURFACE & WEAVE', 'statement-collector-theme' ); ?></span>
			</div>
		</div>

		<div class="statement-home-lookbook__item statement-home-lookbook__item--detail">
			<div class="statement-home-lookbook__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-panelled-hood-jacket-embroidery-detail.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Insignia Chest Embroidery Macro', 'statement-collector-theme' ); ?>"
					 class="statement-home-lookbook__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-lookbook__meta">
				<span class="statement-home-lookbook__label"><?php esc_html_e( '03 / INSIGNIA EMBROIDERY', 'statement-collector-theme' ); ?></span>
			</div>
		</div>

		<div class="statement-home-lookbook__item statement-home-lookbook__item--secondary">
			<div class="statement-home-lookbook__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-panelled-hood-jacket-front.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Statement Panelled Hood Jacket Model Front', 'statement-collector-theme' ); ?>"
					 class="statement-home-lookbook__image"
					 loading="lazy" />
			</div>
			<div class="statement-home-lookbook__meta">
				<span class="statement-home-lookbook__label"><?php esc_html_e( '04 / PANELLED HOOD', 'statement-collector-theme' ); ?></span>
			</div>
		</div>
	</div>
</section>
