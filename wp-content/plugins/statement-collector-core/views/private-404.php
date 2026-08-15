<?php

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main statement-not-found">
	<header>
		<h1><?php echo esc_html__( 'Not found', 'statement-collector-core' ); ?></h1>
	</header>
	<p><?php echo esc_html__( 'The requested page could not be found.', 'statement-collector-core' ); ?></p>
</main>
<?php
get_footer();
