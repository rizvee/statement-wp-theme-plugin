<?php
/**
 * Statement Collector Theme - Thin-Line SVG Icon Subsystem.
 *
 * Provides lightweight, normalized, accessible thin-line SVG iconography
 * across the Statement digital storefront with zero external dependencies.
 *
 * @package Statement\Collector\Theme
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the SVG markup for a given icon name.
 *
 * @param string               $name Icon name.
 * @param array<string, mixed> $args Optional arguments (class, width, height, aria-hidden, title).
 * @return string SVG icon markup or empty string.
 */
function get_statement_icon( string $name, array $args = [] ): string {
	$class       = ! empty( $args['class'] ) ? esc_attr( (string) $args['class'] ) : 'statement-icon';
	$aria_hidden = isset( $args['aria-hidden'] ) ? ( $args['aria-hidden'] ? 'true' : 'false' ) : 'true';
	$title       = ! empty( $args['title'] ) ? esc_html( (string) $args['title'] ) : '';

	$icons = array(
		'search'         => '<svg class="' . $class . ' statement-icon--search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><circle cx="11" cy="11" r="7.5"></circle><line x1="16.5" y1="16.5" x2="21.5" y2="21.5"></line></svg>',
		'account'        => '<svg class="' . $class . ' statement-icon--account" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-3.5 3.58-6 8-6s8 2.5 8 6"></path></svg>',
		'user'           => '<svg class="' . $class . ' statement-icon--account" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-3.5 3.58-6 8-6s8 2.5 8 6"></path></svg>',
		'bag'            => '<svg class="' . $class . ' statement-icon--bag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><path d="M5 8h14l1 13H4L5 8z"></path><path d="M8 8V6a4 4 0 0 1 8 0v2"></path></svg>',
		'menu'           => '<svg class="' . $class . ' statement-icon--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line></svg>',
		'close'          => '<svg class="' . $class . ' statement-icon--close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
		'arrow-right'    => '<svg class="' . $class . ' statement-icon--arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="4" y1="12" x2="20" y2="12"></line><polyline points="14 6 20 12 14 18"></polyline></svg>',
		'arrow-left'     => '<svg class="' . $class . ' statement-icon--arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="20" y1="12" x2="4" y2="12"></line><polyline points="10 18 4 12 10 6"></polyline></svg>',
		'arrow-up-right' => '<svg class="' . $class . ' statement-icon--arrow-up-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>',
		'chevron-right'  => '<svg class="' . $class . ' statement-icon--chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>',
		'plus'           => '<svg class="' . $class . ' statement-icon--plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
		'minus'          => '<svg class="' . $class . ' statement-icon--minus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
		'size-guide'     => '<svg class="' . $class . ' statement-icon--size-guide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><rect x="2" y="7" width="20" height="10" rx="1"></rect><line x1="6" y1="7" x2="6" y2="11"></line><line x1="10" y1="7" x2="10" y2="13"></line><line x1="14" y1="7" x2="14" y2="11"></line><line x1="18" y1="7" x2="18" y2="13"></line></svg>',
		'ruler'          => '<svg class="' . $class . ' statement-icon--size-guide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><rect x="2" y="7" width="20" height="10" rx="1"></rect><line x1="6" y1="7" x2="6" y2="11"></line><line x1="10" y1="7" x2="10" y2="13"></line><line x1="14" y1="7" x2="14" y2="11"></line><line x1="18" y1="7" x2="18" y2="13"></line></svg>',
		'instagram'      => '<svg class="' . $class . ' statement-icon--instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
		'facebook'       => '<svg class="' . $class . ' statement-icon--facebook" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
		'email'          => '<svg class="' . $class . ' statement-icon--email" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
		'check'          => '<svg class="' . $class . ' statement-icon--check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><polyline points="20 6 9 17 4 12"></polyline></svg>',
		'eye'            => '<svg class="' . $class . ' statement-icon--eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="' . $aria_hidden . '" focusable="false"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
	);

	$svg = $icons[ $name ] ?? '';

	if ( '' !== $svg && '' !== $title ) {
		$svg = str_replace( '><circle', '><title>' . $title . '</title><circle', $svg );
		$svg = str_replace( '><rect', '><title>' . $title . '</title><rect', $svg );
		$svg = str_replace( '><path', '><title>' . $title . '</title><path', $svg );
		$svg = str_replace( '><line', '><title>' . $title . '</title><line', $svg );
	}

	return $svg;
}

/**
 * Render the SVG markup for a given icon name directly.
 *
 * @param string               $name Icon name.
 * @param array<string, mixed> $args Optional arguments.
 */
function render_statement_icon( string $name, array $args = [] ): void {
	echo get_statement_icon( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
