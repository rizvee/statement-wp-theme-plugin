<?php
/**
 * Template Name: Drops Index
 * Description: Editorial text-first drops directory listing current, upcoming, and past Statement releases.
 */

namespace Statement\Collector\Theme;

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

get_header();

$current_drop = class_exists( PublicApi::class ) ? PublicApi::get_current_drop() : null;
$past_drops   = array();

if ( class_exists( PublicApi::class ) ) {
	$raw_past = PublicApi::get_past_drops();
	foreach ( $raw_past as $pterm ) {
		if ( is_object( $pterm ) && isset( $pterm->name ) && 0 !== strpos( $pterm->name, 'TEST —' ) ) {
			$past_drops[] = $pterm;
		}
	}
}

$drop_url     = is_object( $current_drop ) ? get_term_link( $current_drop ) : home_url( '/shop/' );
$current_name = is_object( $current_drop ) && isset( $current_drop->name ) && '' !== trim( $current_drop->name ) ? $current_drop->name : __( 'Drop 001 — Monogram Study', 'statement-collector-theme' );
$current_desc = is_object( $current_drop ) && ! empty( $current_drop->description ) ? $current_drop->description : __( 'A study in repeating surface, restrained geometry, and structural wool.', 'statement-collector-theme' );
?>
<main id="primary" class="statement-drops-page statement-container--wide">
	<header class="statement-drops-page__header">
		<span class="statement-eyebrow"><?php esc_html_e( 'RELEASES DIRECTORY', 'statement-collector-theme' ); ?></span>
		<h1 class="statement-drops-page__title"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></h1>
		<p class="statement-drops-page__subtitle"><?php esc_html_e( 'Considered releases, defined by form and surface.', 'statement-collector-theme' ); ?></p>
	</header>

	<div class="statement-home-drops-directory__lists">
		<div class="statement-home-drops-group">
			<h2 class="statement-home-drops-group__label"><?php esc_html_e( 'CURRENT', 'statement-collector-theme' ); ?></h2>
			<ul class="statement-editorial-drops-list">
				<li class="statement-editorial-drops-list__item statement-editorial-drops-list__item--active">
					<a href="<?php echo esc_url( $drop_url ); ?>" class="statement-editorial-drops-list__link">
						<span class="statement-editorial-drops-list__index">01</span>
						<span class="statement-editorial-drops-list__name"><?php echo esc_html( preg_replace( '/^Drop\s*\d+\s*[-—–]\s*/i', '', $current_name ) ); ?></span>
						<span class="statement-editorial-drops-list__arrow" aria-hidden="true"><?php esc_html_e( 'VIEW RELEASE →', 'statement-collector-theme' ); ?></span>
					</a>
				</li>
			</ul>
			<p class="statement-home-drop__description" style="margin-top: 1rem;">
				<?php echo esc_html( $current_desc ); ?>
			</p>
		</div>

		<div class="statement-home-drops-group">
			<h2 class="statement-home-drops-group__label"><?php esc_html_e( 'UPCOMING', 'statement-collector-theme' ); ?></h2>
			<ul class="statement-editorial-drops-list">
				<li class="statement-editorial-drops-list__item statement-editorial-drops-list__item--upcoming">
					<div class="statement-editorial-drops-list__static">
						<span class="statement-editorial-drops-list__index">02</span>
						<span class="statement-editorial-drops-list__name"><?php esc_html_e( 'DROP 002', 'statement-collector-theme' ); ?></span>
						<span class="statement-badge statement-badge--upcoming"><?php esc_html_e( 'UPCOMING', 'statement-collector-theme' ); ?></span>
					</div>
				</li>
				<li class="statement-editorial-drops-list__item statement-editorial-drops-list__item--upcoming">
					<div class="statement-editorial-drops-list__static">
						<span class="statement-editorial-drops-list__index">03</span>
						<span class="statement-editorial-drops-list__name"><?php esc_html_e( 'DROP 003', 'statement-collector-theme' ); ?></span>
						<span class="statement-badge statement-badge--upcoming"><?php esc_html_e( 'UPCOMING', 'statement-collector-theme' ); ?></span>
					</div>
				</li>
			</ul>
		</div>

		<?php if ( ! empty( $past_drops ) ) : ?>
			<div class="statement-home-drops-group">
				<h2 class="statement-home-drops-group__label"><?php esc_html_e( 'PAST RELEASES', 'statement-collector-theme' ); ?></h2>
				<ul class="statement-editorial-drops-list">
					<?php foreach ( $past_drops as $i => $past_drop ) : ?>
						<li class="statement-editorial-drops-list__item">
							<a href="<?php echo esc_url( get_term_link( $past_drop ) ); ?>" class="statement-editorial-drops-list__link">
								<span class="statement-editorial-drops-list__name"><?php echo esc_html( $past_drop->name ); ?></span>
								<span class="statement-badge statement-badge--archived"><?php esc_html_e( 'ARCHIVED', 'statement-collector-theme' ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
