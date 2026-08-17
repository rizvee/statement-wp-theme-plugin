<?php
/**
 * Statement System Health & Prerequisite Diagnostic.
 *
 * Checks WordPress environment, active plugins, required pages, and configuration state.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Admin;

use Statement\Collector\Theme\Compatibility\Seo;

defined( 'ABSPATH' ) || exit;

final class Health {
	/**
	 * Run comprehensive environment health audit.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function audit(): array {
		$checks = array();

		// 1. Versions
		$checks['wordpress'] = array(
			'label'   => __( 'WordPress Version', 'statement-collector-theme' ),
			'value'   => get_bloginfo( 'version' ),
			'status'  => version_compare( get_bloginfo( 'version' ), '6.0', '>=' ) ? 'PASS' : 'REVIEW',
			'message' => version_compare( get_bloginfo( 'version' ), '6.0', '>=' ) ? 'Compatible' : 'WordPress 6.0+ recommended',
		);

		$checks['php'] = array(
			'label'   => __( 'PHP Version', 'statement-collector-theme' ),
			'value'   => PHP_VERSION,
			'status'  => version_compare( PHP_VERSION, '7.4', '>=' ) ? 'PASS' : 'ACTION',
			'message' => version_compare( PHP_VERSION, '7.4', '>=' ) ? 'Compatible' : 'PHP 7.4+ required',
		);

		$checks['theme'] = array(
			'label'   => __( 'Statement Theme Version', 'statement-collector-theme' ),
			'value'   => STATEMENT_COLLECTOR_THEME_VERSION,
			'status'  => 'PASS',
			'message' => 'Active',
		);

		// 2. Core Plugin Status
		$core_active = class_exists( '\Statement\Collector\Core\Plugin' );
		$checks['core_plugin'] = array(
			'label'   => __( 'Statement Collector Core Plugin', 'statement-collector-theme' ),
			'value'   => defined( 'STATEMENT_COLLECTOR_CORE_VERSION' ) ? STATEMENT_COLLECTOR_CORE_VERSION : 'Inactive',
			'status'  => $core_active ? 'PASS' : 'ACTION',
			'message' => $core_active ? 'Active & Healthy' : 'Core plugin is required for release lifecycle & private access',
		);

		// 3. WooCommerce Status
		$woo_active = class_exists( 'WooCommerce' );
		$checks['woocommerce'] = array(
			'label'   => __( 'WooCommerce Commerce Engine', 'statement-collector-theme' ),
			'value'   => defined( 'WC_VERSION' ) ? WC_VERSION : 'Inactive',
			'status'  => $woo_active ? 'PASS' : 'ACTION',
			'message' => $woo_active ? 'Active' : 'WooCommerce is required for commerce catalog and checkout',
		);

		// 4. Critical Store Pages
		$checks['page_home'] = self::check_page_status( 'Home / Front Page', get_option( 'page_on_front' ) );
		$checks['page_shop'] = self::check_page_status( 'Shop Page', function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0 );
		$checks['page_cart'] = self::check_page_status( 'Cart / Bag Page', function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 0 );
		$checks['page_checkout'] = self::check_page_status( 'Checkout Page', function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'checkout' ) : 0 );

		// 5. Permalinks
		$permalink_structure = get_option( 'permalink_structure' );
		$checks['permalinks'] = array(
			'label'   => __( 'Permalinks Structure', 'statement-collector-theme' ),
			'value'   => ! empty( $permalink_structure ) ? $permalink_structure : 'Plain / Default',
			'status'  => ! empty( $permalink_structure ) ? 'PASS' : 'ACTION',
			'message' => ! empty( $permalink_structure ) ? 'Pretty permalinks active' : 'Pretty permalinks (e.g. /%postname%/) required for clean drop URLs',
		);

		// 6. Elementor Compatibility
		$elementor_active = did_action( 'elementor/loaded' );
		$checks['elementor'] = array(
			'label'   => __( 'Elementor Page Builder', 'statement-collector-theme' ),
			'value'   => $elementor_active ? 'Active' : 'Not installed / Inactive',
			'status'  => 'PASS',
			'message' => $elementor_active ? 'Theme Locations registered' : 'Native Statement templates active',
		);

		// 7. SEO Compatibility
		$has_seo = Seo::has_seo_plugin();
		$checks['seo'] = array(
			'label'   => __( 'SEO Plugin Integration', 'statement-collector-theme' ),
			'value'   => $has_seo ? 'Dedicated SEO Plugin Active' : 'Theme Native Meta',
			'status'  => 'PASS',
			'message' => $has_seo ? 'Coexisting peacefully without duplicate tags' : 'Using native title-tag support',
		);

		return $checks;
	}

	/**
	 * Helper to check page status.
	 *
	 * @param string $label Page label.
	 * @param mixed $page_id Page ID.
	 * @return array<string, mixed>
	 */
	private static function check_page_status( string $label, $page_id ): array {
		$id = absint( $page_id );
		$page = $id > 0 ? get_post( $id ) : null;
		$is_published = is_object( $page ) && 'publish' === $page->post_status;

		return array(
			'label'   => $label,
			'value'   => $is_published ? "{$page->post_title} (ID: {$id})" : 'Not Assigned / Missing',
			'status'  => $is_published ? 'PASS' : 'REVIEW',
			'message' => $is_published ? 'Configured' : 'Needs assignment in Settings > Reading or WooCommerce',
		);
	}
}
