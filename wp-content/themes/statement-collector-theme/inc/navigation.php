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
 * Return the Statement Archive page URL.
 */
function get_archive_url(): string {
	return home_url( '/archive/' );
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
	<nav class="statement-mobile-navigation" aria-label="<?php esc_attr_e( 'Mobile primary navigation', 'statement-collector-theme' ); ?>">
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
				<li><a href="<?php echo esc_url( get_shop_url() ); ?>"><?php esc_html_e( 'SHOP', 'statement-collector-theme' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_archive_url() ); ?>"><?php esc_html_e( 'ARCHIVE', 'statement-collector-theme' ); ?></a></li>
			</ul>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Render the native custom logo or an escaped site-name fallback.
 */
function render_site_brand(): void {
	if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="statement-brand__fallback" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
	</a>
	<?php
}
