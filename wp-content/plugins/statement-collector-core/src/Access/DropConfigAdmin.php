<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Admin UI and form handlers for managing Private Access Drop configuration on statement_drop terms.
 */
final class DropConfigAdmin {
	public const NONCE_ACTION = 'statement_drop_config_save';
	public const NONCE_NAME   = 'statement_drop_config_nonce';

	/**
	 * Bootstrap admin hooks.
	 */
	public static function boot(): void {
		if ( function_exists( 'is_admin' ) && ! is_admin() ) {
			return;
		}

		add_action( 'statement_drop_add_form_fields', array( self::class, 'render_add_fields' ) );
		add_action( 'statement_drop_edit_form_fields', array( self::class, 'render_edit_fields' ), 10, 1 );

		add_action( 'created_statement_drop', array( self::class, 'save_fields' ), 10, 1 );
		add_action( 'edited_statement_drop', array( self::class, 'save_fields' ), 10, 1 );
	}

	/**
	 * Renders fields on the Add New Drop taxonomy screen.
	 */
	public static function render_add_fields(): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<div class="form-field term-private-access-closes-at-wrap">
			<label for="statement_private_access_closes_at"><?php esc_html_e( 'Private Access Closes At', 'statement-collector-core' ); ?></label>
			<input type="datetime-local" name="statement_private_access_closes_at" id="statement_private_access_closes_at" value="">
			<p class="description"><?php esc_html_e( 'Site local time when Private Access ends for this Drop. Stored internally as UTC.', 'statement-collector-core' ); ?></p>
		</div>

		<div class="form-field term-private-access-duration-wrap">
			<label for="statement_private_access_duration"><?php esc_html_e( 'Individual Access Duration', 'statement-collector-core' ); ?></label>
			<input type="number" name="statement_private_access_duration" id="statement_private_access_duration" value="2" min="1" style="width: 100px;">
			<select name="statement_private_access_duration_unit" id="statement_private_access_duration_unit">
				<option value="hours" selected><?php esc_html_e( 'Hours', 'statement-collector-core' ); ?></option>
				<option value="minutes"><?php esc_html_e( 'Minutes', 'statement-collector-core' ); ?></option>
				<option value="days"><?php esc_html_e( 'Days', 'statement-collector-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Duration of access grant once a customer enters their email.', 'statement-collector-core' ); ?></p>
		</div>

