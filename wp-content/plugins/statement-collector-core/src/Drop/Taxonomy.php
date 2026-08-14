<?php

namespace Statement\Collector\Core\Drop;

defined( 'ABSPATH' ) || exit;

/**
 * Registers historical Drops for WooCommerce products.
 */
final class Taxonomy {
	public const KEY = 'statement_drop';

	/** @var bool */
	private static $booted = false;

	/**
	 * Register taxonomy hooks once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'init', array( self::class, 'register' ) );
	}

	/**
	 * Register the Drop taxonomy against WooCommerce products.
	 */
	public static function register(): void {
		$labels = array(
			'name'          => __( 'Drops', 'statement-collector-core' ),
			'singular_name' => __( 'Drop', 'statement-collector-core' ),
			'menu_name'     => __( 'Drops', 'statement-collector-core' ),
			'all_items'     => __( 'All Drops', 'statement-collector-core' ),
			'edit_item'     => __( 'Edit Drop', 'statement-collector-core' ),
			'view_item'     => __( 'View Drop', 'statement-collector-core' ),
			'add_new_item'  => __( 'Add New Drop', 'statement-collector-core' ),
			'new_item_name' => __( 'New Drop Name', 'statement-collector-core' ),
			'search_items'  => __( 'Search Drops', 'statement-collector-core' ),
		);

		register_taxonomy(
			self::KEY,
			array( 'product' ),
			array(
				'labels'            => $labels,
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_in_quick_edit' => false,
				'meta_box_cb'        => false,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'drop' ),
			)
		);
	}
}
