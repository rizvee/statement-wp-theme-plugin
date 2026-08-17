<?php
/**
 * Authoritative Statement Design Tokens Layer.
 *
 * Exposes core design tokens and converts Customizer theme_mods
 * into CSS Custom Properties output in the document head.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

final class DesignTokens {
	/**
	 * Get default design token dictionary.
	 *
	 * @return array<string, string>
	 */
	public static function get_defaults(): array {
		return array(
			// Palette
			'color_bg'          => '#FBFBFA',
			'color_surface'     => '#FFFFFF',
			'color_text'        => '#111111',
			'color_muted'       => '#666666',
			'color_border'      => '#E5E5E0',
			'color_accent'      => '#111111',

			// Typography
			'font_body'         => 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
			'font_heading'      => 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
			'font_editorial'    => '"Instrument Serif", "Iowan Old Style", "Times New Roman", serif',

			// Layout
			'container_width'   => '1200px',
			'container_wide'    => '1440px',
			'gutter'            => '24px',
			'header_height'     => '64px',

			// Radius & Motion
			'radius_sm'         => '0px',
			'radius_base'       => '0px',
			'motion_fast'       => '150ms cubic-bezier(0.16, 1, 0.3, 1)',
			'motion_base'       => '250ms cubic-bezier(0.16, 1, 0.3, 1)',
		);
	}

	/**
	 * Output inline CSS custom properties based on theme mods and defaults.
	 */
	public static function output_css_variables(): void {
		$defaults = self::get_defaults();

		$color_bg      = sanitize_hex_color( (string) get_theme_mod( 'statement_color_bg', $defaults['color_bg'] ) ) ?: $defaults['color_bg'];
		$color_surface = sanitize_hex_color( (string) get_theme_mod( 'statement_color_surface', $defaults['color_surface'] ) ) ?: $defaults['color_surface'];
		$color_text    = sanitize_hex_color( (string) get_theme_mod( 'statement_color_text', $defaults['color_text'] ) ) ?: $defaults['color_text'];
		$color_muted   = sanitize_hex_color( (string) get_theme_mod( 'statement_color_muted', $defaults['color_muted'] ) ) ?: $defaults['color_muted'];
		$color_border  = sanitize_hex_color( (string) get_theme_mod( 'statement_color_border', $defaults['color_border'] ) ) ?: $defaults['color_border'];
		$color_accent  = sanitize_hex_color( (string) get_theme_mod( 'statement_color_accent', $defaults['color_accent'] ) ) ?: $defaults['color_accent'];

		$container_width = absint( get_theme_mod( 'statement_container_width', 1200 ) );
		$container_wide  = absint( get_theme_mod( 'statement_container_wide', 1440 ) );
		$header_height   = absint( get_theme_mod( 'statement_header_height', 64 ) );

		$css = ":root {\n";
		$css .= "  --statement-color-bg: {$color_bg};\n";
		$css .= "  --statement-color-surface: {$color_surface};\n";
		$css .= "  --statement-color-text: {$color_text};\n";
		$css .= "  --statement-color-muted: {$color_muted};\n";
		$css .= "  --statement-color-border: {$color_border};\n";
		$css .= "  --statement-color-accent: {$color_accent};\n";
		$css .= "  --statement-font-body: {$defaults['font_body']};\n";
		$css .= "  --statement-font-heading: {$defaults['font_heading']};\n";
		$css .= "  --statement-font-editorial: {$defaults['font_editorial']};\n";
		$css .= "  --statement-container: {$container_width}px;\n";
		$css .= "  --statement-container-wide: {$container_wide}px;\n";
		$css .= "  --statement-header-height: {$header_height}px;\n";
		$css .= "  --statement-gutter: {$defaults['gutter']};\n";
		$css .= "  --statement-radius-sm: {$defaults['radius_sm']};\n";
		$css .= "  --statement-radius-base: {$defaults['radius_base']};\n";
		$css .= "  --statement-motion-fast: {$defaults['motion_fast']};\n";
		$css .= "  --statement-motion-base: {$defaults['motion_base']};\n";
		$css .= "}\n";

		/**
		 * Filter generated design tokens CSS.
		 *
		 * @param string $css Inline CSS block.
		 */
		$css = apply_filters( 'statement_theme_design_tokens_css', $css );

		echo "<style id=\"statement-design-tokens\">\n" . wp_strip_all_tags( $css ) . "\n</style>\n";
	}
}
