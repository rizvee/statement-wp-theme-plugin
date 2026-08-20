<?php
/**
 * Product details accordion disclosures for Statement Collector's Piece.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_object( $product ) ) {
	return;
}

$description = $product->get_description();
?>
<section class="statement-product__disclosures" aria-label="<?php esc_attr_e( 'Product details and specifications', 'statement-collector-theme' ); ?>">
	<?php if ( ! empty( trim( (string) $description ) ) ) : ?>
		<details class="statement-disclosure" open>
			<summary class="statement-disclosure__summary">
				<span class="statement-disclosure__title"><?php esc_html_e( 'PRODUCT DETAILS', 'statement-collector-theme' ); ?></span>
				<span class="statement-disclosure__icon" aria-hidden="true">
					<?php \Statement\Collector\Theme\render_statement_icon( 'plus', array( 'class' => 'statement-disclosure__icon-plus' ) ); ?>
					<?php \Statement\Collector\Theme\render_statement_icon( 'minus', array( 'class' => 'statement-disclosure__icon-minus' ) ); ?>
				</span>
			</summary>
			<div class="statement-disclosure__content statement-prose">
				<?php echo wp_kses_post( wpautop( $description ) ); ?>
			</div>
		</details>
	<?php endif; ?>

	<details class="statement-disclosure">
		<summary class="statement-disclosure__summary">
			<span class="statement-disclosure__title"><?php esc_html_e( 'SIZE & FIT', 'statement-collector-theme' ); ?></span>
			<span class="statement-disclosure__icon" aria-hidden="true">
				<?php \Statement\Collector\Theme\render_statement_icon( 'plus', array( 'class' => 'statement-disclosure__icon-plus' ) ); ?>
				<?php \Statement\Collector\Theme\render_statement_icon( 'minus', array( 'class' => 'statement-disclosure__icon-minus' ) ); ?>
			</span>
		</summary>
		<div class="statement-disclosure__content statement-prose">
			<p><?php esc_html_e( 'Engineered with a contemporary tailored drape. Designed to fit true to size. For a more relaxed, layered silhouette, consider taking one size up.', 'statement-collector-theme' ); ?></p>
			<p><?php esc_html_e( 'Consult the Body Size Guide above for detailed metric dimensions.', 'statement-collector-theme' ); ?></p>
		</div>
	</details>

	<details class="statement-disclosure">
		<summary class="statement-disclosure__summary">
			<span class="statement-disclosure__title"><?php esc_html_e( 'DISPATCH & CARE', 'statement-collector-theme' ); ?></span>
			<span class="statement-disclosure__icon" aria-hidden="true">
				<?php \Statement\Collector\Theme\render_statement_icon( 'plus', array( 'class' => 'statement-disclosure__icon-plus' ) ); ?>
				<?php \Statement\Collector\Theme\render_statement_icon( 'minus', array( 'class' => 'statement-disclosure__icon-minus' ) ); ?>
			</span>
		</summary>
		<div class="statement-disclosure__content statement-prose">
			<p><?php esc_html_e( 'Orders are dispatched with tracking provided upon fulfillment. Spot clean or professional dry clean only to preserve surface integrity.', 'statement-collector-theme' ); ?></p>
		</div>
	</details>
</section>
