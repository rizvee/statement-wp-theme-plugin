<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

use Statement\Collector\Core\Release\ReleaseState;
use Statement\Collector\Core\Product\Metadata;

/**
 * Performs precheck validations before transitioning a product to PRIVATE_ACCESS.
 */
final class Precheck {
	/**
	 * Validates whether a product and its Drop are ready to enter PRIVATE_ACCESS.
	 *
	 * @return array{ok: bool, errors: array<string>}
	 */
	public static function validate_private_access_readiness( int $product_id, int $now_ts ): array {
		$errors = array();

		// 1. Database schema health
		if ( ! Schema::is_healthy() ) {
			$errors[] = 'Database schema is unavailable or unhealthy.';
		}

		// 2. Encryption & secret keys check
		if ( ! Secrets::has_identity_key() ) {
			$errors[] = 'Email identity HMAC key (STATEMENT_ACCESS_IDENTITY_KEY) is missing.';
		}
		if ( ! Secrets::has_rate_limit_key() ) {
			$errors[] = 'IP rate-limit HMAC key (STATEMENT_ACCESS_RATE_LIMIT_KEY) is missing.';
		}
		if ( ! Secrets::has_encryption_config() ) {
			$errors[] = 'Encryption keyring or active key version is missing.';
		}

		// 3. Statement Drop assignment check
		$terms = wp_get_object_terms( $product_id, 'statement_drop' );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			$errors[] = 'Product must be assigned to a valid Statement Drop.';
			return array(
				'ok'     => false,
				'errors' => $errors,
			);
		}

		$drop_term = $terms[0];
		$drop_id   = (int) $drop_term->term_id;

		// 4. Drop configuration check
		$config = DropConfig::get_config( $drop_id );
		if ( null === $config || ! DropConfig::is_config_valid( $config, $now_ts ) ) {
			$errors[] = 'Drop private access configuration (closing time and individual access duration) is missing or invalid.';
		}

		// 5. Forbidden LIVE + PRIVATE_ACCESS state conflict inside same Drop
		$drop_products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'statement_drop',
						'field'    => 'term_id',
						'terms'    => $drop_id,
					),
				),
			)
		);

		foreach ( $drop_products as $other_id ) {
			if ( (int) $other_id === $product_id ) {
				continue;
			}

			$state = Metadata::get_release_state( (int) $other_id );
			if ( ReleaseState::LIVE === $state ) {
				$errors[] = 'Forbidden state: Drop already contains LIVE products. A Drop cannot mix LIVE and PRIVATE_ACCESS products.';
				break;
			}
		}

		return array(
			'ok'     => empty( $errors ),
			'errors' => $errors,
		);
	}
}
