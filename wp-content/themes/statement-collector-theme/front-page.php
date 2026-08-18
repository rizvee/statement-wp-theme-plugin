<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

\Statement\Collector\Theme\Hooks::before_main();

$has_page = have_posts();
if ( $has_page ) {
	the_post();
}

$page_id      = $has_page ? (int) get_the_ID() : 0;
$release_data = get_home_release_data();
$drop_url     = get_home_drop_url( $release_data['drop'] );
?>
<main id="primary" class="statement-home">
	<?php
	// 1. Cinematic Responsive Campaign Hero Slider
	get_template_part(
		'template-parts/home/hero',
		null,
		array(
			'page_id'  => $page_id,
			'drop_url' => $drop_url,
			'drop'     => $release_data['drop'],
		)
	);

	// 2. Current Release (Drop 001) + 2 Product Cards Side-by-Side
	if ( null !== $release_data['drop'] ) {
		get_template_part(
			'template-parts/home/active-drop',
			null,
			array(
				'drop'     => $release_data['drop'],
				'drop_url' => $drop_url,
				'products' => $release_data['products'],
			)
		);
	}

	// 3. Editorial Text-First Drops & Upcoming Drops Directory
	get_template_part(
		'template-parts/home/drops-list',
		null,
		array(
			'drop'     => $release_data['drop'],
			'drop_url' => $drop_url,
		)
	);

	// Optional Access Capture (disabled by default per client feedback)
	$show_access = (bool) get_theme_mod( 'statement_home_show_access_capture', false );
	if ( $show_access ) {
		get_template_part( 'template-parts/home/email-capture' );
	}
	?>
</main>
<?php
\Statement\Collector\Theme\Hooks::after_main();

get_footer();
