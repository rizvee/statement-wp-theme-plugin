<?php
/**
 * Statement Theme Options Export / Import System.
 *
 * Provides safe, versioned JSON export, import, and reset for theme design settings.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Admin;

defined( 'ABSPATH' ) || exit;

final class OptionsExport {
	const NONCE_ACTION = 'statement_theme_options_action';
	const SCHEMA_VERSION = 1;

	/**
	 * Allowed theme mod keys for import/export.
	 *
	 * @return string[]
	 */
	public static function get_allowed_keys(): array {
		return array(
			// Colors
			'statement_color_bg',
			'statement_color_surface',
			'statement_color_text',
			'statement_color_muted',
			'statement_color_border',
			'statement_color_accent',

			// Layout & Header
			'statement_container_width',
			'statement_container_wide',
			'statement_header_height',
			'statement_shop_columns',
			'statement_show_breadcrumbs',
			'statement_front_page_renderer',
			'statement_enable_hero_slider',
			'statement_enable_email_capture',
			'statement_hero_slider_autoplay',

			// Hero Slider Mods
			'statement_hero_slide_1_image',
			'statement_hero_slide_1_mobile_image',
			'statement_hero_slide_1_eyebrow',
			'statement_hero_slide_1_heading',
			'statement_hero_slide_1_link',
			'statement_hero_slide_1_cta',
			'statement_hero_slide_1_focal',

			'statement_hero_slide_2_image',
			'statement_hero_slide_2_mobile_image',
			'statement_hero_slide_2_eyebrow',
			'statement_hero_slide_2_heading',
			'statement_hero_slide_2_link',
			'statement_hero_slide_2_cta',
			'statement_hero_slide_2_focal',

			'statement_hero_slide_3_image',
			'statement_hero_slide_3_mobile_image',
			'statement_hero_slide_3_eyebrow',
			'statement_hero_slide_3_heading',
			'statement_hero_slide_3_link',
			'statement_hero_slide_3_cta',
			'statement_hero_slide_3_focal',

			'statement_hero_slide_4_image',
			'statement_hero_slide_4_mobile_image',
			'statement_hero_slide_4_eyebrow',
			'statement_hero_slide_4_heading',
			'statement_hero_slide_4_link',
			'statement_hero_slide_4_cta',
			'statement_hero_slide_4_focal',
		);
	}

	/**
	 * Generate clean export data array.
	 *
	 * @return array<string, mixed>
	 */
	public static function export(): array {
		$data = array(
			'theme'          => 'statement-collector-theme',
			'theme_version'  => STATEMENT_COLLECTOR_THEME_VERSION,
			'schema_version' => self::SCHEMA_VERSION,
			'exported_at'    => gmdate( 'Y-m-d H:i:s' ),
			'settings'       => array(),
		);

		foreach ( self::get_allowed_keys() as $key ) {
			$val = get_theme_mod( $key, null );
			if ( null !== $val ) {
				$data['settings'][ $key ] = $val;
			}
		}

		return $data;
	}

	/**
	 * Import and validate JSON payload safely.
	 *
	 * @param string $json_string Raw JSON string.
	 * @return array<string, mixed> Result report.
	 */
	public static function import( string $json_string ): array {
		$report = array(
			'success'  => false,
			'imported' => 0,
			'errors'   => array(),
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			$report['errors'][] = 'Unauthorized capability';
			return $report;
		}

		$decoded = json_decode( $json_string, true );
		if ( ! is_array( $decoded ) || ! isset( $decoded['settings'] ) || ! is_array( $decoded['settings'] ) ) {
			$report['errors'][] = 'Invalid JSON structure or schema';
			return $report;
		}

		if ( ( $decoded['theme'] ?? '' ) !== 'statement-collector-theme' ) {
			$report['errors'][] = 'Unsupported theme settings package';
			return $report;
		}

		// Backup current settings before applying import
		$backup = self::export();
		update_option( '_statement_theme_mods_backup_' . time(), $backup, false );

		$allowed = self::get_allowed_keys();
		$count   = 0;

		foreach ( $decoded['settings'] as $key => $value ) {
			if ( ! in_array( $key, $allowed, true ) ) {
				continue;
			}

			// Strictly sanitize based on key type
			if ( false !== strpos( $key, 'color' ) ) {
				$clean = sanitize_hex_color( (string) $value );
				if ( $clean ) {
					set_theme_mod( $key, $clean );
					$count++;
				}
			} elseif ( false !== strpos( $key, 'width' ) || false !== strpos( $key, 'height' ) || false !== strpos( $key, 'columns' ) || false !== strpos( $key, 'image' ) ) {
				set_theme_mod( $key, absint( $value ) );
				$count++;
			} elseif ( false !== strpos( $key, 'breadcrumbs' ) || false !== strpos( $key, 'enable_' ) || false !== strpos( $key, 'autoplay' ) ) {
				set_theme_mod( $key, (bool) $value );
				$count++;
			} elseif ( 'statement_front_page_renderer' === $key ) {
				set_theme_mod( $key, in_array( $value, array( 'statement', 'content' ), true ) ? (string) $value : 'statement' );
				$count++;
			} else {
				set_theme_mod( $key, trim( sanitize_text_field( (string) $value ) ) );
				$count++;
			}
		}

		$report['success']  = true;
		$report['imported'] = $count;

		return $report;
	}

	/**
	 * Reset theme mods to defaults safely.
	 *
	 * @return bool
	 */
	public static function reset_defaults(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$backup = self::export();
		update_option( '_statement_theme_mods_backup_' . time(), $backup, false );

		foreach ( self::get_allowed_keys() as $key ) {
			remove_theme_mod( $key );
		}

		return true;
	}
}
