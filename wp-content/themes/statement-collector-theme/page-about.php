<?php
/**
 * Template Name: About Statement
 * Description: Editorial brand narrative and craftsmanship overview for Statement Collector's Piece.
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

$theme_uri = get_template_directory_uri();
?>
<main id="primary" class="statement-about-page statement-container--wide">
	<header class="statement-about-header">
		<span class="statement-eyebrow"><?php esc_html_e( 'PROVENANCE & CRAFT', 'statement-collector-theme' ); ?></span>
		<h1 class="statement-about-header__title"><?php esc_html_e( 'CRAFTED. NOT MASS MADE.', 'statement-collector-theme' ); ?></h1>
		<p class="statement-about-header__lead">
			<?php esc_html_e( 'Statement approaches clothing as physical objects of identity rather than volume-driven basics. Each release is produced in limited numbers with structural weight, considered geometry, and permanent archival record.', 'statement-collector-theme' ); ?>
		</p>
	</header>

	<section class="statement-about-pillars" aria-label="<?php esc_attr_e( 'Craftsmanship Pillars', 'statement-collector-theme' ); ?>">
		<div class="statement-about-grid">
			<!-- Pillar 1: Form & Silhouette -->
			<article class="statement-about-pillar">
				<div class="statement-about-pillar__media">
					<img src="<?php echo esc_url( $theme_uri . '/assets/images/statement-panelled-hood-jacket-front.jpg' ); ?>"
						 alt="<?php esc_attr_e( 'Statement structural silhouette and cut', 'statement-collector-theme' ); ?>"
						 class="statement-about-pillar__image"
						 loading="lazy" />
				</div>
				<div class="statement-about-pillar__content">
					<span class="statement-eyebrow"><?php esc_html_e( 'PILLAR 01', 'statement-collector-theme' ); ?></span>
					<h2 class="statement-about-pillar__title"><?php esc_html_e( 'FORM & SILHOUETTE', 'statement-collector-theme' ); ?></h2>
					<p class="statement-about-pillar__text">
						<?php esc_html_e( 'Engineered with relaxed architectural drape, dropped shoulder contouring, and cropped proportions designed to hold form across daily movement.', 'statement-collector-theme' ); ?>
					</p>
				</div>
			</article>

			<!-- Pillar 2: Material & Surface -->
			<article class="statement-about-pillar">
				<div class="statement-about-pillar__media">
					<img src="<?php echo esc_url( $theme_uri . '/assets/images/statement-monogram-jacket-collar-detail.jpg' ); ?>"
						 alt="<?php esc_attr_e( 'Statement monogram jacquard weave detail', 'statement-collector-theme' ); ?>"
						 class="statement-about-pillar__image"
						 loading="lazy" />
				</div>
				<div class="statement-about-pillar__content">
					<span class="statement-eyebrow"><?php esc_html_e( 'PILLAR 02', 'statement-collector-theme' ); ?></span>
					<h2 class="statement-about-pillar__title"><?php esc_html_e( 'MATERIAL & SURFACE', 'statement-collector-theme' ); ?></h2>
					<p class="statement-about-pillar__text">
						<?php esc_html_e( 'Custom-milled heavyweight jacquard textiles, dense structural weave patterns, and custom matte-finish closures developed for longevity and tactile presence.', 'statement-collector-theme' ); ?>
					</p>
				</div>
			</article>

			<!-- Pillar 3: Insignia & Detail -->
			<article class="statement-about-pillar">
				<div class="statement-about-pillar__media">
					<img src="<?php echo esc_url( $theme_uri . '/assets/images/statement-brand-leather-patch.jpg' ); ?>"
						 alt="<?php esc_attr_e( 'Statement leather insignia badge', 'statement-collector-theme' ); ?>"
						 class="statement-about-pillar__image"
						 loading="lazy" />
				</div>
				<div class="statement-about-pillar__content">
					<span class="statement-eyebrow"><?php esc_html_e( 'PILLAR 03', 'statement-collector-theme' ); ?></span>
					<h2 class="statement-about-pillar__title"><?php esc_html_e( 'INSIGNIA & PROVENANCE', 'statement-collector-theme' ); ?></h2>
					<p class="statement-about-pillar__text">
						<?php esc_html_e( 'Tactile embossed leather patches, metallic thread embroidery, and numbered collector marks identifying each piece within its edition.', 'statement-collector-theme' ); ?>
					</p>
				</div>
			</article>

			<!-- Pillar 4: The Object -->
			<article class="statement-about-pillar">
				<div class="statement-about-pillar__media">
					<img src="<?php echo esc_url( $theme_uri . '/assets/images/statement-collector-dust-bag.jpg' ); ?>"
						 alt="<?php esc_attr_e( 'Statement cotton dust bag packaging', 'statement-collector-theme' ); ?>"
						 class="statement-about-pillar__image"
						 loading="lazy" />
				</div>
				<div class="statement-about-pillar__content">
					<span class="statement-eyebrow"><?php esc_html_e( 'PILLAR 04', 'statement-collector-theme' ); ?></span>
					<h2 class="statement-about-pillar__title"><?php esc_html_e( 'THE OBJECT', 'statement-collector-theme' ); ?></h2>
					<p class="statement-about-pillar__text">
						<?php esc_html_e( 'Each piece is delivered in heavy raw cotton dust packaging with durable insignia branding, preserving the garment as a physical collector piece.', 'statement-collector-theme' ); ?>
					</p>
				</div>
			</article>
		</div>
	</section>

	<section class="statement-about-manifesto" aria-label="<?php esc_attr_e( 'Release Invariant', 'statement-collector-theme' ); ?>">
		<div class="statement-about-manifesto__inner">
			<span class="statement-eyebrow"><?php esc_html_e( 'THE INVARIANT', 'statement-collector-theme' ); ?></span>
			<h2 class="statement-about-manifesto__statement">
				<?php esc_html_e( 'One release. Limited availability. Sold out. Never restocked. Permanent archive.', 'statement-collector-theme' ); ?>
			</h2>
			<div class="statement-about-manifesto__actions">
				<a href="<?php echo esc_url( get_drops_url() ); ?>" class="statement-about-manifesto__cta">
					<span><?php esc_html_e( 'EXPLORE DROPS', 'statement-collector-theme' ); ?></span>
					<span aria-hidden="true">&rarr;</span>
				</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
