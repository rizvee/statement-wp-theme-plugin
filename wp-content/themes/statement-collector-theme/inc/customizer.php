<?php
/**
 * Register Statement Theme Customizer Settings, Panels, and Controls.
 *
 * Provides structured customization for Global design tokens, Header,
 * Shop & Catalog, and the Homepage Hero Slider with strict sanitization.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Register customizer settings and controls.
 *
 * @param \WP_Customize_Manager $wp_customize Customizer manager.
 */
function customize_register( \WP_Customize_Manager $wp_customize ): void {
	// ==========================================
	// 1. PANEL: STATEMENT DESIGN SYSTEM
	// ==========================================
	$wp_customize->add_panel(
		'statement_theme_panel',
		array(
			'title'       => __( 'Statement Design Settings', 'statement-collector-theme' ),
			'description' => __( 'Customize Statement luxury design tokens, layout options, and hero slider.', 'statement-collector-theme' ),
			'priority'    => 25,
		)
	);

	// ==========================================
	// SECTION 1: GLOBAL PALETTE & LAYOUT
	// ==========================================
	$wp_customize->add_section(
		'statement_global_section',
		array(
			'title'       => __( 'Global Palette & Layout', 'statement-collector-theme' ),
			'description' => __( 'Configure foundational gallery colors and layout widths.', 'statement-collector-theme' ),
			'panel'       => 'statement_theme_panel',
			'priority'    => 10,
		)
	);

	// Color: Background
	$wp_customize->add_setting(
		'statement_color_bg',
		array(
			'default'           => '#FBFBFA',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'statement_color_bg',
			array(
				'label'   => __( 'Background Color', 'statement-collector-theme' ),
				'section' => 'statement_global_section',
			)
		)
	);

	// Color: Surface
	$wp_customize->add_setting(
		'statement_color_surface',
		array(
			'default'           => '#FFFFFF',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'statement_color_surface',
			array(
				'label'   => __( 'Card / Surface Color', 'statement-collector-theme' ),
				'section' => 'statement_global_section',
			)
		)
	);

	// Color: Text (Ink)
	$wp_customize->add_setting(
		'statement_color_text',
		array(
			'default'           => '#111111',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'statement_color_text',
			array(
				'label'   => __( 'Primary Text (Ink)', 'statement-collector-theme' ),
				'section' => 'statement_global_section',
			)
		)
	);

	// Color: Muted Text
	$wp_customize->add_setting(
		'statement_color_muted',
		array(
			'default'           => '#666666',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'statement_color_muted',
			array(
				'label'   => __( 'Muted Secondary Text', 'statement-collector-theme' ),
				'section' => 'statement_global_section',
			)
		)
	);

	// Color: Border
	$wp_customize->add_setting(
		'statement_color_border',
		array(
			'default'           => '#E5E5E0',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'statement_color_border',
			array(
				'label'   => __( 'Border / Rule Color', 'statement-collector-theme' ),
				'section' => 'statement_global_section',
			)
		)
	);

	// Container Width
	$wp_customize->add_setting(
		'statement_container_width',
		array(
			'default'           => 1200,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_container_width',
		array(
			'label'   => __( 'Standard Container Width (px)', 'statement-collector-theme' ),
			'section' => 'statement_global_section',
			'type'    => 'number',
		)
	);

	// Wide Container Width
	$wp_customize->add_setting(
		'statement_container_wide',
		array(
			'default'           => 1440,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_container_wide',
		array(
			'label'   => __( 'Wide Container Width (px)', 'statement-collector-theme' ),
			'section' => 'statement_global_section',
			'type'    => 'number',
		)
	);

	// ==========================================
	// SECTION 2: HEADER & NAVIGATION
	// ==========================================
	$wp_customize->add_section(
		'statement_header_section',
		array(
			'title'       => __( 'Header & Navigation', 'statement-collector-theme' ),
			'description' => __( 'Configure header dimensions and navigation options.', 'statement-collector-theme' ),
			'panel'       => 'statement_theme_panel',
			'priority'    => 20,
		)
	);

	// Header Height
	$wp_customize->add_setting(
		'statement_header_height',
		array(
			'default'           => 64,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_header_height',
		array(
			'label'   => __( 'Desktop Header Height (px)', 'statement-collector-theme' ),
			'section' => 'statement_header_section',
			'type'    => 'number',
		)
	);

	// ==========================================
	// SECTION 3: SHOP & CATALOG
	// ==========================================
	$wp_customize->add_section(
		'statement_shop_section',
		array(
			'title'       => __( 'Shop & Catalog', 'statement-collector-theme' ),
			'description' => __( 'Configure product catalog presentation.', 'statement-collector-theme' ),
			'panel'       => 'statement_theme_panel',
			'priority'    => 30,
		)
	);

	// Shop Columns
	$wp_customize->add_setting(
		'statement_shop_columns',
		array(
			'default'           => 3,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_shop_columns',
		array(
			'label'   => __( 'Shop Grid Columns (Desktop)', 'statement-collector-theme' ),
			'section' => 'statement_shop_section',
			'type'    => 'select',
			'choices' => array(
				2 => __( '2 Columns (Editorial Large)', 'statement-collector-theme' ),
				3 => __( '3 Columns (Balanced Default)', 'statement-collector-theme' ),
				4 => __( '4 Columns (Compact Grid)', 'statement-collector-theme' ),
			),
		)
	);

	// Show Breadcrumbs
	$wp_customize->add_setting(
		'statement_show_breadcrumbs',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_show_breadcrumbs',
		array(
			'label'   => __( 'Display Breadcrumb Trail', 'statement-collector-theme' ),
			'section' => 'statement_shop_section',
			'type'    => 'checkbox',
		)
	);

	// ==========================================
	// SECTION 4: HOMEPAGE SETTINGS & BUILDER MODE
	// ==========================================
	$wp_customize->add_section(
		'statement_home_section',
		array(
			'title'       => __( 'Homepage & Layout Mode', 'statement-collector-theme' ),
			'description' => __( 'Configure front page layout mode and homepage modules.', 'statement-collector-theme' ),
			'panel'       => 'statement_theme_panel',
			'priority'    => 35,
		)
	);

	// Front Page Renderer Mode
	$wp_customize->add_setting(
		'statement_front_page_renderer',
		array(
			'default'           => 'statement',
			'sanitize_callback' => static function ( $val ) {
				return in_array( $val, array( 'statement', 'content' ), true ) ? $val : 'statement';
			},
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_front_page_renderer',
		array(
			'label'       => __( 'Front Page Renderer Mode', 'statement-collector-theme' ),
			'description' => __( 'Choose "Statement Editorial Homepage" for the curated release layout, or "Page Content / Page Builder" to design the front page with Elementor / Gutenberg.', 'statement-collector-theme' ),
			'section'     => 'statement_home_section',
			'type'        => 'select',
			'choices'     => array(
				'statement' => __( 'Statement Editorial Homepage (Default)', 'statement-collector-theme' ),
				'content'   => __( 'Page Content / Page Builder (Elementor / Gutenberg)', 'statement-collector-theme' ),
			),
		)
	);

	// Enable Hero Slider
	$wp_customize->add_setting(
		'statement_enable_hero_slider',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_enable_hero_slider',
		array(
			'label'   => __( 'Enable Homepage Hero Slider', 'statement-collector-theme' ),
			'section' => 'statement_home_section',
			'type'    => 'checkbox',
		)
	);

	// Enable Email Capture Section
	$wp_customize->add_setting(
		'statement_enable_email_capture',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'statement_enable_email_capture',
		array(
			'label'   => __( 'Enable Homepage Email Capture Form', 'statement-collector-theme' ),
			'section' => 'statement_home_section',
			'type'    => 'checkbox',
		)
	);

	// ==========================================
	// SECTION 5: HOMEPAGE HERO SLIDER
	// ==========================================
	$wp_customize->add_section(
		'statement_hero_slider',
		array(
			'title'       => __( 'Homepage Hero Slider', 'statement-collector-theme' ),
			'description' => __( 'Configure up to 4 campaign slides for the homepage hero carousel.', 'statement-collector-theme' ),
			'panel'       => 'statement_theme_panel',
			'priority'    => 40,
		)
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		// Slide Image
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_image",
			array(
				'default'           => '',
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Media_Control(
				$wp_customize,
				"statement_hero_slide_{$i}_image",
				array(
					'label'       => sprintf( __( 'Slide %d: Image (Desktop)', 'statement-collector-theme' ), $i ),
					'section'     => 'statement_hero_slider',
					'mime_type'   => 'image',
				)
			)
		);

		// Slide Mobile Image
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_mobile_image",
			array(
				'default'           => '',
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Media_Control(
				$wp_customize,
				"statement_hero_slide_{$i}_mobile_image",
				array(
					'label'       => sprintf( __( 'Slide %d: Image (Mobile Optional)', 'statement-collector-theme' ), $i ),
					'section'     => 'statement_hero_slider',
					'mime_type'   => 'image',
				)
			)
		);

		// Slide Eyebrow
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_eyebrow",
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"statement_hero_slide_{$i}_eyebrow",
			array(
				'label'   => sprintf( __( 'Slide %d: Eyebrow', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'text',
			)
		);

		// Slide Heading
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_heading",
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"statement_hero_slide_{$i}_heading",
			array(
				'label'   => sprintf( __( 'Slide %d: Heading', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'text',
			)
		);

		// Slide Link
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_link",
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"statement_hero_slide_{$i}_link",
			array(
				'label'   => sprintf( __( 'Slide %d: Target Link', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'url',
			)
		);

		// Slide CTA Text
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_cta",
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"statement_hero_slide_{$i}_cta",
			array(
				'label'   => sprintf( __( 'Slide %d: CTA Button Text', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'text',
			)
		);

		// Slide Focal Point
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_focal",
			array(
				'default'           => 'center center',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"statement_hero_slide_{$i}_focal",
			array(
				'label'       => sprintf( __( 'Slide %d: Focal Position', 'statement-collector-theme' ), $i ),
				'description' => __( 'CSS object-position (e.g. "center 25%")', 'statement-collector-theme' ),
				'section'     => 'statement_hero_slider',
				'type'        => 'text',
			)
		);
	}
}
add_action( 'customize_register', __NAMESPACE__ . '\customize_register' );
