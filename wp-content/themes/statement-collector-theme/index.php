<?php

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="statement-page statement-container">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header>
					<h1><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h1>
				</header>
				<?php the_content(); ?>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'statement-collector-theme' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
