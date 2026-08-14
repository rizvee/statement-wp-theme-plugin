<?php

namespace Statement\Collector\Core\Product;

use Statement\Collector\Core\Drop\Taxonomy;
use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Controlled Statement fields on the native WooCommerce product editor.
 */
final class Admin {
	private const DROP_FIELD   = 'statement_collector_drop';
	private const NONCE_ACTION = 'statement_collector_save_product_data';
	private const NONCE_NAME   = '_statement_collector_product_nonce';

	/** @var bool */
	private static $booted = false;

	/**
	 * Register product-admin hooks once.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'woocommerce_product_options_general_product_data', array( self::class, 'render_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( self::class, 'save_fields' ) );
	}

	/**
	 * Render a single controlled Statement field group.
	 */
	public static function render_fields(): void {
		global $post, $product_object;

		$product = $product_object;
		if ( ! is_object( $product ) && isset( $post->ID ) && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post->ID );
		}
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return;
		}

		$drop_options = array( '' => __( '— No Drop assigned —', 'statement-collector-core' ) );
		$terms        = get_terms(
			array(
				'taxonomy'   => Taxonomy::KEY,
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$drop_options[ (string) $term->term_id ] = $term->name;
			}
		}

		$assigned = wp_get_object_terms( $product->get_id(), Taxonomy::KEY, array( 'fields' => 'ids' ) );
		$drop_id  = ! is_wp_error( $assigned ) && ! empty( $assigned ) ? (string) reset( $assigned ) : '';
		$states   = array_combine( ReleaseState::all(), ReleaseState::all() );

		echo '<div class="options_group statement-collector-product-data">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		woocommerce_wp_select(
			array(
				'id'          => self::DROP_FIELD,
				'label'       => __( 'Drop', 'statement-collector-core' ),
				'description' => __( 'Assign one historical Statement Drop.', 'statement-collector-core' ),
				'desc_tip'    => true,
				'options'     => $drop_options,
				'value'       => $drop_id,
			)
		);
		woocommerce_wp_select(
			array(
				'id'          => Metadata::RELEASE_STATE_KEY,
				'label'       => __( 'Release State', 'statement-collector-core' ),
				'description' => __( 'Release states move forward only.', 'statement-collector-core' ),
				'desc_tip'    => true,
				'options'     => $states,
				'value'       => Metadata::get_release_state( $product ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => Metadata::EDITION_LABEL_KEY,
				'label'             => __( 'Edition Label', 'statement-collector-core' ),
				'description'       => __( 'Optional concise label, for example EDITION 001.', 'statement-collector-core' ),
				'desc_tip'          => true,
				'value'             => Metadata::get_edition_label( $product ),
				'custom_attributes' => array( 'maxlength' => Metadata::EDITION_LABEL_MAX_LENGTH ),
			)
		);
		echo '</div>';
	}

	/**
	 * Validate and apply Statement fields through WooCommerce product CRUD.
	 *
	 * @param object $product WooCommerce product object.
	 */
	public static function save_fields( $product ): void {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return;
		}

		$product_id = (int) $product->get_id();
		if (
			$product_id < 1
			|| wp_is_post_autosave( $product_id )
			|| wp_is_post_revision( $product_id )
			|| ! current_user_can( 'edit_post', $product_id )
		) {
			return;
		}

		$nonce = isset( $_POST[ self::NONCE_NAME ] ) && is_string( $_POST[ self::NONCE_NAME ] )
			? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) )
			: '';
		if ( '' === $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		self::save_drop( $product );

		if ( isset( $_POST[ Metadata::RELEASE_STATE_KEY ] ) && is_string( $_POST[ Metadata::RELEASE_STATE_KEY ] ) ) {
			$requested_state = sanitize_text_field( wp_unslash( $_POST[ Metadata::RELEASE_STATE_KEY ] ) );
			Metadata::set_release_state( $product, $requested_state );
		}

		if ( isset( $_POST[ Metadata::EDITION_LABEL_KEY ] ) && is_string( $_POST[ Metadata::EDITION_LABEL_KEY ] ) ) {
			Metadata::set_edition_label( $product, wp_unslash( $_POST[ Metadata::EDITION_LABEL_KEY ] ) );
		}
	}

	/**
	 * Assign zero or one verified Drop while preserving data on invalid input.
	 */
	private static function save_drop( $product ): void {
		if ( ! array_key_exists( self::DROP_FIELD, $_POST ) || ! is_string( $_POST[ self::DROP_FIELD ] ) ) {
			return;
		}

		$product_id      = (int) $product->get_id();
		$current_drop_id = self::get_current_drop_id( $product_id );
		$is_locked       = $current_drop_id > 0 && ReleaseState::UPCOMING !== Metadata::get_release_state( $product );
		$submitted       = trim( wp_unslash( $_POST[ self::DROP_FIELD ] ) );
		if ( '' === $submitted ) {
			if ( $is_locked ) {
				return;
			}

			wp_set_object_terms( $product_id, array(), Taxonomy::KEY, false );
			return;
		}

		$term_id = absint( $submitted );
		if ( $term_id < 1 ) {
			return;
		}

		$term = get_term( $term_id, Taxonomy::KEY );
		if ( ! $term || is_wp_error( $term ) || Taxonomy::KEY !== $term->taxonomy ) {
			return;
		}

		if ( $is_locked ) {
			return;
		}

		wp_set_object_terms( $product_id, array( $term_id ), Taxonomy::KEY, false );
	}

	/**
	 * Return the first valid existing Statement Drop relationship.
	 */
	private static function get_current_drop_id( int $product_id ): int {
		$assigned = wp_get_object_terms( $product_id, Taxonomy::KEY, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $assigned ) || empty( $assigned ) ) {
			return 0;
		}

		foreach ( $assigned as $assigned_id ) {
			$term_id = absint( $assigned_id );
			$term    = $term_id > 0 ? get_term( $term_id, Taxonomy::KEY ) : null;
			if ( $term && ! is_wp_error( $term ) && Taxonomy::KEY === $term->taxonomy ) {
				return $term_id;
			}
		}

		return 0;
	}
}
