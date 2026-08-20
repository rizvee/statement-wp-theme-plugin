<?php

namespace Statement\Collector\Theme;

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

$current_drop = class_exists( PublicApi::class ) ? PublicApi::get_current_drop() : null;
$drop_url     = is_object( $current_drop ) ? get_term_link( $current_drop ) : home_url( '/shop/' );
$current_name = is_object( $current_drop ) && isset( $current_drop->name ) && '' !== trim( $current_drop->name ) ? $current_drop->name : __( 'Drop 001 — Monogram Study', 'statement-collector-theme' );
?>
<section class="statement-home-drops-directory statement-container--wide" aria-labelledby="statement-home-drops-title">
	<div class="statement-home-drops-directory__inner">
		<header class="statement-home-drops-directory__header">
			<span class="statement-eyebrow"><?php esc_html_e( 'RELEASES DIRECTORY', 'statement-collector-theme' ); ?></span>
			<h2 id="statement-home-drops-title" class="statement-home-drops-directory__title"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></h2>
		</header>

		<div class="statement-home-drops-directory__lists">
			<div class="statement-home-drops-group">
				<h3 class="statement-home-drops-group__label"><?php esc_html_e( 'CURRENT RELEASE', 'statement-collector-theme' ); ?></h3>
				<ul class="statement-editorial-drops-list">
					<li class="statement-editorial-drops-list__item statement-editorial-drops-list__item--active">
						<a href="<?php echo esc_url( $drop_url ); ?>" class="statement-editorial-drops-list__link">
							<span class="statement-editorial-drops-list__index">01</span>
							<span class="statement-editorial-drops-list__name"><?php echo esc_html( $current_name ); ?></span>
							<span class="statement-editorial-drops-list__arrow" aria-hidden="true">&rarr;</span>
						</a>
					</li>
				</ul>
			</div>

			<div class="statement-home-drops-group">
				<h3 class="statement-home-drops-group__label"><?php esc_html_e( 'UPCOMING DROPS', 'statement-collector-theme' ); ?></h3>
				<ul class="statement-editorial-drops-list">
					<li class="statement-editorial-drops-list__item statement-editorial-drops-list__item--upcoming">
						<div class="statement-editorial-drops-list__static">
							<span class="statement-editorial-drops-list__index">02</span>
							<span class="statement-editorial-drops-list__name"><?php esc_html_e( 'Drop 002', 'statement-collector-theme' ); ?></span>
							<span class="statement-badge statement-badge--upcoming"><?php esc_html_e( 'UPCOMING', 'statement-collector-theme' ); ?></span>
						</div>
					</li>
					<li class="statement-editorial-drops-list__item statement-editorial-drops-list__item--upcoming">
						<div class="statement-editorial-drops-list__static">
							<span class="statement-editorial-drops-list__index">03</span>
							<span class="statement-editorial-drops-list__name"><?php esc_html_e( 'Drop 003', 'statement-collector-theme' ); ?></span>
							<span class="statement-badge statement-badge--upcoming"><?php esc_html_e( 'UPCOMING', 'statement-collector-theme' ); ?></span>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</section>
