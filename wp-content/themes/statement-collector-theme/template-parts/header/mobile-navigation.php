<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

$account_url  = get_account_url();
$cart_url     = get_cart_url();
$bag_count    = get_bag_count();
$facebook_url = get_facebook_url();
?>
<dialog class="statement-dialog statement-mobile-dialog" id="statement-mobile-navigation" aria-labelledby="statement-mobile-navigation-title">
	<div class="statement-mobile-dialog__header">
		<div class="statement-mobile-dialog__meta">
			<span class="statement-meta-code" id="statement-mobile-navigation-title"><?php esc_html_e( 'STATEMENT / NAVIGATION', 'statement-collector-theme' ); ?></span>
		</div>
		<button class="statement-dialog-close statement-mobile-dialog__close" type="button" data-dialog-close aria-label="<?php esc_attr_e( 'Close navigation', 'statement-collector-theme' ); ?>">
			<?php render_statement_icon( 'close' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'statement-collector-theme' ); ?></span>
		</button>
	</div>

	<div class="statement-mobile-dialog__body">
		<nav class="statement-mobile-navigation" aria-label="<?php esc_attr_e( 'Mobile primary navigation', 'statement-collector-theme' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'statement-mobile-menu',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
			} else {
				render_mobile_primary_navigation();
			}
			?>
		</nav>
	</div>

	<div class="statement-mobile-dialog__footer">
		<div class="statement-mobile-utilities" role="group" aria-label="<?php esc_attr_e( 'Site utilities', 'statement-collector-theme' ); ?>">
			<button class="statement-mobile-utility statement-mobile-utility--search" type="button" data-dialog-open="statement-search-dialog">
				<?php render_statement_icon( 'search', array( 'class' => 'statement-mobile-utility__icon' ) ); ?>
				<span><?php esc_html_e( 'Search', 'statement-collector-theme' ); ?></span>
			</button>
			<?php if ( null !== $account_url ) : ?>
				<a class="statement-mobile-utility statement-mobile-utility--account" href="<?php echo esc_url( $account_url ); ?>">
					<?php render_statement_icon( 'account', array( 'class' => 'statement-mobile-utility__icon' ) ); ?>
					<span><?php esc_html_e( 'Account', 'statement-collector-theme' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( null !== $cart_url ) : ?>
				<a class="statement-mobile-utility statement-mobile-utility--bag" href="<?php echo esc_url( $cart_url ); ?>">
					<?php render_statement_icon( 'bag', array( 'class' => 'statement-mobile-utility__icon' ) ); ?>
					<span><?php echo esc_html( get_bag_label() ); ?></span>
					<?php if ( $bag_count > 0 ) : ?>
						<span class="statement-header-bag-pill" aria-hidden="true"><?php echo esc_html( (string) $bag_count ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="statement-mobile-dialog__channels">
			<a class="statement-mobile-dialog__channel" href="https://instagram.com/statement.au" target="_blank" rel="noopener noreferrer">
				<?php render_statement_icon( 'instagram', array( 'class' => 'statement-mobile-dialog__channel-icon' ) ); ?>
				<span>@statement.au</span>
			</a>
			<?php if ( ! empty( $facebook_url ) ) : ?>
				<a class="statement-mobile-dialog__channel" href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php render_statement_icon( 'facebook', array( 'class' => 'statement-mobile-dialog__channel-icon' ) ); ?>
					<span><?php esc_html_e( 'Facebook', 'statement-collector-theme' ); ?></span>
				</a>
			<?php endif; ?>
			<a class="statement-mobile-dialog__channel" href="mailto:info@mystatement.store">
				<?php render_statement_icon( 'email', array( 'class' => 'statement-mobile-dialog__channel-icon' ) ); ?>
				<span>info@mystatement.store</span>
			</a>
		</div>

		<div class="statement-mobile-dialog__motto-wrap">
			<p class="statement-mobile-dialog__motto">NOT MASS PRODUCED.</p>
		</div>
	</div>
</dialog>
