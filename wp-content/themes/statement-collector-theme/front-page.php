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
	get_template_part(
		'template-parts/home/hero',
		null,
		array(
			'page_id'  => $page_id,
			'drop_url' => $drop_url,
			'drop'     => $release_data['drop'],
		)
	);

	if ( null !== $release_data['drop'] ) {
		get_template_part(
			'template-parts/home/active-drop',
			null,
			array(
				'drop'     => $release_data['drop'],
				'drop_url' => $drop_url,
			)
		);
	}

	if ( ! empty( $release_data['products'] ) ) {
		get_template_part(
			'template-parts/home/products',
			null,
			array(
				'drop'     => $release_data['drop'],
				'products' => $release_data['products'],
			)
		);
	}

	$show_access = (bool) get_theme_mod( 'statement_home_show_access_capture', get_theme_mod( 'statement_enable_email_capture', false ) );
	if ( $show_access ) {
		get_template_part( 'template-parts/home/email-capture' );
	}
	?>
</main>
<?php
\Statement\Collector\Theme\Hooks::after_main();

get_footer();
