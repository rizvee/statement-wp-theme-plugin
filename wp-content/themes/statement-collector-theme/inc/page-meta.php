<?php
/**
 * Page-Level Design Meta Overrides.
 *
 * Provides lightweight, secure per-page layout, header, footer, and title controls.
 *
 * @package Statement_Collector_Theme
 */

namespace Statement\Collector\Theme;

defined( 'ABSPATH' ) || exit;

final class PageMeta {
	const NONCE_ACTION = 'statement_page_meta_nonce_action';
	const NONCE_NAME   = 'statement_page_meta_nonce';

	const META_LAYOUT = '_statement_page_layout';
	const META_HEADER = '_statement_page_header';
	const META_FOOTER = '_statement_page_footer';
	const META_TITLE  = '_statement_page_title';

	/**
	 * Register meta box hooks.
	 */
	public static function boot(): void {
		add_action( 'add_meta_boxes', array( self::class, 'register_meta_boxes' ) );
		add_action( 'save_post', array( self::class, 'save_meta_box_data' ), 10, 2 );
	}

	/**
	 * Register design options meta box for pages and posts.
	 */
	public static function register_meta_boxes(): void {
		$screens = array( 'page', 'post' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'statement_page_design_options',
				__( 'Statement Design Settings', 'statement-collector-theme' ),
				array( self::class, 'render_meta_box' ),
				$screen,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box UI.
	 *
	 * @param object $post Post object.
	 */
	public static function render_meta_box( $post ): void {
		if ( ! is_object( $post ) || ! isset( $post->ID ) ) {
			return;
		}

		$post_id = (int) $post->ID;
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$layout = (string) get_post_meta( $post_id, self::META_LAYOUT, true ) ?: 'default';
		$header = (string) get_post_meta( $post_id, self::META_HEADER, true ) ?: 'default';
		$footer = (string) get_post_meta( $post_id, self::META_FOOTER, true ) ?: 'default';
		$title  = (string) get_post_meta( $post_id, self::META_TITLE, true ) ?: 'default';
		?>
		<div class="statement-page-meta-box">
			<p>
				<label for="statement_page_layout"><strong><?php esc_html_e( 'Layout Width', 'statement-collector-theme' ); ?></strong></label><br>
				<select name="statement_page_layout" id="statement_page_layout" class="widefat">
					<option value="default" <?php selected( $layout, 'default' ); ?>><?php esc_html_e( 'Default (Global Container)', 'statement-collector-theme' ); ?></option>
					<option value="contained" <?php selected( $layout, 'contained' ); ?>><?php esc_html_e( 'Contained (1200px)', 'statement-collector-theme' ); ?></option>
					<option value="wide" <?php selected( $layout, 'wide' ); ?>><?php esc_html_e( 'Wide (1440px)', 'statement-collector-theme' ); ?></option>
					<option value="full" <?php selected( $layout, 'full' ); ?>><?php esc_html_e( 'Full Bleed (100%)', 'statement-collector-theme' ); ?></option>
				</select>
			</p>

			<p>
				<label for="statement_page_header"><strong><?php esc_html_e( 'Header Style', 'statement-collector-theme' ); ?></strong></label><br>
				<select name="statement_page_header" id="statement_page_header" class="widefat">
					<option value="default" <?php selected( $header, 'default' ); ?>><?php esc_html_e( 'Default', 'statement-collector-theme' ); ?></option>
					<option value="transparent" <?php selected( $header, 'transparent' ); ?>><?php esc_html_e( 'Transparent Overlay', 'statement-collector-theme' ); ?></option>
					<option value="hidden" <?php selected( $header, 'hidden' ); ?>><?php esc_html_e( 'Hidden', 'statement-collector-theme' ); ?></option>
				</select>
			</p>

			<p>
				<label for="statement_page_footer"><strong><?php esc_html_e( 'Footer', 'statement-collector-theme' ); ?></strong></label><br>
				<select name="statement_page_footer" id="statement_page_footer" class="widefat">
					<option value="default" <?php selected( $footer, 'default' ); ?>><?php esc_html_e( 'Default', 'statement-collector-theme' ); ?></option>
					<option value="hidden" <?php selected( $footer, 'hidden' ); ?>><?php esc_html_e( 'Hidden', 'statement-collector-theme' ); ?></option>
				</select>
			</p>

			<p>
				<label for="statement_page_title"><strong><?php esc_html_e( 'Page Title', 'statement-collector-theme' ); ?></strong></label><br>
				<select name="statement_page_title" id="statement_page_title" class="widefat">
					<option value="default" <?php selected( $title, 'default' ); ?>><?php esc_html_e( 'Default', 'statement-collector-theme' ); ?></option>
					<option value="hidden" <?php selected( $title, 'hidden' ); ?>><?php esc_html_e( 'Hidden', 'statement-collector-theme' ); ?></option>
				</select>
			</p>
		</div>
		<?php
	}

	/**
	 * Save meta box data securely.
	 *
	 * @param int $post_id Post ID.
	 * @param object $post Post object.
	 */
	public static function save_meta_box_data( int $post_id, $post ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$valid_layouts = array( 'default', 'contained', 'wide', 'full' );
		$valid_headers = array( 'default', 'transparent', 'hidden' );
		$valid_footers = array( 'default', 'hidden' );
		$valid_titles  = array( 'default', 'hidden' );

		if ( isset( $_POST['statement_page_layout'] ) ) {
			$layout = sanitize_key( wp_unslash( $_POST['statement_page_layout'] ) );
			if ( in_array( $layout, $valid_layouts, true ) ) {
				update_post_meta( $post_id, self::META_LAYOUT, $layout );
			}
		}

		if ( isset( $_POST['statement_page_header'] ) ) {
			$header = sanitize_key( wp_unslash( $_POST['statement_page_header'] ) );
			if ( in_array( $header, $valid_headers, true ) ) {
				update_post_meta( $post_id, self::META_HEADER, $header );
			}
		}

		if ( isset( $_POST['statement_page_footer'] ) ) {
			$footer = sanitize_key( wp_unslash( $_POST['statement_page_footer'] ) );
			if ( in_array( $footer, $valid_footers, true ) ) {
				update_post_meta( $post_id, self::META_FOOTER, $footer );
			}
		}

		if ( isset( $_POST['statement_page_title'] ) ) {
			$title = sanitize_key( wp_unslash( $_POST['statement_page_title'] ) );
			if ( in_array( $title, $valid_titles, true ) ) {
				update_post_meta( $post_id, self::META_TITLE, $title );
			}
		}
	}

	/**
	 * Get current queried page layout override.
	 *
	 * @return string
	 */
	public static function get_layout_override(): string {
		$post_id = function_exists( 'get_the_ID' ) ? (int) get_the_ID() : 0;
		if ( $post_id < 1 ) {
			return 'default';
		}

		return (string) get_post_meta( $post_id, self::META_LAYOUT, true ) ?: 'default';
	}

	/**
	 * Get current queried page header override.
	 *
	 * @return string
	 */
	public static function get_header_override(): string {
		$post_id = function_exists( 'get_the_ID' ) ? (int) get_the_ID() : 0;
		if ( $post_id < 1 ) {
			return 'default';
		}

		return (string) get_post_meta( $post_id, self::META_HEADER, true ) ?: 'default';
	}

	/**
	 * Whether footer should render on current queried page.
	 *
	 * @return bool
	 */
	public static function is_footer_visible(): bool {
		$post_id = function_exists( 'get_the_ID' ) ? (int) get_the_ID() : 0;
		if ( $post_id < 1 ) {
			return true;
		}

		$footer = (string) get_post_meta( $post_id, self::META_FOOTER, true );
		return 'hidden' !== $footer;
	}

	/**
	 * Whether page title should render on current queried page.
	 *
	 * @return bool
	 */
	public static function is_title_visible(): bool {
		$post_id = function_exists( 'get_the_ID' ) ? (int) get_the_ID() : 0;
		if ( $post_id < 1 ) {
			return true;
		}

		$title = (string) get_post_meta( $post_id, self::META_TITLE, true );
		return 'hidden' !== $title;
	}
}
