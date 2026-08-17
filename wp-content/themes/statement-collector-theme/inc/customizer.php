<?php

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Register Statement Theme Customizer settings and controls.
 *
 * @param \WP_Customize_Manager $wp_customize Customizer manager.
 */
function customize_register( \WP_Customize_Manager $wp_customize ): void {
	// Section: Hero Slider
	$wp_customize->add_section(
		'statement_hero_slider',
		array(
			'title'       => __( 'Homepage Hero Slider', 'statement-collector-theme' ),
			'description' => __( 'Configure up to 4 campaign slides for the homepage hero carousel.', 'statement-collector-theme' ),
			'priority'    => 30,
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
				'label'   => sprintf( __( 'Slide %d: Link URL', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'url',
			)
		);

		// Slide CTA
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
				'label'   => sprintf( __( 'Slide %d: CTA Label', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'text',
			)
		);

		// Slide Focal Point
		$wp_customize->add_setting(
			"statement_hero_slide_{$i}_focal",
			array(
				'default'           => 'center 25%',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"statement_hero_slide_{$i}_focal",
			array(
				'label'   => sprintf( __( 'Slide %d: Image Focal Position', 'statement-collector-theme' ), $i ),
				'section' => 'statement_hero_slider',
				'type'    => 'select',
				'choices' => array(
					'center top'    => __( 'Top (center top)', 'statement-collector-theme' ),
					'center 25%'    => __( 'Upper Chest (center 25%)', 'statement-collector-theme' ),
					'center center' => __( 'Center (center center)', 'statement-collector-theme' ),
					'center 75%'    => __( 'Lower (center 75%)', 'statement-collector-theme' ),
					'center bottom' => __( 'Bottom (center bottom)', 'statement-collector-theme' ),
				),
			)
		);
	}
}

add_action( 'customize_register', __NAMESPACE__ . '\\customize_register' );
