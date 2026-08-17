<?php
/**
 * Main Template / Journal Archive.
 *
 * Provides a luxury editorial card layout for posts and studies.
 *
 * @package Statement_Collector_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="statement-page statement-journal statement-container--wide" style="padding-top: 3rem; padding-bottom: 6rem;">
	<header class="statement-catalog__header" style="margin-bottom: 3rem;">
		<span class="statement-eyebrow"><?php esc_html_e( 'Editorial Record', 'statement-collector-theme' ); ?></span>
		<h1 class="statement-catalog__title"><?php esc_html_e( 'JOURNAL', 'statement-collector-theme' ); ?></h1>
		<p class="statement-catalog__description"><?php esc_html_e( 'Studies in form, texture, silhouette, and the object.', 'statement-collector-theme' ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="statement-journal-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 2.5rem;">
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'statement-journal-card' ); ?> style="display: flex; flex-direction: column; gap: 1rem;">
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="statement-journal-card__media" style="aspect-ratio: 16/10; overflow: hidden; background: #F5F5F3; display: block;">
							<?php the_post_thumbnail( 'large', array( 'style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease;' ) ); ?>
						</a>
					<?php endif; ?>
					<div class="statement-journal-card__body">
						<span class="statement-eyebrow" style="margin-bottom: 0.25rem;"><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
						<h2 style="font-size: 1.25rem; font-weight: 500; text-transform: uppercase; margin: 0 0 0.5rem 0; letter-spacing: -0.01em;">
							<a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;"><?php the_title(); ?></a>
						</h2>
						<div style="font-size: 0.875rem; color: #666666; line-height: 1.6;">
							<?php the_excerpt(); ?>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="statement-catalog__empty">
			<p><?php esc_html_e( 'No journal entries published yet.', 'statement-collector-theme' ); ?></p>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
