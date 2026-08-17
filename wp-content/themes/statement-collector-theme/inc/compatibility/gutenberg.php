<?php
/**
 * Gutenberg & Block Editor Compatibility Adapter.
 *
 * Registers block categories, patterns, and editor styles.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme\Compatibility;

defined( 'ABSPATH' ) || exit;

final class Gutenberg {
	/**
	 * Boot Gutenberg compatibility.
	 */
	public static function boot(): void {
		add_action( 'init', array( self::class, 'register_block_categories' ) );
		add_action( 'init', array( self::class, 'register_block_patterns' ) );
	}

	/**
	 * Register custom block category.
	 */
	public static function register_block_categories(): void {
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				'statement',
				array( 'label' => __( 'Statement Editorial', 'statement-collector-theme' ) )
			);
		}
	}

	/**
	 * Register curated editorial block patterns.
	 */
	public static function register_block_patterns(): void {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		// Pattern 1: Editorial Hero
		register_block_pattern(
			'statement/editorial-hero',
			array(
				'title'       => __( 'Statement Editorial Hero', 'statement-collector-theme' ),
				'description' => __( 'Full-width editorial image with centered typography and brand line.', 'statement-collector-theme' ),
				'categories'  => array( 'statement' ),
				'content'     => '<!-- wp:group {"align":"full","className":"statement-editorial-hero-block"} --><div class="wp-block-group alignfull statement-editorial-hero-block"><p class="statement-eyebrow">DROP 001</p><h2 class="statement-editorial-title">MONOGRAM STUDY</h2><p class="statement-editorial-serif">"A study in repeating surface and structural wool."</p></div><!-- /wp:group -->',
			)
		);

		// Pattern 2: Two Image Editorial Grid
		register_block_pattern(
			'statement/two-image-editorial',
			array(
				'title'       => __( 'Two Image Editorial Grid', 'statement-collector-theme' ),
				'description' => __( 'Two asymmetric editorial garment photographs.', 'statement-collector-theme' ),
				'categories'  => array( 'statement' ),
				'content'     => '<!-- wp:columns {"className":"statement-editorial-grid-block"} --><div class="wp-block-columns statement-editorial-grid-block"><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"></figure><!-- /wp:image --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"></figure><!-- /wp:image --></div><!-- /wp:column --></div><!-- /wp:columns -->',
			)
		);

		// Pattern 3: Statement CTA
		register_block_pattern(
			'statement/brand-cta',
			array(
				'title'       => __( 'Statement Brand CTA', 'statement-collector-theme' ),
				'description' => __( 'Crafted. Not Mass Made. luxury banner.', 'statement-collector-theme' ),
				'categories'  => array( 'statement' ),
				'content'     => '<!-- wp:group {"align":"wide","className":"statement-cta-block"} --><div class="wp-block-group alignwide statement-cta-block"><h2 class="statement-cta-title">CRAFTED. NOT MASS MADE.</h2><p class="statement-cta-subtitle">Exclusive limited editions released in numbered sequences.</p></div><!-- /wp:group -->',
			)
		);
	}
}
