<?php

defined( 'ABSPATH' ) || exit;

$drop = isset( $args['drop'] ) && is_object( $args['drop'] ) ? $args['drop'] : null;
if ( null === $drop || ! isset( $drop->name ) || '' === trim( (string) $drop->name ) ) {
	return;
}

$drop_url    = isset( $args['drop_url'] ) && is_string( $args['drop_url'] ) && '' !== $args['drop_url'] ? $args['drop_url'] : null;
$description = isset( $drop->description ) && is_string( $drop->description ) ? trim( $drop->description ) : '';
?>
<section class="statement-home-drop statement-container--wide" aria-labelledby="statement-home-drop-title">
	<div class="statement-home-drop__inner">
		<div class="statement-home-drop__content">
			<span class="statement-eyebrow"><?php esc_html_e( 'Current Release', 'statement-collector-theme' ); ?></span>
			<h2 class="statement-home-drop__title" id="statement-home-drop-title"><?php echo esc_html( $drop->name ); ?></h2>
			<?php if ( '' !== $description ) : ?>
				<div class="statement-home-drop__description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( null !== $drop_url ) : ?>
			<div class="statement-home-drop__actions">
				<a class="statement-home-link statement-home-drop__link" href="<?php echo esc_url( $drop_url ); ?>">
					<span><?php esc_html_e( 'VIEW DROP', 'statement-collector-theme' ); ?></span>
					<span aria-hidden="true">&rarr;</span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
