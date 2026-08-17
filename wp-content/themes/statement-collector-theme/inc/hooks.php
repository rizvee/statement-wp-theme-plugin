<?php
/**
 * Statement Theme Public Extension API & Hook Registration.
 *
 * Defines canonical layout action and filter extension points.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

final class Hooks {
	/**
	 * Boot default hook handlers and filters.
	 */
	public static function boot(): void {
		add_action( 'wp_head', array( DesignTokens::class, 'output_css_variables' ), 1 );
		add_filter( 'body_class', array( self::class, 'filter_body_classes' ) );
	}

	/**
	 * Add contextual layout and design classes to body.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function filter_body_classes( array $classes ): array {
		$classes[] = 'statement-theme';
		$classes[] = 'statement-v3';

		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			$classes[] = 'statement-front-page';
		}

		$layout = PageMeta::get_layout_override();
		if ( 'wide' === $layout ) {
			$classes[] = 'statement-layout--wide';
		} elseif ( 'full' === $layout ) {
			$classes[] = 'statement-layout--full';
		} elseif ( 'contained' === $layout ) {
			$classes[] = 'statement-layout--contained';
		}

		$header_style = PageMeta::get_header_override();
		if ( 'transparent' === $header_style ) {
			$classes[] = 'statement-header--transparent';
		} elseif ( 'hidden' === $header_style ) {
			$classes[] = 'statement-header--hidden';
		}

		return $classes;
	}

	/**
	 * Fire before header action hook.
	 */
	public static function before_header(): void {
		do_action( 'statement_theme_before_header' );
	}

	/**
	 * Fire after header action hook.
	 */
	public static function after_header(): void {
		do_action( 'statement_theme_after_header' );
	}

	/**
	 * Fire before main container action hook.
	 */
	public static function before_main(): void {
		do_action( 'statement_theme_before_main' );
	}

	/**
	 * Fire after main container action hook.
	 */
	public static function after_main(): void {
		do_action( 'statement_theme_after_main' );
	}

	/**
	 * Fire before product card hook.
	 *
	 * @param mixed $product WC Product instance.
	 */
	public static function before_product_card( $product = null ): void {
		do_action( 'statement_theme_before_product_card', $product );
	}

	/**
	 * Fire after product card hook.
	 *
	 * @param mixed $product WC Product instance.
	 */
	public static function after_product_card( $product = null ): void {
		do_action( 'statement_theme_after_product_card', $product );
	}

	/**
	 * Fire before footer action hook.
	 */
	public static function before_footer(): void {
		do_action( 'statement_theme_before_footer' );
	}

	/**
	 * Fire after footer action hook.
	 */
	public static function after_footer(): void {
		do_action( 'statement_theme_after_footer' );
	}

	/**
	 * Get shop columns passing through public filter.
	 *
	 * @param int $default Default columns.
	 * @return int
	 */
	public static function get_shop_columns( int $default = 3 ): int {
		$customizer_cols = absint( get_theme_mod( 'statement_shop_columns', $default ) );
		$cols = $customizer_cols > 0 ? $customizer_cols : $default;

		/**
		 * Filter shop product columns.
		 *
		 * @param int $cols Number of columns (2, 3, or 4).
		 */
		return (int) apply_filters( 'statement_theme_shop_columns', $cols );
	}

	/**
	 * Determine whether breadcrumbs should render.
	 *
	 * @return bool
	 */
	public static function show_breadcrumbs(): bool {
		$default = (bool) get_theme_mod( 'statement_show_breadcrumbs', false );

		/**
		 * Filter breadcrumbs display visibility.
		 *
		 * @param bool $show True to render breadcrumbs.
		 */
		return (bool) apply_filters( 'statement_theme_show_breadcrumbs', $default );
	}

	/**
	 * Filter design tokens CSS block.
	 *
	 * @param string $css CSS block.
	 * @return string
	 */
	public static function filter_design_tokens_css( string $css ): string {
		/**
		 * Filter generated design tokens CSS.
		 *
		 * @param string $css Inline CSS block.
		 */
		return (string) apply_filters( 'statement_theme_design_tokens_css', $css );
	}
}
