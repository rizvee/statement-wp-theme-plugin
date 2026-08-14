<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

get_header();

$has_page = have_posts();
if ( $has_page ) {
	the_post();
}

$page_id       = $has_page ? (int) get_the_ID() : 0;
$release_data  = get_home_release_data();
$drop_url      = get_home_drop_url( $release_data['drop'] );
$archive_url   = get_home_archive_url();
$has_editorial = has_home_editorial_content( $page_id );
?>
<main id="primary" class="statement-home">
	<?php
	get_template_part(
		'template-parts/home/hero',
		null,
		array(
			'page_id'  => $page_id,
			'drop_url' => $drop_url,
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

	if ( $has_editorial ) {
		get_template_part( 'template-parts/home/editorial' );
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

	get_template_part( 'template-parts/home/principle' );

	if ( null !== $archive_url ) {
		get_template_part(
			'template-parts/home/archive-link',
			null,
			array( 'archive_url' => $archive_url )
		);
	}
	?>
</main>
<?php
get_footer();
