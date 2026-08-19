<?php
/**
 * Template Name: About Statement
 * Description: Pure typographic editorial narrative for Statement Collector's Piece (Text Only).
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="statement-about-document statement-container--editorial">
	<!-- Editorial Top Bar -->
	<header class="statement-about-document__header">
		<div class="statement-about-document__meta">
			<span class="statement-meta-code"><?php esc_html_e( 'STATEMENT / THE HOUSE', 'statement-collector-theme' ); ?></span>
			<span class="statement-meta-code"><?php esc_html_e( 'FOUNDED 2026', 'statement-collector-theme' ); ?></span>
		</div>
		<h1 class="statement-about-document__title"><?php esc_html_e( 'ABOUT STATEMENT', 'statement-collector-theme' ); ?></h1>
	</header>

	<div class="statement-drop-divider"></div>

	<!-- Monolithic Opening Pull Quote -->
	<section class="statement-about-lead-block">
		<p class="statement-about-lead-quote">
			<?php esc_html_e( 'Clothing should be more than something you wear. It should make a statement, tell a story, and become something worth collecting.', 'statement-collector-theme' ); ?>
		</p>
	</section>

	<div class="statement-drop-divider"></div>

	<!-- 12-Column Asymmetric Narrative Sections -->
	<div class="statement-about-narrative">
		<!-- Section 01: Philosophy -->
		<section class="statement-narrative-row">
			<div class="statement-narrative-rail">
				<span class="statement-rail-index">01</span>
				<span class="statement-rail-title"><?php esc_html_e( 'PHILOSOPHY', 'statement-collector-theme' ); ?></span>
			</div>
			<div class="statement-narrative-content">
				<p>
					<?php esc_html_e( 'Born in Australia, our brand was created for individuals who value distinctive identity over transient trends. We reject the cadence of seasonal excess in favor of permanence, sculptural integrity, and quiet confidence.', 'statement-collector-theme' ); ?>
				</p>
			</div>
		</section>

		<div class="statement-drop-divider--subtle"></div>

		<!-- Section 02: Design & Craft -->
		<section class="statement-narrative-row">
			<div class="statement-narrative-rail">
				<span class="statement-rail-index">02</span>
				<span class="statement-rail-title"><?php esc_html_e( 'CRAFT', 'statement-collector-theme' ); ?></span>
			</div>
			<div class="statement-narrative-content">
				<p>
					<?php esc_html_e( 'Every piece is developed around tactile surfaces, custom jacquards, and structured tailoring. We choose rigorous quality over volume, engineering garments designed to be worn, appreciated, and permanently remembered.', 'statement-collector-theme' ); ?>
				</p>
			</div>
		</section>

		<div class="statement-drop-divider--subtle"></div>

		<!-- Section 03: Exclusivity & Archival Integrity -->
		<section class="statement-narrative-row">
			<div class="statement-narrative-rail">
				<span class="statement-rail-index">03</span>
				<span class="statement-rail-title"><?php esc_html_e( 'EXCLUSIVITY', 'statement-collector-theme' ); ?></span>
			</div>
			<div class="statement-narrative-content">
				<p>
					<?php esc_html_e( 'What defines Statement is our unwavering commitment to scarcity. Releases occur in limited allocations. Once a drop closes, the edition is permanently sealed into the archive and will never be reproduced.', 'statement-collector-theme' ); ?>
				</p>
			</div>
		</section>

		<div class="statement-drop-divider--subtle"></div>

		<!-- Section 04: Responsibility -->
		<section class="statement-narrative-row">
			<div class="statement-narrative-rail">
				<span class="statement-rail-index">04</span>
				<span class="statement-rail-title"><?php esc_html_e( 'RESPONSIBILITY', 'statement-collector-theme' ); ?></span>
			</div>
			<div class="statement-narrative-content">
				<p>
					<?php esc_html_e( 'We make conscious decisions regarding our footprint. Wherever possible, we source responsible fibers and partner with specialized makers who uphold equitable working standards.', 'statement-collector-theme' ); ?>
				</p>
			</div>
		</section>
	</div>

	<div class="statement-drop-divider"></div>

	<!-- Monolithic Brand Signature Conclusion -->
	<footer class="statement-about-conclusion">
		<p class="statement-about-signature"><?php esc_html_e( 'CRAFTED. LIMITED. NEVER RESTOCKED.', 'statement-collector-theme' ); ?></p>
	</footer>
</main>
<?php
get_footer();
