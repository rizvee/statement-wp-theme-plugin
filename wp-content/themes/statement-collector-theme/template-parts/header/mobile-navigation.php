<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$account_url = get_account_url();
$cart_url    = get_cart_url();
?>
<dialog class="statement-dialog statement-mobile-dialog" id="statement-mobile-navigation" aria-labelledby="statement-mobile-navigation-title">
	<div class="statement-mobile-dialog__header">
		<p class="statement-mobile-dialog__title" id="statement-mobile-navigation-title"><?php esc_html_e( 'Menu', 'statement-collector-theme' ); ?></p>
		<button class="statement-dialog-close" type="button" data-dialog-close>
			<span aria-hidden="true">&times;</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'statement-collector-theme' ); ?></span>
		</button>
	</div>

	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav class="statement-mobile-navigation" aria-label="<?php esc_attr_e( 'Mobile primary navigation', 'statement-collector-theme' ); ?>">
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
		</nav>
	<?php endif; ?>

	<div class="statement-mobile-utilities" role="group" aria-label="<?php esc_attr_e( 'Site utilities', 'statement-collector-theme' ); ?>">
		<button class="statement-mobile-utility" type="button" data-dialog-open="statement-search-dialog">
			<?php esc_html_e( 'Search', 'statement-collector-theme' ); ?>
		</button>
		<?php if ( null !== $account_url ) : ?>
			<a class="statement-mobile-utility" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Account', 'statement-collector-theme' ); ?></a>
		<?php endif; ?>
		<?php if ( null !== $cart_url ) : ?>
			<a class="statement-mobile-utility" href="<?php echo esc_url( $cart_url ); ?>"><?php echo esc_html( get_bag_label() ); ?></a>
		<?php endif; ?>
	</div>
</dialog>
