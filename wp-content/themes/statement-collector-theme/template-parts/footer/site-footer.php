<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$site_name    = get_bloginfo( 'name' );
$brand_label  = '' !== trim( (string) $site_name ) ? trim( (string) $site_name ) : __( 'STATEMENT', 'statement-collector-theme' );
$account_url  = get_account_url();
$facebook_url = get_facebook_url();
?>
<footer class="statement-site-footer">
	<div class="statement-site-footer__inner statement-container--wide">
		<div class="statement-site-footer__grid">
			<!-- Col 1: Brand & Philosophy -->
			<div class="statement-site-footer__col statement-site-footer__col--brand">
				<a class="statement-site-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $brand_label ); ?>">
					<span class="statement-brand-wordmark"><?php echo esc_html( $brand_label ); ?></span>
				</a>
				<p class="statement-site-footer__message"><?php esc_html_e( 'Crafted. Limited. Never Restocked.', 'statement-collector-theme' ); ?></p>
				<p class="statement-site-footer__provenance-note">
					<?php esc_html_e( 'A collector archive of considered garments, released in single non-recurring editions.', 'statement-collector-theme' ); ?>
				</p>
			</div>

			<!-- Col 2: Collection Navigation -->
			<div class="statement-site-footer__col statement-site-footer__col--nav">
				<h3 class="statement-site-footer__col-heading"><?php esc_html_e( 'COLLECTION', 'statement-collector-theme' ); ?></h3>
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
							<li><a href="<?php echo esc_url( get_shop_url() ); ?>"><?php esc_html_e( 'SHOP', 'statement-collector-theme' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_drops_url() ); ?>"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_archive_url() ); ?>"><?php esc_html_e( 'ARCHIVE', 'statement-collector-theme' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_about_url() ); ?>"><?php esc_html_e( 'ABOUT', 'statement-collector-theme' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_contact_url() ); ?>"><?php esc_html_e( 'CONTACT', 'statement-collector-theme' ); ?></a></li>
						</ul>
					<?php endif; ?>
				</nav>
			</div>

			<!-- Col 3: Collector Services & Account -->
			<div class="statement-site-footer__col statement-site-footer__col--services">
				<h3 class="statement-site-footer__col-heading"><?php esc_html_e( 'SERVICES', 'statement-collector-theme' ); ?></h3>
				<ul class="statement-footer-navigation__list">
					<?php if ( null !== $account_url ) : ?>
						<li><a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'ACCOUNT & ORDERS', 'statement-collector-theme' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( get_about_url() ); ?>"><?php esc_html_e( 'SIZE & FIT GUIDE', 'statement-collector-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( get_contact_url() ); ?>"><?php esc_html_e( 'CONCIERGE CARE', 'statement-collector-theme' ); ?></a></li>
				</ul>
			</div>

			<!-- Col 4: Direct Channels & Concierge -->
			<div class="statement-site-footer__col statement-site-footer__col--channels">
				<h3 class="statement-site-footer__col-heading"><?php esc_html_e( 'CHANNELS', 'statement-collector-theme' ); ?></h3>
				<div class="statement-site-footer__socials">
					<a href="https://instagram.com/statement.au" target="_blank" rel="noopener noreferrer" class="statement-site-footer__social-link">
						<?php render_statement_icon( 'instagram', array( 'class' => 'statement-site-footer__social-icon' ) ); ?>
						<span>@statement.au</span>
					</a>
					<?php if ( ! empty( $facebook_url ) ) : ?>
						<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" class="statement-site-footer__social-link">
							<?php render_statement_icon( 'facebook', array( 'class' => 'statement-site-footer__social-icon' ) ); ?>
							<span><?php esc_html_e( 'Facebook', 'statement-collector-theme' ); ?></span>
						</a>
					<?php endif; ?>
					<a href="mailto:info@mystatement.store" class="statement-site-footer__social-link">
						<?php render_statement_icon( 'email', array( 'class' => 'statement-site-footer__social-icon' ) ); ?>
						<span>info@mystatement.store</span>
					</a>
				</div>
			</div>
		</div>

		<div class="statement-site-footer__bottom">
			<p class="statement-site-footer__copyright">
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $brand_label ); ?>. <?php esc_html_e( 'All rights reserved.', 'statement-collector-theme' ); ?>
			</p>
			<p class="statement-site-footer__motto">
				<?php esc_html_e( 'Crafted. Limited. Never Restocked.', 'statement-collector-theme' ); ?>
			</p>
		</div>
	</div>
</footer>
