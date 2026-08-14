<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Provides future-facing commercial completion evaluation for WooCommerce orders.
 *
 * NOTE: is_commercially_completed() checks whether an order has reached a commercially
 * secured status (processing or completed). It does NOT imply legal ownership,
 * authenticity credentials, transferable collector ownership, or eligibility.
 * Partial order line refunds do not mutate order-level status evaluation.
 */
final class Completion {
	private const COMPLETED_STATUSES = array( 'processing', 'completed' );

	/**
	 * Evaluates whether an order is in a commercially completed/secured status.
	 *
	 * @param object $order WooCommerce order object.
	 */
	public static function is_commercially_completed( $order ): bool {
		if ( ! is_object( $order ) || ! method_exists( $order, 'get_status' ) ) {
			return false;
		}

		$status = strtolower( trim( (string) $order->get_status() ) );

		return in_array( $status, self::COMPLETED_STATUSES, true );
	}
}
