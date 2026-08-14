<?php

defined( 'ABSPATH' ) || exit;

$page_id   = isset( $args['page_id'] ) ? absint( $args['page_id'] ) : 0;
$drop_url  = isset( $args['drop_url'] ) && is_string( $args['drop_url'] ) && '' !== $args['drop_url'] ? $args['drop_url'] : null;
$has_image = $page_id > 0 && has_post_thumbnail( $page_id );
$site_name = get_bloginfo( 'name' );
?>
<section class="statement-home-hero<?php echo $has_image ? ' statement-home-hero--image' : ' statement-home-hero--surface'; ?>" aria-labelledby="statement-home-title">
	<?php if ( $has_image ) : ?>
		<div class="statement-home-hero__media">
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
		</div>
	<?php endif; ?>

	<div class="statement-home-hero__content statement-container--wide">
		<h1 class="statement-home-hero__title" id="statement-home-title"><?php echo esc_html( $site_name ); ?></h1>
		<?php if ( null !== $drop_url ) : ?>
			<a class="statement-home-link statement-home-hero__link" href="<?php echo esc_url( $drop_url ); ?>">
				<?php esc_html_e( 'ENTER DROP', 'statement-collector-theme' ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
