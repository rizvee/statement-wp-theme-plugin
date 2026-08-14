<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Order;

use Statement\Collector\Core\Product\Metadata;
use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Drop\Taxonomy;

defined( 'ABSPATH' ) || exit;

/**
 * Handles immutable purchase provenance capture and read access for Statement order line items.
 *
 * NOTE: The timestamp key _statement_purchased_at represents the timestamp at which
 * the Statement purchase-provenance snapshot was captured during order creation.
 * It is NOT proof of payment, commercial completion, or collector ownership.
 */
final class Provenance {
	public const META_VERSION        = '_statement_provenance_version';
	public const META_PRODUCT_ID     = '_statement_product_id_at_purchase';
	public const META_VARIATION_ID   = '_statement_variation_id_at_purchase';
	public const META_DROP_ID        = '_statement_drop_id_at_purchase';
	public const META_DROP_NAME      = '_statement_drop_name_at_purchase';
	public const META_EDITION_LABEL  = '_statement_edition_label_at_purchase';
	public const META_PRODUCT_TITLE  = '_statement_product_title_at_purchase';
	public const META_RELEASE_STATE  = '_statement_release_state_at_purchase';
	public const META_PURCHASED_AT   = '_statement_purchased_at';

	public const SCHEMA_VERSION = 1;

	public const STATUS_MISSING  = 'missing';
	public const STATUS_COMPLETE = 'complete';
	public const STATUS_INVALID  = 'invalid';

	/** @var bool */
	private static $booted = false;

	/**
	 * Boot order item provenance capture hooks.
	 */
	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'woocommerce_checkout_create_order_line_item', array( self::class, 'capture_line_item_provenance' ), 10, 4 );
	}

	/**
	 * Checks whether provenance metadata exists on an order item (prevents re-capture).
	 *
	 * @param object $item WooCommerce order line item.
	 */
	public static function is_captured( $item ): bool {
		if ( ! is_object( $item ) || ! method_exists( $item, 'get_meta' ) ) {
			return false;
		}

		$ver = $item->get_meta( self::META_VERSION, true );

		return '' !== (string) $ver;
	}

	/**
	 * Evaluates snapshot integrity status: 'missing', 'complete', or 'invalid'.
	 *
	 * @param object $item WooCommerce order line item.
	 */
	public static function get_snapshot_status( $item ): string {
		if ( ! self::is_captured( $item ) ) {
			return self::STATUS_MISSING;
		}

		$version       = (int) $item->get_meta( self::META_VERSION, true );
		$product_id    = (int) $item->get_meta( self::META_PRODUCT_ID, true );
		$product_title = trim( (string) $item->get_meta( self::META_PRODUCT_TITLE, true ) );
		$release_state = trim( (string) $item->get_meta( self::META_RELEASE_STATE, true ) );
		$purchased_at  = trim( (string) $item->get_meta( self::META_PURCHASED_AT, true ) );

		if ( 1 === $version && $product_id > 0 && '' !== $product_title && '' !== $release_state && '' !== $purchased_at ) {
			return self::STATUS_COMPLETE;
		}

		return self::STATUS_INVALID;
	}

	/**
	 * Checks whether a complete, valid provenance snapshot exists.
	 *
	 * @param object $item WooCommerce order line item.
	 */
	public static function is_valid( $item ): bool {
		return self::STATUS_COMPLETE === self::get_snapshot_status( $item );
	}

	/**
	 * Captures an immutable snapshot of Statement release metadata during order line item creation.
	 *
	 * @param object $item          WooCommerce order line item object.
	 * @param string $cart_item_key Cart item key.
	 * @param array  $values        Cart item values.
	 * @param object $order         WooCommerce order object.
	 */
	public static function capture_line_item_provenance( $item, string $cart_item_key, array $values, $order ): void {
		unset( $cart_item_key, $order );

		if ( ! is_object( $item ) || ! method_exists( $item, 'add_meta_data' ) || self::is_captured( $item ) ) {
			return;
		}

		$product = $values['data'] ?? null;
		if ( ! is_object( $product ) ) {
			return;
		}

		$owner = Metadata::get_release_owner( $product );
		if ( ! is_object( $owner ) ) {
			return;
		}

		$is_variation = method_exists( $product, 'get_type' ) && 'variation' === $product->get_type();
		$variation_id = $is_variation && method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;
		$product_id   = method_exists( $owner, 'get_id' ) ? (int) $owner->get_id() : 0;

		$title = method_exists( $owner, 'get_name' ) ? trim( (string) $owner->get_name() ) : '';
		$state = Metadata::get_release_state( $owner );
		$edition_label = Metadata::get_edition_label( $owner );

		$drop_id   = 0;
		$drop_name = '';

		if ( $product_id > 0 && function_exists( 'wp_get_object_terms' ) ) {
			$terms = wp_get_object_terms( $product_id, Taxonomy::KEY );
			if ( is_array( $terms ) && ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$first_term = reset( $terms );
				if ( is_object( $first_term ) && isset( $first_term->term_id, $first_term->name ) ) {
					$drop_id   = (int) $first_term->term_id;
					$drop_name = (string) $first_term->name;
				}
			}
		}

		$now_formatted = function_exists( 'current_time' ) ? current_time( 'mysql' ) : date( 'Y-m-d H:i:s' );

		$item->add_meta_data( self::META_VERSION, self::SCHEMA_VERSION, true );
		$item->add_meta_data( self::META_PRODUCT_ID, $product_id, true );
		$item->add_meta_data( self::META_VARIATION_ID, $variation_id, true );
		$item->add_meta_data( self::META_DROP_ID, $drop_id, true );
		$item->add_meta_data( self::META_DROP_NAME, $drop_name, true );
		$item->add_meta_data( self::META_EDITION_LABEL, $edition_label, true );
		$item->add_meta_data( self::META_PRODUCT_TITLE, $title, true );
		$item->add_meta_data( self::META_RELEASE_STATE, $state, true );
		$item->add_meta_data( self::META_PURCHASED_AT, $now_formatted, true );
	}

	/**
	 * Reads and normalizes captured provenance snapshot array from an order item.
	 *
	 * @param object $item WooCommerce order line item.
	 * @return array
	 */
	public static function get_provenance( $item ): array {
		if ( ! is_object( $item ) || ! method_exists( $item, 'get_meta' ) ) {
			return array();
		}

		$status = self::get_snapshot_status( $item );
		if ( self::STATUS_MISSING === $status ) {
			return array();
		}

		return array(
			'status'        => $status,
			'version'       => (int) $item->get_meta( self::META_VERSION, true ),
			'product_id'    => (int) $item->get_meta( self::META_PRODUCT_ID, true ),
			'variation_id'  => (int) $item->get_meta( self::META_VARIATION_ID, true ),
			'drop_id'       => (int) $item->get_meta( self::META_DROP_ID, true ),
			'drop_name'     => (string) $item->get_meta( self::META_DROP_NAME, true ),
			'edition_label' => (string) $item->get_meta( self::META_EDITION_LABEL, true ),
			'product_title' => (string) $item->get_meta( self::META_PRODUCT_TITLE, true ),
			'release_state' => (string) $item->get_meta( self::META_RELEASE_STATE, true ),
			'purchased_at'  => (string) $item->get_meta( self::META_PURCHASED_AT, true ),
		);
	}
}
