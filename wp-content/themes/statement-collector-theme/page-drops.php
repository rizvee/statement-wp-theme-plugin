<?php
/**
 * Template Name: Drops Index
 * Description: High-fashion drops index listing current and historical Statement releases.
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
		// Filter out any QA test terms
		if ( is_object( $pterm ) && isset( $pterm->name ) && 0 !== strpos( $pterm->name, 'TEST —' ) ) {
			$past_drops[] = $pterm;
		}
	}
}

$drop_url = is_object( $current_drop ) ? get_term_link( $current_drop ) : home_url( '/shop/' );
?>
<main id="primary" class="statement-drops-page statement-container--wide">
	<header class="statement-drops-page__header">
		<span class="statement-eyebrow"><?php esc_html_e( 'RELEASES', 'statement-collector-theme' ); ?></span>
		<h1 class="statement-drops-page__title"><?php esc_html_e( 'DROPS', 'statement-collector-theme' ); ?></h1>
		<p class="statement-drops-page__subtitle"><?php esc_html_e( 'Considered releases, defined by form and surface.', 'statement-collector-theme' ); ?></p>
	</header>

	<section class="statement-drops-current" aria-labelledby="statement-current-drop-heading">
		<span class="statement-eyebrow"><?php esc_html_e( 'CURRENT RELEASE', 'statement-collector-theme' ); ?></span>

		<div class="statement-drops-current__card">
			<div class="statement-drops-current__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/statement-monogram-jacket-front.jpg' ); ?>"
					 alt="<?php esc_attr_e( 'Drop 001 — Monogram Study Campaign', 'statement-collector-theme' ); ?>"
					 class="statement-drops-current__image"
					 loading="eager"
					 fetchpriority="high" />
			</div>

			<div class="statement-drops-current__content">
				<h2 id="statement-current-drop-heading" class="statement-drops-current__title">
					<?php echo is_object( $current_drop ) ? esc_html( $current_drop->name ) : esc_html__( 'DROP 001 — MONOGRAM STUDY', 'statement-collector-theme' ); ?>
				</h2>
				<p class="statement-drops-current__desc">
					<?php echo is_object( $current_drop ) && ! empty( $current_drop->description ) ? esc_html( $current_drop->description ) : esc_html__( 'A study in repeating surface, restrained geometry, and structural wool.', 'statement-collector-theme' ); ?>
				</p>
				<div class="statement-drops-current__actions">
					<a href="<?php echo esc_url( $drop_url ); ?>" class="statement-drops-current__cta">
						<span><?php esc_html_e( 'VIEW DROP', 'statement-collector-theme' ); ?></span>
						<span aria-hidden="true">&rarr;</span>
					</a>
				</div>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $past_drops ) ) : ?>
		<section class="statement-drops-past" aria-labelledby="statement-past-drops-heading">
			<span class="statement-eyebrow"><?php esc_html_e( 'PAST RELEASES', 'statement-collector-theme' ); ?></span>
			<h2 id="statement-past-drops-heading" class="statement-drops-past__title"><?php esc_html_e( 'PAST DROPS', 'statement-collector-theme' ); ?></h2>

			<div class="statement-drops-past__grid">
				<?php foreach ( $past_drops as $past_drop ) : ?>
					<div class="statement-drops-past__card">
						<h3 class="statement-drops-past__card-title"><?php echo esc_html( $past_drop->name ); ?></h3>
						<span class="statement-badge statement-badge--archived"><?php esc_html_e( 'ARCHIVED', 'statement-collector-theme' ); ?></span>
						<a href="<?php echo esc_url( get_term_link( $past_drop ) ); ?>" class="statement-drops-past__card-link">
							<?php esc_html_e( 'VIEW ARCHIVE RECORD', 'statement-collector-theme' ); ?> &rarr;
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
