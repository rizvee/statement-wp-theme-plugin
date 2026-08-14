<?php

defined( 'ABSPATH' ) || exit;

$site_name = get_bloginfo( 'name' );
?>
<footer class="statement-site-footer">
	<div class="statement-site-footer__inner statement-container--wide">
		<div class="statement-site-footer__identity">
			<a class="statement-site-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( $site_name ); ?></a>
			<p class="statement-site-footer__message"><?php esc_html_e( 'Crafted. Limited. Never Restocked.', 'statement-collector-theme' ); ?></p>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="statement-footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'statement-collector-theme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'statement-footer-navigation__list',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<p class="statement-site-footer__copyright">
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>
		</p>
	</div>
</footer>
