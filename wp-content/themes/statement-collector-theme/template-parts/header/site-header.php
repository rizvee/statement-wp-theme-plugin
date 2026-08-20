<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$account_url = get_account_url();
$cart_url    = get_cart_url();
$bag_count   = get_bag_count();
?>
<header class="statement-site-header" id="statement-site-header">
	<div class="statement-site-header__desktop statement-container--wide">
		<nav class="statement-primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'statement-collector-theme' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'statement-menu',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
			} else {
				render_primary_navigation();
			}
			?>
		</nav>

		<div class="statement-brand">
			<?php render_site_brand(); ?>
		</div>

		<div class="statement-header-utilities" role="group" aria-label="<?php esc_attr_e( 'Site utilities', 'statement-collector-theme' ); ?>">
			<a class="statement-header-action statement-header-action--about" href="<?php echo esc_url( get_about_url() ); ?>">
				<span class="statement-header-action__label"><?php esc_html_e( 'About', 'statement-collector-theme' ); ?></span>
			</a>
			<button class="statement-header-action statement-header-action--search" type="button" data-dialog-open="statement-search-dialog" aria-label="<?php esc_attr_e( 'Search catalogue', 'statement-collector-theme' ); ?>">
				<?php render_statement_icon( 'search', array( 'class' => 'statement-header-action__icon' ) ); ?>
				<span class="statement-header-action__label"><?php esc_html_e( 'Search', 'statement-collector-theme' ); ?></span>
			</button>
			<?php if ( null !== $account_url ) : ?>
				<a class="statement-header-action statement-header-action--account" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php esc_attr_e( 'Collector Account', 'statement-collector-theme' ); ?>">
					<?php render_statement_icon( 'account', array( 'class' => 'statement-header-action__icon' ) ); ?>
					<span class="statement-header-action__label"><?php esc_html_e( 'Account', 'statement-collector-theme' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( null !== $cart_url ) : ?>
				<a class="statement-header-action statement-header-action--bag" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Shopping Bag', 'statement-collector-theme' ); ?>">
					<?php render_statement_icon( 'bag', array( 'class' => 'statement-header-action__icon' ) ); ?>
					<span class="statement-header-action__label"><?php echo esc_html( get_bag_label() ); ?></span>
					<?php if ( $bag_count > 0 ) : ?>
						<span class="statement-header-bag-pill" aria-hidden="true"><?php echo esc_html( (string) $bag_count ); ?></span>
					<?php endif; ?>
				</a>
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
			aria-label="<?php esc_attr_e( 'Open Menu', 'statement-collector-theme' ); ?>"
		>
			<?php render_statement_icon( 'menu', array( 'class' => 'statement-mobile-menu-trigger__icon' ) ); ?>
			<span class="statement-mobile-menu-trigger__label"><?php esc_html_e( 'Menu', 'statement-collector-theme' ); ?></span>
		</button>

		<div class="statement-brand">
			<?php render_site_brand(); ?>
		</div>

		<div class="statement-mobile-bag">
			<?php if ( null !== $cart_url ) : ?>
				<a class="statement-header-action statement-header-action--bag" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Shopping Bag', 'statement-collector-theme' ); ?>">
					<?php render_statement_icon( 'bag', array( 'class' => 'statement-header-action__icon' ) ); ?>
					<?php if ( $bag_count > 0 ) : ?>
						<span class="statement-header-bag-pill" aria-hidden="true"><?php echo esc_html( (string) $bag_count ); ?></span>
					<?php else : ?>
						<span class="statement-mobile-bag__label"><?php echo esc_html( get_bag_label() ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

<?php get_template_part( 'template-parts/header/mobile-navigation' ); ?>
<?php get_template_part( 'template-parts/header/search-dialog' ); ?>
