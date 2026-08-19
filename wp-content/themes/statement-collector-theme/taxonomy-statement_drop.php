<?php
/**
 * Statement Drop Taxonomy Template — Release Document & Collection Register.
 *
 * Conceives the Drop page as an editorial release document and collection register
 * rather than a standard e-commerce archive grid.
 */

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

$drop = get_queried_object();
$drop_name = is_object( $drop ) && isset( $drop->name ) ? (string) $drop->name : '';
$drop_desc = is_object( $drop ) && isset( $drop->description ) ? (string) $drop->description : '';

// Parse drop number and title if separated by dash or em-dash
$drop_number = '001';
$drop_title  = $drop_name;
if ( preg_match( '/^Drop\s*(\d+)\s*[-—–:]\s*(.+)$/i', $drop_name, $matches ) ) {
	$drop_number = sprintf( '%03d', (int) $matches[1] );
	$drop_title  = trim( $matches[2] );
} elseif ( preg_match( '/^Drop\s*(\d+)/i', $drop_name, $matches ) ) {
	$drop_number = sprintf( '%03d', (int) $matches[1] );
}

$term_id = is_object( $drop ) && isset( $drop->term_id ) ? (int) $drop->term_id : 0;
$is_past = class_exists( PublicApi::class ) && $term_id > 0 ? PublicApi::is_past_drop( $term_id ) : false;
$eyebrow = $is_past ? __( 'RELEASE ARCHIVE', 'statement-collector-theme' ) : __( 'CURRENT RELEASE', 'statement-collector-theme' );
$status  = $is_past ? __( 'ARCHIVED', 'statement-collector-theme' ) : __( 'AVAILABLE', 'statement-collector-theme' );

// Collect products in this drop
$products = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$p = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;
		if ( $p ) {
			$products[] = array(
				'id'        => get_the_ID(),
				'title'     => get_the_title(),
				'permalink' => get_permalink(),
				'price'     => $p->get_price_html(),
				'image_id'  => $p->get_image_id(),
				'image_url' => wp_get_attachment_image_url( $p->get_image_id(), 'medium' ),
				'sku'       => $p->get_sku(),
			);
		}
	}
	wp_reset_postdata();
}

$piece_count = sprintf( '%02d', count( $products ) );

get_header( 'shop' );
?>
<main id="primary" class="statement-drop-document statement-container--wide">
	<!-- Top Metadata Bar -->
	<div class="statement-drop-document__meta-bar">
		<div class="statement-drop-document__meta-left">
			<span class="statement-meta-code">DROP / <?php echo esc_html( $drop_number ); ?></span>
		</div>
		<div class="statement-drop-document__meta-right">
			<span class="statement-badge statement-badge--<?php echo esc_attr( $is_past ? 'archived' : 'live' ); ?>">
				<?php echo esc_html( $eyebrow ); ?>
			</span>
		</div>
	</div>

	<!-- Monolithic Title -->
	<header class="statement-drop-document__header">
		<h1 class="statement-drop-document__title"><?php echo esc_html( strtoupper( $drop_title ) ); ?></h1>
	</header>

	<div class="statement-drop-divider"></div>

	<!-- Two-Column Editorial Overview & Spec Sheet -->
	<section class="statement-drop-document__overview">
		<div class="statement-drop-document__narrative">
			<?php if ( '' !== trim( $drop_desc ) ) : ?>
				<p class="statement-drop-document__description"><?php echo esc_html( $drop_desc ); ?></p>
			<?php else : ?>
				<p class="statement-drop-document__description"><?php esc_html_e( 'A focused study in surface texture, silhouette proportion, and structural wool.', 'statement-collector-theme' ); ?></p>
			<?php endif; ?>
		</div>
		<div class="statement-drop-document__spec">
			<div class="statement-spec-row">
				<span class="statement-spec-label"><?php esc_html_e( 'STATUS', 'statement-collector-theme' ); ?></span>
				<span class="statement-spec-value"><?php echo esc_html( $status ); ?></span>
			</div>
			<div class="statement-spec-row">
				<span class="statement-spec-label"><?php esc_html_e( 'EDITION', 'statement-collector-theme' ); ?></span>
				<span class="statement-spec-value">DROP <?php echo esc_html( $drop_number ); ?></span>
			</div>
			<div class="statement-spec-row">
				<span class="statement-spec-label"><?php esc_html_e( 'PIECES', 'statement-collector-theme' ); ?></span>
				<span class="statement-spec-value"><?php echo esc_html( $piece_count ); ?></span>
			</div>
		</div>
	</section>

	<div class="statement-drop-divider"></div>

	<!-- Collection Register -->
	<section class="statement-drop-document__register" aria-label="<?php esc_attr_e( 'Collection Pieces', 'statement-collector-theme' ); ?>">
		<header class="statement-register-header">
			<span class="statement-meta-code"><?php esc_html_e( 'COLLECTION REGISTER', 'statement-collector-theme' ); ?></span>
		</header>

		<?php if ( ! empty( $products ) ) : ?>
			<ol class="statement-register-list">
				<?php foreach ( $products as $index => $item ) : ?>
					<?php $num = sprintf( '%02d', $index + 1 ); ?>
					<li class="statement-register-item">
						<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="statement-register-link">
							<span class="statement-register-index"><?php echo esc_html( $num ); ?></span>
							<span class="statement-register-title"><?php echo esc_html( strtoupper( $item['title'] ) ); ?></span>
							<span class="statement-register-price"><?php echo wp_kses_post( $item['price'] ); ?></span>
							<span class="statement-register-arrow" aria-hidden="true"><?php esc_html_e( 'VIEW PIECE →', 'statement-collector-theme' ); ?></span>
							<?php if ( ! empty( $item['image_url'] ) ) : ?>
								<div class="statement-register-preview" aria-hidden="true">
									<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="" loading="lazy" />
								</div>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<div class="statement-catalog-empty">
				<p class="statement-catalog-empty__message"><?php esc_html_e( 'No pieces currently registered in this release.', 'statement-collector-theme' ); ?></p>
			</div>
		<?php endif; ?>
	</section>
</main>
<?php
get_footer( 'shop' );
