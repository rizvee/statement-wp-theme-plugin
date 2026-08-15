<?php

namespace Statement\Integration\Fixtures;

defined( 'ABSPATH' ) || exit;

use WC_Payment_Gateway;

/**
 * Statement QA Test Payment Gateway.
 *
 * Dedicated test-only offline gateway for M13 integration testing.
 * Strictly bounded:
 * - Available ONLY when cart contains exclusively the exact test product SKU TEST-PD01-PAJ and private fixture exists.
 * - Zero external APIs, zero real-money charges, zero credentials.
 * - Places order in deterministic 'processing' state.
 * - Exactly-once stock reduction via WooCommerce standard payment_complete() lifecycle.
 * - Revalidates order scope inside process_payment().
 */
class StatementQaGateway extends WC_Payment_Gateway {
	public const GATEWAY_ID = 'statement_qa_gateway';
	public const TARGET_SKU = 'TEST-PD01-PAJ';
	public const VERSION    = '0.3.1';

	public function __construct() {
		$this->id                 = self::GATEWAY_ID;
		$this->method_title       = 'Statement QA Test Gateway (TEST ONLY)';
		$this->method_description = 'Temporary fixture payment gateway for controlled M13 test orders. No real money charge.';
		$this->title              = 'TEST ONLY — NO PAYMENT (Statement QA)';
		$this->description        = 'Zero-cost offline test gateway for M13 controlled order verification.';
		$this->has_fields         = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled = 'yes';
	}

	/**
	 * Gate availability: strictly restricted to test environment with exact test SKU in cart.
	 */
	public function is_available(): bool {
		if ( ! parent::is_available() ) {
			return false;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		$cart = WC()->cart;
		if ( $cart->is_empty() ) {
			return false;
		}

		// Ensure the private fixture product entity exists
		$test_product = PrivateFixtureService::find_existing_product();
		if ( ! is_object( $test_product ) || ! method_exists( $test_product, 'get_id' ) ) {
			return false;
		}
		$target_product_id = (int) $test_product->get_id();

		$has_target_product = false;
		$has_non_target     = false;

		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'] ?? null;
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_sku' ) ) {
				$has_non_target = true;
				continue;
			}

			$sku        = (string) $product->get_sku();
			$product_id = (int) ( method_exists( $product, 'get_id' ) ? $product->get_id() : 0 );

			if ( self::TARGET_SKU === $sku && $product_id === $target_product_id ) {
				$has_target_product = true;
			} else {
				$has_non_target = true;
			}
		}

		// Available ONLY if exact test SKU & product ID is present AND no other products are in cart
		return $has_target_product && ! $has_non_target;
	}

	/**
	 * Process payment for controlled QA order.
	 *
	 * @param int $order_id Order ID.
	 * @return array{result: string, redirect: string}
	 */
	public function process_payment( $order_id ): array {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return array(
				'result'   => 'failure',
				'redirect' => '',
			);
		}

		$order = wc_get_order( $order_id );
		if ( ! is_object( $order ) ) {
			return array(
				'result'   => 'failure',
				'redirect' => '',
			);
		}

		// Order Scope Revalidation: Validate line items before marking payment complete
		$items = $order->get_items();
		if ( empty( $items ) ) {
			return array(
				'result'   => 'failure',
				'redirect' => '',
			);
		}

		$test_product = PrivateFixtureService::find_existing_product();
		$target_product_id = ( is_object( $test_product ) && method_exists( $test_product, 'get_id' ) )
			? (int) $test_product->get_id()
			: 0;

		$has_target = false;
		$has_invalid = false;

		foreach ( $items as $item ) {
			$product = method_exists( $item, 'get_product' ) ? $item->get_product() : null;
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_sku' ) ) {
				$has_invalid = true;
				break;
			}

			$sku = (string) $product->get_sku();
			$pid = (int) ( method_exists( $product, 'get_id' ) ? $product->get_id() : 0 );

			if ( self::TARGET_SKU === $sku && ( 0 === $target_product_id || $pid === $target_product_id ) ) {
				$has_target = true;
			} else {
				$has_invalid = true;
				break;
			}
		}

		if ( ! $has_target || $has_invalid ) {
			return array(
				'result'   => 'failure',
				'redirect' => '',
			);
		}

		// Mark order as test/QA order in meta
		$order->update_meta_data( '_statement_is_qa_order', 'yes' );
		$order->update_meta_data( '_statement_qa_gateway_version', self::VERSION );

		// Move order to processing without external charge
		// Note: payment_complete() inherently invokes stock reduction via WooCommerce standard hooks.
		// We do NOT call wc_reduce_stock_levels() explicitly to prevent double stock deduction.
		$order->payment_complete();

		// Record order ID in option for deterministic admin QA verification
		if ( function_exists( 'update_option' ) ) {
			update_option( QaTestService::QA_ORDER_OPTION, $order_id );
		}

		// Clear cart
		if ( function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->empty_cart();
		}

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}
