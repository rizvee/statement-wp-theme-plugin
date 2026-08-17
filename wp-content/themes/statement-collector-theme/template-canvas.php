<?php
/**
 * Template Name: Statement Canvas
 * Description: Pure canvas page template omitting theme header and footer for standalone page builders.
 *
 * @package Statement_Collector_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'statement-canvas' ); ?>>
<?php wp_body_open(); ?>

<main id="main" class="statement-canvas-main" role="main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>

<?php wp_footer(); ?>
</body>
</html>
