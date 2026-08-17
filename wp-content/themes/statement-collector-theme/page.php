<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="statement-page statement-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'statement-article' ); ?>>
			<header class="statement-article__header">
				<h1 class="statement-article__title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="statement-article__media">
					<?php the_post_thumbnail( 'full', array( 'class' => 'statement-article__image' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="statement-article__content statement-entry-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
