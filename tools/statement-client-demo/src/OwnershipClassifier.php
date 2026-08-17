<?php

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

/**
 * Deterministic classifier for product ownership boundaries.
 */
final class OwnershipClassifier {
	public const STATUS_QA_FIXTURE  = 'QA_FIXTURE';
	public const STATUS_CLIENT_DEMO = 'CLIENT_DEMO';
	public const STATUS_PRODUCTION  = 'PRODUCTION';
	public const STATUS_CONFLICT    = 'CONFLICT';
	public const STATUS_UNKNOWN     = 'UNKNOWN';

	/**
	 * Classify the ownership status of a product.
	 *
	 * @param mixed $product_or_id
	 * @return array<string, mixed>
	 */
	public static function classify( $product_or_id ): array {
		$post_id = 0;
		$product = null;

		if ( is_numeric( $product_or_id ) ) {
			$post_id = (int) $product_or_id;
			$product = function_exists( 'wc_get_product' ) ? wc_get_product( $post_id ) : null;
		} elseif ( is_object( $product_or_id ) ) {
			if ( method_exists( $product_or_id, 'get_id' ) ) {
				$post_id = (int) $product_or_id->get_id();
				$product = $product_or_id;
			} elseif ( isset( $product_or_id->ID ) ) {
				$post_id = (int) $product_or_id->ID;
				$product = function_exists( 'wc_get_product' ) ? wc_get_product( $post_id ) : null;
			}
		}

		if ( $post_id <= 0 ) {
			return array(
				'status'     => self::STATUS_UNKNOWN,
				'reason'     => 'Invalid or non-existent product ID',
				'product_id' => 0,
				'sku'        => '',
				'is_fixture' => false,
				'is_demo'    => false,
			);
		}

		$has_fixture_meta = function_exists( 'get_post_meta' ) && '1' === (string) get_post_meta( $post_id, '_statement_fixture', true );
		$has_demo_meta    = function_exists( 'get_post_meta' ) && '1' === (string) get_post_meta( $post_id, '_statement_client_demo', true );

		$sku = is_object( $product ) && method_exists( $product, 'get_sku' )
			? (string) $product->get_sku()
			: ( function_exists( 'get_post_meta' ) ? (string) get_post_meta( $post_id, '_sku', true ) : '' );

		$title = is_object( $product ) && method_exists( $product, 'get_name' )
			? (string) $product->get_name()
			: ( function_exists( 'get_the_title' ) ? (string) get_the_title( $post_id ) : '' );

		$is_test_sku   = '' !== $sku && 0 === stripos( $sku, 'TEST-' );
		$is_demo_sku   = '' !== $sku && 0 === stripos( $sku, 'STMT-CD-' );
		$is_test_title = '' !== $title && ( 0 === stripos( $title, 'TEST —' ) || 0 === stripos( $title, 'TEST -' ) || 0 === stripos( $title, 'TEST:' ) );

		$is_fixture_indicator = $has_fixture_meta || $is_test_sku || $is_test_title;
		$is_demo_indicator    = $has_demo_meta || $is_demo_sku;

		// Conflict condition: contradictory markers or mixed namespaces
		if ( ( $has_fixture_meta && $has_demo_meta ) || ( $is_fixture_indicator && $is_demo_indicator ) ) {
			return array(
				'status'     => self::STATUS_CONFLICT,
				'reason'     => "Product carries mixed fixture and client demo markers (Fixture meta: {$has_fixture_meta}, Demo meta: {$has_demo_meta}, SKU: {$sku})",
				'product_id' => $post_id,
				'sku'        => $sku,
				'is_fixture' => true,
				'is_demo'    => false,
			);
		}

		if ( $is_fixture_indicator ) {
			return array(
				'status'     => self::STATUS_QA_FIXTURE,
				'reason'     => 'Explicit QA fixture metadata or TEST namespace detected',
				'product_id' => $post_id,
				'sku'        => $sku,
				'is_fixture' => true,
				'is_demo'    => false,
			);
		}

		if ( $has_demo_meta && $is_demo_sku ) {
			return array(
				'status'     => self::STATUS_CLIENT_DEMO,
				'reason'     => 'Verified standalone Client Demo product',
				'product_id' => $post_id,
				'sku'        => $sku,
				'is_fixture' => false,
				'is_demo'    => true,
			);
		}

		return array(
			'status'     => self::STATUS_PRODUCTION,
			'reason'     => 'Organic product without fixture or demo markers',
			'product_id' => $post_id,
			'sku'        => $sku,
			'is_fixture' => false,
			'is_demo'    => false,
		);
	}
}
