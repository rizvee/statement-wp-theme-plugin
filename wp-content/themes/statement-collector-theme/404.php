<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="statement-404 statement-container">
	<div class="statement-404__inner">
		<p class="statement-eyebrow"><?php esc_html_e( '404', 'statement-collector-theme' ); ?></p>
		<h1 class="statement-404__title"><?php esc_html_e( 'NOTHING HERE.', 'statement-collector-theme' ); ?></h1>
		<p class="statement-404__message"><?php esc_html_e( 'The requested page or release could not be located.', 'statement-collector-theme' ); ?></p>
		<div class="statement-404__actions">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="statement-404__cta">
				<span><?php esc_html_e( 'RETURN HOME', 'statement-collector-theme' ); ?></span>
				<span aria-hidden="true">&rarr;</span>
			</a>
		</div>
	</div>
</main>
<?php
get_footer();
