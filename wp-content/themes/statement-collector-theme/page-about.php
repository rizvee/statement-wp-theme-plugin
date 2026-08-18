<?php
/**
 * Template Name: About Statement
 * Description: Pure editorial typography narrative for Statement Collector's Piece (Text Only).
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="statement-about-page statement-container--narrow">
	<article class="statement-about-prose">
		<header class="statement-about-header">
			<span class="statement-eyebrow"><?php esc_html_e( 'ABOUT US', 'statement-collector-theme' ); ?></span>
			<h1 class="statement-about-header__title"><?php esc_html_e( 'STATEMENT COLLECTOR\'S PIECE', 'statement-collector-theme' ); ?></h1>
		</header>

		<div class="statement-about-body">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php
					$content = get_the_content();
					if ( ! empty( trim( (string) $content ) ) ) :
						the_content();
					else :
					?>
						<p class="statement-about-lead">
							<?php esc_html_e( 'At Statement Collector\'s Piece, we believe clothing should be more than something you wear. It should make a statement, tell a story, and become something worth collecting.', 'statement-collector-theme' ); ?>
						</p>

						<p>
							<?php esc_html_e( 'Born in Australia, our brand was created for people who value individuality over trends. Every piece is thoughtfully designed using premium materials, with a focus on comfort, craftsmanship, and timeless style. We choose quality over quantity, creating garments that are made to be worn, appreciated, and remembered.', 'statement-collector-theme' ); ?>
						</p>

						<p>
							<?php esc_html_e( 'What truly sets us apart is our commitment to exclusivity. Many of our collections are released in strictly limited quantities, with selected designs individually numbered. Once a collection sells out, it is permanently retired and will never be reproduced. Owning one means owning a genuine collector\'s piece.', 'statement-collector-theme' ); ?>
						</p>

						<p>
							<?php esc_html_e( 'We are also committed to making better choices for the future. Wherever possible, we use responsibly sourced and organic materials, creating clothing that not only looks exceptional but is made with greater respect for people and the planet.', 'statement-collector-theme' ); ?>
						</p>

						<p>
							<?php esc_html_e( 'We\'re not here to chase fast fashion. We\'re here to create garments with meaning. Pieces that stand apart from the ordinary and become part of your personal story.', 'statement-collector-theme' ); ?>
						</p>

						<blockquote class="statement-about-quote">
							<p><?php esc_html_e( 'Crafted. Limited. Never Restocked.', 'statement-collector-theme' ); ?></p>
						</blockquote>
					<?php
					endif;
					?>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</article>
</main>
<?php
get_footer();
