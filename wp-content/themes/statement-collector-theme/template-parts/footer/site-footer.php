<?php

defined( 'ABSPATH' ) || exit;

$site_name   = get_bloginfo( 'name' );
$account_url = \Statement\Collector\Theme\get_account_url();
?>
<footer class="statement-site-footer">
	<div class="statement-site-footer__inner statement-container--wide">
		<div class="statement-site-footer__identity">
			<a class="statement-site-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="statement-brand-wordmark"><?php esc_html_e( 'STATEMENT', 'statement-collector-theme' ); ?></span>
			</a>
			<p class="statement-site-footer__message"><?php esc_html_e( 'Crafted. Not Mass Made.', 'statement-collector-theme' ); ?></p>
		</div>

		<nav class="statement-footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'statement-collector-theme' ); ?>">
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
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
			<?php else : ?>
				<ul class="statement-footer-navigation__list">
					<li><a href="<?php echo esc_url( \Statement\Collector\Theme\get_shop_url() ); ?>"><?php esc_html_e( 'SHOP', 'statement-collector-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( \Statement\Collector\Theme\get_drops_url() ); ?>"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( \Statement\Collector\Theme\get_archive_url() ); ?>"><?php esc_html_e( 'ARCHIVE', 'statement-collector-theme' ); ?></a></li>
					<?php if ( null !== $account_url ) : ?>
						<li><a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'ACCOUNT', 'statement-collector-theme' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( \Statement\Collector\Theme\get_about_url() ); ?>"><?php esc_html_e( 'ABOUT', 'statement-collector-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( \Statement\Collector\Theme\get_contact_url() ); ?>"><?php esc_html_e( 'CONTACT', 'statement-collector-theme' ); ?></a></li>
				</ul>
			<?php endif; ?>
		</nav>

		<div class="statement-site-footer__bottom">
			<p class="statement-site-footer__copyright">
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>
			</p>
		</div>
	</div>
</footer>
