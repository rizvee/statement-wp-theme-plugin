<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

$slug = get_post_field( 'post_name', get_post() );
?>
<main id="primary" class="statement-journal-single statement-container--wide">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'statement-journal-article' ); ?>>
			<header class="statement-journal-article__header statement-container">
				<p class="statement-eyebrow"><?php esc_html_e( 'STATEMENT / JOURNAL', 'statement-collector-theme' ); ?></p>
				<h1 class="statement-journal-article__title"><?php the_title(); ?></h1>
				<p class="statement-journal-article__date"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></p>
			</header>

			<div class="statement-journal-article__content statement-container">
				<?php the_content(); ?>
			</div>

			<?php
			if ( 'study-and-form-monogram-study' === $slug ) {
				get_template_part( 'template-parts/home/lookbook' );
			} elseif ( 'the-object' === $slug ) {
				get_template_part( 'template-parts/home/brand-object' );
			}
			?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