		<div class="form-field term-private-access-send-email-wrap">
			<label for="statement_send_access_email">
				<input type="checkbox" name="statement_send_access_email" id="statement_send_access_email" value="yes" checked>
				<?php esc_html_e( 'Send Access Email', 'statement-collector-core' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Send email with access return link when grant is created.', 'statement-collector-core' ); ?></p>
		</div>

		<div class="form-field term-private-access-reminder-wrap">
			<label for="statement_reminder_enabled">
				<input type="checkbox" name="statement_reminder_enabled" id="statement_reminder_enabled" value="yes">
				<?php esc_html_e( 'Reminder Enabled', 'statement-collector-core' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Schedule an access reminder email before grant expires.', 'statement-collector-core' ); ?></p>
		</div>

		<div class="form-field term-private-access-reminder-delay-wrap">
			<label for="statement_reminder_delay"><?php esc_html_e( 'Reminder Delay / Lead Time', 'statement-collector-core' ); ?></label>
			<input type="number" name="statement_reminder_delay" id="statement_reminder_delay" value="1" min="1" style="width: 100px;">
			<select name="statement_reminder_delay_unit" id="statement_reminder_delay_unit">
				<option value="hours" selected><?php esc_html_e( 'Hours', 'statement-collector-core' ); ?></option>
				<option value="minutes"><?php esc_html_e( 'Minutes', 'statement-collector-core' ); ?></option>
				<option value="days"><?php esc_html_e( 'Days', 'statement-collector-core' ); ?></option>
			</select>
		</div>
		<?php
	}

	/**
	 * Renders fields on the Edit Drop taxonomy screen.
	 *
	 * @param \WP_Term|object $term Taxonomy term object.
	 */
	public static function render_edit_fields( $term ): void {
		$term_id = is_object( $term ) && isset( $term->term_id ) ? (int) $term->term_id : 0;
		if ( $term_id <= 0 ) {
			return;
		}

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$config = DropConfig::get_config( $term_id );

		// Format closes_at to site local time for input[type="datetime-local"]
		$local_closes_at = '';
		if ( ! empty( $config['closes_at_ts'] ) ) {
			$local_closes_at = function_exists( 'wp_date' )
				? wp_date( 'Y-m-d\TH:i', $config['closes_at_ts'] )
				: gmdate( 'Y-m-d\TH:i', $config['closes_at_ts'] );
		}

		$duration            = ( isset( $config['duration'] ) && $config['duration'] > 0 ) ? $config['duration'] : 2;
		$duration_unit       = ( isset( $config['duration_unit'] ) && in_array( $config['duration_unit'], DropConfig::ALLOWED_UNITS, true ) ) ? $config['duration_unit'] : 'hours';
		$send_email          = ! isset( $config['send_access_email'] ) || 'no' !== $config['send_access_email'];
		$reminder_enabled    = isset( $config['reminder_enabled'] ) && 'yes' === $config['reminder_enabled'];
		$reminder_delay      = ( isset( $config['reminder_delay'] ) && $config['reminder_delay'] > 0 ) ? $config['reminder_delay'] : 1;
		$reminder_delay_unit = ( isset( $config['reminder_delay_unit'] ) && in_array( $config['reminder_delay_unit'], DropConfig::ALLOWED_UNITS, true ) ) ? $config['reminder_delay_unit'] : 'hours';
		?>
		<tr class="form-field term-private-access-closes-at-wrap">
			<th scope="row"><label for="statement_private_access_closes_at"><?php esc_html_e( 'Private Access Closes At', 'statement-collector-core' ); ?></label></th>
			<td>
				<input type="datetime-local" name="statement_private_access_closes_at" id="statement_private_access_closes_at" value="<?php echo esc_attr( $local_closes_at ); ?>">
				<p class="description"><?php esc_html_e( 'Site local time when Private Access ends for this Drop. Stored internally as UTC.', 'statement-collector-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-private-access-duration-wrap">
			<th scope="row"><label for="statement_private_access_duration"><?php esc_html_e( 'Individual Access Duration', 'statement-collector-core' ); ?></label></th>
			<td>
				<input type="number" name="statement_private_access_duration" id="statement_private_access_duration" value="<?php echo esc_attr( (string) $duration ); ?>" min="1" style="width: 100px;">
				<select name="statement_private_access_duration_unit" id="statement_private_access_duration_unit">
					<option value="hours" <?php selected( $duration_unit, 'hours' ); ?>><?php esc_html_e( 'Hours', 'statement-collector-core' ); ?></option>
					<option value="minutes" <?php selected( $duration_unit, 'minutes' ); ?>><?php esc_html_e( 'Minutes', 'statement-collector-core' ); ?></option>
					<option value="days" <?php selected( $duration_unit, 'days' ); ?>><?php esc_html_e( 'Days', 'statement-collector-core' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'Duration of access grant once a customer enters their email.', 'statement-collector-core' ); ?></p>
			</td>
		</tr>

		<tr class="form-field term-private-access-send-email-wrap">
			<th scope="row"><?php esc_html_e( 'Send Access Email', 'statement-collector-core' ); ?></th>
			<td>
				<label for="statement_send_access_email">
					<input type="checkbox" name="statement_send_access_email" id="statement_send_access_email" value="yes" <?php checked( $send_email ); ?>>
					<?php esc_html_e( 'Send email with access return link when grant is created', 'statement-collector-core' ); ?>
				</label>
			</td>
		</tr>

		<tr class="form-field term-private-access-reminder-wrap">
			<th scope="row"><?php esc_html_e( 'Reminder Email', 'statement-collector-core' ); ?></th>
			<td>
				<label for="statement_reminder_enabled">
					<input type="checkbox" name="statement_reminder_enabled" id="statement_reminder_enabled" value="yes" <?php checked( $reminder_enabled ); ?>>
					<?php esc_html_e( 'Schedule an access reminder email before grant expires', 'statement-collector-core' ); ?>
				</label>
			</td>
		</tr>

		<tr class="form-field term-private-access-reminder-delay-wrap">
			<th scope="row"><label for="statement_reminder_delay"><?php esc_html_e( 'Reminder Delay / Lead Time', 'statement-collector-core' ); ?></label></th>
			<td>
				<input type="number" name="statement_reminder_delay" id="statement_reminder_delay" value="<?php echo esc_attr( (string) $reminder_delay ); ?>" min="1" style="width: 100px;">
				<select name="statement_reminder_delay_unit" id="statement_reminder_delay_unit">
					<option value="hours" <?php selected( $reminder_delay_unit, 'hours' ); ?>><?php esc_html_e( 'Hours', 'statement-collector-core' ); ?></option>
					<option value="minutes" <?php selected( $reminder_delay_unit, 'minutes' ); ?>><?php esc_html_e( 'Minutes', 'statement-collector-core' ); ?></option>
					<option value="days" <?php selected( $reminder_delay_unit, 'days' ); ?>><?php esc_html_e( 'Days', 'statement-collector-core' ); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Saves term meta fields from taxonomy form submission.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save_fields( int $term_id ): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		$raw_closes = isset( $_POST['statement_private_access_closes_at'] ) ? sanitize_text_field( wp_unslash( $_POST['statement_private_access_closes_at'] ) ) : '';
		if ( '' === $raw_closes ) {
			return;
		}

		// Convert site local datetime to UTC timestamp
		$site_timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( 'UTC' );
		$utc_closes_at = '';

		try {
			$dt = new \DateTimeImmutable( $raw_closes, $site_timezone );
			$utc_dt = $dt->setTimezone( new \DateTimeZone( 'UTC' ) );
			$utc_closes_at = $utc_dt->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) {
			return;
		}

		$duration            = isset( $_POST['statement_private_access_duration'] ) ? (int) $_POST['statement_private_access_duration'] : 2;
		$duration_unit       = isset( $_POST['statement_private_access_duration_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['statement_private_access_duration_unit'] ) ) : 'hours';
		$send_email          = isset( $_POST['statement_send_access_email'] ) ? 'yes' : 'no';
		$reminder_enabled    = isset( $_POST['statement_reminder_enabled'] ) ? 'yes' : 'no';
		$reminder_delay      = isset( $_POST['statement_reminder_delay'] ) ? (int) $_POST['statement_reminder_delay'] : 1;
		$reminder_delay_unit = isset( $_POST['statement_reminder_delay_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['statement_reminder_delay_unit'] ) ) : 'hours';

		DropConfig::save_config(
			$term_id,
			array(
				'closes_at'           => $utc_closes_at,
				'duration'            => $duration,
				'duration_unit'       => $duration_unit,
				'send_access_email'   => $send_email,
				'reminder_enabled'    => $reminder_enabled,
				'reminder_delay'      => $reminder_delay,
				'reminder_delay_unit' => $reminder_delay_unit,
			)
		);
	}
}
