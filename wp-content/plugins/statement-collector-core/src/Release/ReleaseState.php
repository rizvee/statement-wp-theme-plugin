<?php

declare(strict_types=1);

namespace Statement\Collector\Core\Release;

/**
 * Canonical Statement product release lifecycle.
 */
final class ReleaseState {
	public const UPCOMING       = 'UPCOMING';
	public const PRIVATE_ACCESS = 'PRIVATE_ACCESS';
	public const LIVE           = 'LIVE';
	public const SOLD_OUT       = 'SOLD_OUT';
	public const ARCHIVED       = 'ARCHIVED';

	private const ORDER = array(
		self::UPCOMING,
		self::PRIVATE_ACCESS,
		self::LIVE,
		self::SOLD_OUT,
		self::ARCHIVED,
	);

	/**
	 * Return the complete ordered lifecycle.
	 *
	 * @return string[]
	 */
	public static function all(): array {
		return self::ORDER;
	}

	/**
	 * Whether a value is one of the canonical states.
	 */
	public static function is_valid( string $state ): bool {
		return in_array( $state, self::ORDER, true );
	}

	/**
	 * Resolve missing or invalid persisted data to the safe initial state.
	 */
	public static function normalize( ?string $state ): string {
		return null !== $state && self::is_valid( $state ) ? $state : self::UPCOMING;
	}

	/**
	 * Allow only same-state or forward lifecycle transitions.
	 */
	public static function can_transition( string $current, string $requested ): bool {
		if ( ! self::is_valid( $requested ) ) {
			return false;
		}

		$current_index   = array_search( self::normalize( $current ), self::ORDER, true );
		$requested_index = array_search( $requested, self::ORDER, true );

		return $requested_index >= $current_index;
	}

	/**
	 * Terminal states permanently lock normal commerce purchasability.
	 */
	public static function is_terminal( string $state ): bool {
		return in_array( $state, array( self::SOLD_OUT, self::ARCHIVED ), true );
	}
}
