<?php
/**
 * Dedicated Archive page template for historical Drop provenance and archived pieces.
 *
 * Template Name: Statement Archive
 */

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

get_header();

$has_api  = class_exists( PublicApi::class );
$products = $has_api ? PublicApi::get_archive_products( 24 ) : array();
$drops    = $has_api ? PublicApi::get_past_drops() : array();
?>
<main id="primary" class="statement-page statement-archive statement-container">
	<header class="statement-archive__header statement-stack">
		<h1 class="statement-archive__title"><?php esc_html_e( 'Archive', 'statement-collector-theme' ); ?></h1>
		<p class="statement-archive__subtitle"><?php esc_html_e( 'Permanent record of past releases and historical pieces. Crafted. Limited. Permanent Archive.', 'statement-collector-theme' ); ?></p>
	</header>

	<?php if ( ! empty( $products ) ) : ?>
		<section class="statement-archive__grid statement-grid" aria-label="<?php esc_attr_e( 'Archived Pieces', 'statement-collector-theme' ); ?>">
			<?php
			foreach ( $products as $product ) {
				get_template_part(
					'template-parts/product/card',
					null,
					array(
						'product'       => $product,
						'heading_level' => 2,
					)
				);
			}
			?>
		</section>
	<?php else : ?>
		<div class="statement-archive__empty">
			<p><?php esc_html_e( 'No archived pieces recorded yet.', 'statement-collector-theme' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $drops ) ) : ?>
		<section class="statement-archive__drops statement-stack" aria-label="<?php esc_attr_e( 'Historical Drops', 'statement-collector-theme' ); ?>">
			<h2><?php esc_html_e( 'Past Drops', 'statement-collector-theme' ); ?></h2>
			<ul class="statement-archive__drops-list">
				<?php foreach ( $drops as $drop ) : ?>
					<li class="statement-archive__drop-item">
						<a href="<?php echo esc_url( get_term_link( $drop ) ); ?>">
							<span class="statement-archive__drop-name"><?php echo esc_html( $drop->name ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
