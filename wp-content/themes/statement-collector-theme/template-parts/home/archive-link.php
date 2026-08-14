<?php

defined( 'ABSPATH' ) || exit;

$archive_url = isset( $args['archive_url'] ) && is_string( $args['archive_url'] ) && '' !== $args['archive_url'] ? $args['archive_url'] : null;
if ( null === $archive_url ) {
	return;
}
?>
<section class="statement-home-archive statement-container--wide" aria-labelledby="statement-home-archive-title">
	<h2 id="statement-home-archive-title"><?php esc_html_e( 'PAST RELEASES', 'statement-collector-theme' ); ?></h2>
	<a class="statement-home-link statement-home-archive__link" href="<?php echo esc_url( $archive_url ); ?>">
		<?php esc_html_e( 'ARCHIVE', 'statement-collector-theme' ); ?> <span aria-hidden="true">&rarr;</span>
	</a>
</section>
