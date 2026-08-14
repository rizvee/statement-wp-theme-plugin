<?php

namespace Statement\Collector\Core\Product;

use Statement\Collector\Core\Release\ReleaseState;

defined( 'ABSPATH' ) || exit;

/**
 * Central WooCommerce CRUD access for Statement product metadata.
 */
final class Metadata {
	public const RELEASE_STATE_KEY        = '_statement_release_state';
	public const EDITION_LABEL_KEY        = '_statement_edition_label';
	public const EDITION_LABEL_MAX_LENGTH = 80;

	/**
	 * Read and normalize the product release state.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function get_release_state( $product ): string {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_meta' ) ) {
			return ReleaseState::UPCOMING;
		}

		$persisted = $product->get_meta( self::RELEASE_STATE_KEY, true );

		return ReleaseState::normalize( is_string( $persisted ) ? $persisted : null );
	}

	/**
	 * Save a valid same-state or forward transition without persisting failures.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function set_release_state( $product, string $requested_state ): bool {
		if ( ! is_object( $product ) || ! method_exists( $product, 'update_meta_data' ) ) {
			return false;
		}

		$current_state = self::get_release_state( $product );
		if ( ! ReleaseState::can_transition( $current_state, $requested_state ) ) {
			return false;
		}

		$product->update_meta_data( self::RELEASE_STATE_KEY, $requested_state );

		return true;
	}

	/**
	 * Read the optional concise edition label.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function get_edition_label( $product ): string {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_meta' ) ) {
			return '';
		}

		$value = $product->get_meta( self::EDITION_LABEL_KEY, true );

		return self::sanitize_edition_label( is_string( $value ) ? $value : '' );
	}

	/**
	 * Save or clear the optional edition label through WooCommerce CRUD.
	 *
	 * @param object $product WooCommerce product-like object.
	 */
	public static function set_edition_label( $product, string $label ): bool {
		if ( ! is_object( $product ) || ! method_exists( $product, 'update_meta_data' ) ) {
			return false;
		}

		$label = self::sanitize_edition_label( $label );
		if ( '' === $label && method_exists( $product, 'delete_meta_data' ) ) {
			$product->delete_meta_data( self::EDITION_LABEL_KEY );
			return true;
		}

		$product->update_meta_data( self::EDITION_LABEL_KEY, $label );

		return true;
	}

	/**
	 * Sanitize and bound edition labels without implying a production total.
	 */
	public static function sanitize_edition_label( string $label ): string {
		$label = function_exists( 'sanitize_text_field' )
			? sanitize_text_field( $label )
			: trim( strip_tags( $label ) );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $label, 0, self::EDITION_LABEL_MAX_LENGTH );
		}

		return substr( $label, 0, self::EDITION_LABEL_MAX_LENGTH );
	}
}
