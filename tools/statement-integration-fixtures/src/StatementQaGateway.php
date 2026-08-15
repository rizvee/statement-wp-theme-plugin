<?php

namespace Statement\Integration\Fixtures;

defined( 'ABSPATH' ) || exit;

use WC_Payment_Gateway;

/**
 * Statement QA Test Payment Gateway.
 *
 * Dedicated test-only offline gateway for M13 integration testing.
 * Strictly bounded:
 * - Available ONLY when cart contains exact test product SKU TEST-PD01-PAJ.
 * - Zero external APIs, zero real-money charges, zero credentials.
 * - Places order in deterministic 'processing' state.
 */
class StatementQaGateway extends WC_Payment_Gateway {
	public const GATEWAY_ID = 'statement_qa_gateway';
	public const TARGET_SKU = 'TEST-PD01-PAJ';

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

		$has_target_sku = false;
		$has_non_target = false;

		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'] ?? null;
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_sku' ) ) {
				$has_non_target = true;
				continue;
			}

			if ( self::TARGET_SKU === $product->get_sku() ) {
				$has_target_sku = true;
			} else {
				$has_non_target = true;
			}
		}

		// Available ONLY if exact test SKU is present AND no other products are in cart
		return $has_target_sku && ! $has_non_target;
	}

	/**
	 * Process payment for controlled QA order.
	 *
	 * @param int $order_id Order ID.
	 * @return array{result: string, redirect: string}
	 */
	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );
		if ( ! is_object( $order ) ) {
			return array(
				'result'   => 'failure',
				'redirect' => '',
			);
		}

		// Mark order as test/QA order in meta
		$order->update_meta_data( '_statement_is_qa_order', 'yes' );
		$order->update_meta_data( '_statement_qa_gateway_version', '0.3.0' );

		// Move order to processing without external charge
		$order->payment_complete();

		// Reduce stock levels
		if ( function_exists( 'wc_reduce_stock_levels' ) ) {
			wc_reduce_stock_levels( $order_id );
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
