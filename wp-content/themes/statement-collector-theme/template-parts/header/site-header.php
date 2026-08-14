<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$account_url = get_account_url();
$cart_url    = get_cart_url();
?>
<header class="statement-site-header">
	<div class="statement-site-header__desktop statement-container--wide">
		<div class="statement-primary-navigation">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Primary navigation', 'statement-collector-theme' ); ?>">
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
				</nav>
			<?php endif; ?>
		</div>

		<div class="statement-brand">
			<?php render_site_brand(); ?>
		</div>

		<div class="statement-header-utilities" role="group" aria-label="<?php esc_attr_e( 'Site utilities', 'statement-collector-theme' ); ?>">
			<button class="statement-header-action" type="button" data-dialog-open="statement-search-dialog">
				<?php esc_html_e( 'Search', 'statement-collector-theme' ); ?>
			</button>
			<?php if ( null !== $account_url ) : ?>
				<a class="statement-header-action" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Account', 'statement-collector-theme' ); ?></a>
			<?php endif; ?>
			<?php if ( null !== $cart_url ) : ?>
				<a class="statement-header-action" href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'Bag', 'statement-collector-theme' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="statement-site-header__mobile statement-container--wide">
		<button
			class="statement-header-action statement-mobile-menu-trigger"
			type="button"
			aria-controls="statement-mobile-navigation"
			aria-expanded="false"
			data-dialog-open="statement-mobile-navigation"
		>
			<?php esc_html_e( 'Menu', 'statement-collector-theme' ); ?>
		</button>

		<div class="statement-brand">
			<?php render_site_brand(); ?>
		</div>

		<div class="statement-mobile-bag">
			<?php if ( null !== $cart_url ) : ?>
				<a class="statement-header-action" href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'Bag', 'statement-collector-theme' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</header>

<?php get_template_part( 'template-parts/header/mobile-navigation' ); ?>
<?php get_template_part( 'template-parts/header/search-dialog' ); ?>
