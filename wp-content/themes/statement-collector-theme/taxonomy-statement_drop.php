<?php

use Statement\Collector\Core\PublicApi;

defined( 'ABSPATH' ) || exit;

$drop = get_queried_object();
$drop_name = is_object( $drop ) && isset( $drop->name ) ? (string) $drop->name : '';
$drop_desc = is_object( $drop ) && isset( $drop->description ) ? (string) $drop->description : '';

// Parse drop number and title if separated by dash or em-dash
$drop_number = '';
$drop_title  = $drop_name;
if ( preg_match( '/^(Drop\s*\d+)\s*[-—–]\s*(.+)$/i', $drop_name, $matches ) ) {
	$drop_number = trim( $matches[1] );
	$drop_title  = trim( $matches[2] );
}

$term_id = is_object( $drop ) && isset( $drop->term_id ) ? (int) $drop->term_id : 0;
$is_past = class_exists( PublicApi::class ) && $term_id > 0 ? PublicApi::is_past_drop( $term_id ) : false;
$eyebrow = $is_past ? __( 'RELEASE ARCHIVE', 'statement-collector-theme' ) : __( 'CURRENT RELEASE', 'statement-collector-theme' );
$status  = $is_past ? __( 'ARCHIVED', 'statement-collector-theme' ) : __( 'AVAILABLE', 'statement-collector-theme' );

if ( ! function_exists( 'woocommerce_product_loop' ) ) {
	get_header();
	?>
	<main id="primary" class="statement-drop-page statement-catalog statement-container--wide">
		<header class="statement-drop-page__header">
			<div class="statement-drop-page__meta-row">
				<span class="statement-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
				<span class="statement-badge statement-badge--<?php echo esc_attr( $is_past ? 'archived' : 'live' ); ?>"><?php echo esc_html( $status ); ?></span>
			</div>
			<h1 class="statement-drop-page__title">
				<?php if ( '' !== $drop_number ) : ?>
					<span class="statement-drop-page__number"><?php echo esc_html( strtoupper( $drop_number ) ); ?></span>
				<?php endif; ?>
				<span class="statement-drop-page__name"><?php echo esc_html( strtoupper( $drop_title ) ); ?></span>
			</h1>
			<?php if ( '' !== trim( $drop_desc ) ) : ?>
				<div class="statement-drop-page__description"><?php echo wp_kses_post( wpautop( $drop_desc ) ); ?></div>
			<?php endif; ?>
		</header>
		<?php \Statement\Collector\Theme\render_catalog_empty_state(); ?>
	</main>
	<?php
	get_footer();
	return;
}

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );
?>
<div class="statement-drop-page statement-catalog statement-container--wide">
	<header class="statement-drop-page__header">
		<div class="statement-drop-page__meta-row">
			<span class="statement-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<span class="statement-badge statement-badge--<?php echo esc_attr( $is_past ? 'archived' : 'live' ); ?>"><?php echo esc_html( $status ); ?></span>
		</div>
		<h1 class="statement-drop-page__title">
			<?php if ( '' !== $drop_number ) : ?>
				<span class="statement-drop-page__number"><?php echo esc_html( strtoupper( $drop_number ) ); ?></span>
			<?php endif; ?>
			<span class="statement-drop-page__name"><?php echo esc_html( strtoupper( $drop_title ) ); ?></span>
		</h1>
		<?php if ( '' !== trim( $drop_desc ) ) : ?>
			<div class="statement-drop-page__description"><?php echo wp_kses_post( wpautop( $drop_desc ) ); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( woocommerce_product_loop() ) : ?>
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
		<?php woocommerce_product_loop_start(); ?>
		<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
				?>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php woocommerce_product_loop_end(); ?>
		<?php do_action( 'woocommerce_after_shop_loop' ); ?>
	<?php else : ?>
		<?php do_action( 'woocommerce_no_products_found' ); ?>
	<?php endif; ?>
</div>
<?php
do_action( 'woocommerce_after_main_content' );
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
