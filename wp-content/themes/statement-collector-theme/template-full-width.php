<?php
/**
 * Template Name: Statement Full Width
 * Description: Full-width page template retaining header and footer without container width constraints.
 *
 * @package Statement_Collector_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

\Statement\Collector\Theme\Hooks::before_main();
?>

<main id="main" class="statement-main statement-main--full-width" role="main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>

<?php
\Statement\Collector\Theme\Hooks::after_main();

get_footer();
