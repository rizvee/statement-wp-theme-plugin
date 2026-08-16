<?php

defined( 'ABSPATH' ) || exit;

$archive_url = isset( $args['archive_url'] ) && is_string( $args['archive_url'] ) && '' !== $args['archive_url'] ? $args['archive_url'] : null;
if ( null === $archive_url ) {
	return;
}
?>
<section class="statement-home-archive statement-container--wide" aria-labelledby="statement-home-archive-title">
	<div class="statement-home-archive__inner">
		<span class="statement-eyebrow"><?php esc_html_e( 'The Archive', 'statement-collector-theme' ); ?></span>
		<h2 id="statement-home-archive-title" class="statement-home-archive__title"><?php esc_html_e( 'Past pieces remain part of the record.', 'statement-collector-theme' ); ?></h2>
		<a class="statement-home-link statement-home-archive__link" href="<?php echo esc_url( $archive_url ); ?>">
			<?php esc_html_e( 'VIEW ARCHIVE', 'statement-collector-theme' ); ?> <span aria-hidden="true">&rarr;</span>
		</a>
	</div>
</section>
