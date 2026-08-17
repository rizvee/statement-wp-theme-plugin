<?php
/**
 * Footer template.
 *
 * @package Statement_Collector_Theme
 */

use Statement\Collector\Theme\Hooks;
use Statement\Collector\Theme\PageMeta;
use Statement\Collector\Theme\Compatibility\Elementor;

defined( 'ABSPATH' ) || exit;

Hooks::before_footer();
if ( PageMeta::is_footer_visible() ) {
	if ( ! Elementor::do_footer() ) {
		get_template_part( 'template-parts/footer/site-footer' );
	}
}
Hooks::after_footer();
?>
<?php wp_footer(); ?>
</body>
</html>
