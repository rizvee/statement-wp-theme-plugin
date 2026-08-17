<?php
/**
 * Header template.
 *
 * @package Statement_Collector_Theme
 */

use Statement\Collector\Theme\Hooks;
use Statement\Collector\Theme\Compatibility\Elementor;

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'statement-collector-theme' ); ?></a>
<?php
Hooks::before_header();
if ( ! Elementor::do_header() ) {
	get_template_part( 'template-parts/header/site-header' );
}
Hooks::after_header();
