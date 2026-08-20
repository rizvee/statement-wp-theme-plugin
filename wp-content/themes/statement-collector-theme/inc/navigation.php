<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Return the WooCommerce account URL when its public API is available.
 */
function get_account_url(): ?string {
	if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'wc_get_page_permalink' ) ) {
		return null;
	}

	if ( wc_get_page_id( 'myaccount' ) < 1 ) {
		return null;
	}

	$url = wc_get_page_permalink( 'myaccount' );

	return is_string( $url ) && '' !== $url ? $url : null;
}

/**
 * Return the WooCommerce cart URL when its public API is available.
 */
function get_cart_url(): ?string {
	if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'wc_get_cart_url' ) ) {
		return null;
	}

	if ( wc_get_page_id( 'cart' ) < 1 ) {
		return null;
	}

	$url = wc_get_cart_url();

	return is_string( $url ) && '' !== $url ? $url : null;
}

/**
 * Return the Shop catalog URL when WooCommerce is active or fallback to /shop/.
 */
function get_shop_url(): string {
	if ( function_exists( 'wc_get_page_id' ) && function_exists( 'get_permalink' ) ) {
		$shop_page_id = wc_get_page_id( 'shop' );
		if ( $shop_page_id > 0 ) {
			$url = get_permalink( $shop_page_id );
			if ( is_string( $url ) && '' !== $url ) {
				return $url;
			}
		}
	}

	return home_url( '/shop/' );
}

/**
 * Return the Statement Drops index page URL.
 */
function get_drops_url(): string {
	return home_url( '/drops/' );
}

/**
 * Return the Statement Archive page URL.
 */
function get_archive_url(): string {
	return home_url( '/archive/' );
}

/**
 * Return the Statement About page URL.
 */
function get_about_url(): string {
	return home_url( '/about/' );
}

/**
 * Return the Statement Contact page URL.
 */
function get_contact_url(): string {
	return home_url( '/contact/' );
}

/**
 * Return configured Facebook page URL if present.
 */
function get_facebook_url(): ?string {
	$fb = get_theme_mod( 'statement_facebook_url', get_option( 'statement_facebook_url', '' ) );
	return ( is_string( $fb ) && '' !== trim( $fb ) ) ? trim( $fb ) : null;
}

/**
 * Render desktop primary navigation with editorial fallback when no menu is configured.
 */
function render_primary_navigation(): void {
	?>
	<nav class="statement-primary-navigation__nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'statement-collector-theme' ); ?>">
		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'statement-navigation-list',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		<?php else : ?>
			<ul class="statement-navigation-list">
				<li><a href="<?php echo esc_url( get_shop_url() ); ?>"><?php esc_html_e( 'SHOP', 'statement-collector-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_drops_url() ); ?>"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_archive_url() ); ?>"><?php esc_html_e( 'ARCHIVE', 'statement-collector-theme' ); ?></a></li>
			</ul>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Render mobile primary navigation with editorial fallback when no menu is configured.
 */
function render_mobile_primary_navigation(): void {
	?>
	<nav class="statement-mobile-navigation__nav" aria-label="<?php esc_attr_e( 'Mobile primary navigation', 'statement-collector-theme' ); ?>">
		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'statement-mobile-navigation__list',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		<?php else : ?>
			<ul class="statement-mobile-navigation__list">
				<li>
					<a href="<?php echo esc_url( get_shop_url() ); ?>">
						<span class="statement-mobile-nav-index" aria-hidden="true">01</span>
						<span class="statement-mobile-nav-label"><?php esc_html_e( 'SHOP', 'statement-collector-theme' ); ?></span>
						<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-mobile-nav-arrow' ) ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( get_drops_url() ); ?>">
						<span class="statement-mobile-nav-index" aria-hidden="true">02</span>
						<span class="statement-mobile-nav-label"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></span>
						<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-mobile-nav-arrow' ) ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( get_archive_url() ); ?>">
						<span class="statement-mobile-nav-index" aria-hidden="true">03</span>
						<span class="statement-mobile-nav-label"><?php esc_html_e( 'ARCHIVE', 'statement-collector-theme' ); ?></span>
						<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-mobile-nav-arrow' ) ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( get_about_url() ); ?>">
						<span class="statement-mobile-nav-index" aria-hidden="true">04</span>
						<span class="statement-mobile-nav-label"><?php esc_html_e( 'ABOUT', 'statement-collector-theme' ); ?></span>
						<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-mobile-nav-arrow' ) ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( get_contact_url() ); ?>">
						<span class="statement-mobile-nav-index" aria-hidden="true">05</span>
						<span class="statement-mobile-nav-label"><?php esc_html_e( 'CONTACT', 'statement-collector-theme' ); ?></span>
						<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-mobile-nav-arrow' ) ); ?>
					</a>
				</li>
				<?php if ( null !== get_account_url() ) : ?>
					<li>
						<a href="<?php echo esc_url( get_account_url() ); ?>">
							<span class="statement-mobile-nav-index" aria-hidden="true">06</span>
							<span class="statement-mobile-nav-label"><?php esc_html_e( 'ACCOUNT', 'statement-collector-theme' ); ?></span>
							<?php render_statement_icon( 'arrow-right', array( 'class' => 'statement-mobile-nav-arrow' ) ); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Render site brand logo with authentic Statement brand logo and custom_logo support.
 */
function render_site_brand(): void {
	$site_name   = get_bloginfo( 'name' );
	$brand_label = '' !== trim( (string) $site_name ) ? trim( (string) $site_name ) : __( 'STATEMENT', 'statement-collector-theme' );

	if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
	} else {
		$theme_logo_path = get_template_directory() . '/assets/images/statement-logo.png';
		$theme_logo_uri  = get_template_directory_uri() . '/assets/images/statement-logo.png';

		$logo_url = apply_filters( 'statement_theme_logo_url', $theme_logo_uri );

		if ( file_exists( $theme_logo_path ) || ( is_string( $logo_url ) && '' !== $logo_url ) ) {
			?>
			<a class="statement-brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $brand_label ); ?>">
				<img class="statement-brand-logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $brand_label ); ?>" width="250" height="80" />
			</a>
			<?php
		} else {
			?>
			<a class="statement-brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( $brand_label ); ?>">
				<span class="statement-brand-wordmark"><?php echo esc_html( $brand_label ); ?></span>
			</a>
			<?php
		}
	}
}
