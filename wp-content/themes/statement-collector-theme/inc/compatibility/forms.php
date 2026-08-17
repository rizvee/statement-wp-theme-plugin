<?php
/**
 * Forms Plugins Compatibility Adapter.
 *
 * Scopes styling for Contact Form 7, WPForms, and MailPoet without global overrides.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class Forms {
	/**
	 * Boot forms compatibility.
	 */
	public static function boot(): void {
		add_filter( 'wpcf7_autop_or_not', '__return_false' );
	}
}
