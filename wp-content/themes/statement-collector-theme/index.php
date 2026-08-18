<?php
/**
 * Main Template / Journal Archive & Search Results.
 *
 * Provides a luxury editorial card layout for posts and studies.
 *
 * @package Statement_Collector_Theme
 */

use Statement\Collector\Core\Catalog\Visibility;

defined( 'ABSPATH' ) || exit;

get_header();

$is_search_page = function_exists( 'is_search' ) && is_search();
$has_visibility = class_exists( Visibility::class );
?>
<main id="primary" class="statement-page <?php echo $is_search_page ? 'statement-search' : 'statement-journal'; ?> statement-container--wide">
	<header class="statement-catalog__header">
		<span class="statement-eyebrow">
			<?php echo $is_search_page ? esc_html__( 'Search Record', 'statement-collector-theme' ) : esc_html__( 'Editorial Record', 'statement-collector-theme' ); ?>
		</span>
		<h1 class="statement-catalog__title">
			<?php
			if ( $is_search_page ) {
				/* translators: %s: Search query term */
				printf( esc_html__( 'RESULTS FOR "%s"', 'statement-collector-theme' ), esc_html( get_search_query() ) );
			} else {
				esc_html_e( 'JOURNAL', 'statement-collector-theme' );
			}
			?>
		</h1>
		<p class="statement-catalog__description">
			<?php
			if ( $is_search_page ) {
				esc_html_e( 'Curated findings matching your inquiry.', 'statement-collector-theme' );
			} else {
				esc_html_e( 'Studies in form, texture, silhouette, and the object.', 'statement-collector-theme' );
			}
			?>
		</p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="statement-journal-grid">
			<?php
			while ( have_posts() ) :
				the_post();

				// Exclude internal QA test fixtures from public search and journal loops
				if ( $has_visibility && Visibility::is_fixture_product( get_the_ID() ) ) {
					continue;
				}
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'statement-journal-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="statement-journal-card__media">
							<?php the_post_thumbnail( 'large', array( 'class' => 'statement-journal-card__image' ) ); ?>
						</a>
					<?php endif; ?>
					<div class="statement-journal-card__body">
						<span class="statement-eyebrow"><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
						<h2 class="statement-journal-card__title">
							<a href="<?php the_permalink(); ?>" class="statement-journal-card__link"><?php the_title(); ?></a>
						</h2>
						<div class="statement-journal-card__excerpt">
							<?php the_excerpt(); ?>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="statement-catalog__empty">
			<p>
				<?php
				if ( $is_search_page ) {
					esc_html_e( 'No matching pieces or journal entries found.', 'statement-collector-theme' );
				} else {
					esc_html_e( 'No journal entries published yet.', 'statement-collector-theme' );
				}
				?>
			</p>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
