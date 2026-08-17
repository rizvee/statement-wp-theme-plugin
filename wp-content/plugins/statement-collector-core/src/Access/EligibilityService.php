<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Centralized commerce authorization service used across product visibility, direct access,
 * Add to Bag, Cart Integrity, checkout final validation, and order access audit.
 */
final class EligibilityService {
	/**
	 * Evaluates commerce eligibility for a given state and session context.
	 */
	public static function is_state_commerce_eligible( string $release_state, ?array $session_context ): bool {
		if ( ReleaseState::LIVE === $release_state ) {
			return true;
		}

		if ( ReleaseState::PRIVATE_ACCESS === $release_state ) {
			return is_array( $session_context ) && ! empty( $session_context['valid'] );
		}

		return false;
	}

	/**
	 * Evaluates whether a product (or product ID / variation) is eligible for commerce action.
	 */
	public static function is_commerce_eligible( $product_id_or_object, ?array $session_context = null, int $now_ts = 0 ): bool {
		$owner = Metadata::get_release_owner( $product_id_or_object );
		if ( ! $owner ) {
			return false;
		}

		$state = Metadata::get_release_state( $owner );

		if ( ReleaseState::LIVE === $state ) {
			return true;
		}

		if ( ReleaseState::SOLD_OUT === $state || ReleaseState::ARCHIVED === $state || ReleaseState::UPCOMING === $state ) {
			return false;
		}

		if ( ReleaseState::PRIVATE_ACCESS === $state ) {
			if ( null !== $session_context ) {
				return self::is_state_commerce_eligible( $state, $session_context );
			}

			// Resolve drop assigned to product
			$product_id = is_numeric( $product_id_or_object ) ? (int) $product_id_or_object : ( method_exists( $owner, 'get_id' ) ? (int) $owner->get_id() : 0 );
			if ( $product_id <= 0 || ! function_exists( 'wp_get_object_terms' ) ) {
				return false;
			}
			$terms = wp_get_object_terms( $product_id, 'statement_drop' );
			if ( empty( $terms ) || ( function_exists( 'is_wp_error' ) && is_wp_error( $terms ) ) ) {
				return false;
			}

			$drop_id = (int) $terms[0]->term_id;
			$cookie_name = SessionService::get_cookie_name( $drop_id );
			$token = $_COOKIE[ $cookie_name ] ?? '';

			if ( '' === $token ) {
				return false;
			}

			global $wpdb;
			if ( $now_ts <= 0 ) {
				$now_ts = time();
			}

			$config = DropConfig::get_config( $drop_id );
			$drop_close_ts = $config['closes_at_ts'] ?? 0;

			$validation = SessionService::validate_session( $wpdb, $drop_id, $token, $now_ts, $drop_close_ts );
			if ( null === $validation ) {
				return false;
			}

			return true;
		}

		return false;
	}
}
