<?php

defined( 'ABSPATH' ) || exit;

$drop = get_queried_object();

if ( ! function_exists( 'woocommerce_product_loop' ) ) {
	get_header();
	?>
	<main id="primary" class="statement-catalog statement-container--wide">
		<header class="statement-catalog__header">
			<h1><?php echo isset( $drop->name ) ? esc_html( $drop->name ) : ''; ?></h1>
			<?php if ( isset( $drop->description ) && '' !== trim( (string) $drop->description ) ) : ?>
				<div class="statement-catalog__description"><?php echo wp_kses_post( wpautop( $drop->description ) ); ?></div>
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
<div class="statement-catalog statement-container--wide">
	<header class="statement-catalog__header">
		<h1><?php echo isset( $drop->name ) ? esc_html( $drop->name ) : ''; ?></h1>
		<?php if ( isset( $drop->description ) && '' !== trim( (string) $drop->description ) ) : ?>
			<div class="statement-catalog__description"><?php echo wp_kses_post( wpautop( $drop->description ) ); ?></div>
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
