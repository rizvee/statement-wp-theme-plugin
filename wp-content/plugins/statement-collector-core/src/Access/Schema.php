<?php

namespace Statement\Collector\Core\Access;

defined( 'ABSPATH' ) || exit;

/**
 * Versioned schema and database migration manager for Private Access tables.
 */
final class Schema {
	public const DB_VERSION = '1.0.0';
	public const OPTION_KEY = 'statement_access_db_version';

	/**
	 * Returns operational table names given WordPress prefix.
	 *
	 * @return array<string, string>
	 */
	public static function get_table_names( string $prefix ): array {
		return array(
			'grants'      => $prefix . 'statement_access_grants',
			'sessions'    => $prefix . 'statement_access_sessions',
			'tokens'      => $prefix . 'statement_access_tokens',
			'rate_limits' => $prefix . 'statement_access_rate_limits',
			'consent'     => $prefix . 'statement_consent_events',
		);
	}

	/**
	 * Generates table creation SQL for dbDelta / installation.
	 *
	 * @return array<string, string>
	 */
	public static function get_schema_sql( string $prefix, string $charset_collate = '' ): array {
		$tables = self::get_table_names( $prefix );

		return array(
			'grants' => "CREATE TABLE {$tables['grants']} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				drop_term_id bigint(20) unsigned NOT NULL,
				email_hash varchar(64) NOT NULL,
				encrypted_email text NOT NULL,
				encryption_algo varchar(32) NOT NULL,
				key_version varchar(32) NOT NULL,
				granted_at datetime NOT NULL,
				individual_expires_at datetime NOT NULL,
				drop_close_at_issuance datetime NOT NULL,
				grant_expires_at datetime NOT NULL,
				source varchar(32) NOT NULL DEFAULT 'public',
				supersedes_grant_id bigint(20) unsigned DEFAULT NULL,
				revoked_at datetime DEFAULT NULL,
				revoked_reason varchar(64) DEFAULT NULL,
				access_email_sent_at datetime DEFAULT NULL,
				access_email_count int(10) unsigned NOT NULL DEFAULT 0,
				reminder_scheduled_at datetime DEFAULT NULL,
				reminder_sent_at datetime DEFAULT NULL,
				reminder_cancelled_at datetime DEFAULT NULL,
				reminder_cancel_reason varchar(64) DEFAULT NULL,
				anonymized_at datetime DEFAULT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY drop_email_idx (drop_term_id, email_hash),
				KEY email_hash_idx (email_hash),
				KEY grant_expires_idx (grant_expires_at)
			) {$charset_collate};",

			'sessions' => "CREATE TABLE {$tables['sessions']} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				grant_id bigint(20) unsigned NOT NULL,
				drop_term_id bigint(20) unsigned NOT NULL,
				token_hash varchar(64) NOT NULL,
				issued_at datetime NOT NULL,
				expires_at datetime NOT NULL,
				revoked_at datetime DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY token_hash_idx (token_hash),
				KEY grant_id_idx (grant_id),
				KEY drop_grant_idx (drop_term_id, grant_id)
			) {$charset_collate};",

			'tokens' => "CREATE TABLE {$tables['tokens']} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				grant_id bigint(20) unsigned DEFAULT NULL,
				subject_email_hash varchar(64) DEFAULT NULL,
				purpose varchar(32) NOT NULL,
				token_hash varchar(64) NOT NULL,
				issued_at datetime NOT NULL,
				expires_at datetime NOT NULL,
				consumed_at datetime DEFAULT NULL,
				revoked_at datetime DEFAULT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY token_hash_idx (token_hash),
				KEY grant_purpose_idx (grant_id, purpose),
				KEY purpose_hash_idx (purpose, subject_email_hash)
			) {$charset_collate};",

			'rate_limits' => "CREATE TABLE {$tables['rate_limits']} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				drop_term_id bigint(20) unsigned NOT NULL,
				scope_type varchar(16) NOT NULL,
				scope_hash varchar(64) NOT NULL,
				attempted_at datetime NOT NULL,
				expires_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY scope_lookup_idx (drop_term_id, scope_type, scope_hash, expires_at)
			) {$charset_collate};",

			'consent' => "CREATE TABLE {$tables['consent']} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email_hash varchar(64) NOT NULL,
				drop_term_id bigint(20) unsigned DEFAULT NULL,
				grant_id bigint(20) unsigned DEFAULT NULL,
				event_type varchar(32) NOT NULL,
				consent_version varchar(32) NOT NULL,
				exact_consent_text text NOT NULL,
				consent_text_hash varchar(64) NOT NULL,
				source varchar(32) NOT NULL,
				occurred_at datetime NOT NULL,
				schema_version varchar(16) NOT NULL DEFAULT '1.0',
				PRIMARY KEY (id),
				KEY email_hash_idx (email_hash)
			) {$charset_collate};",
		);
	}

	/**
	 * Run migrations/install.
	 */
	public static function install(): void {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();
		$sql_statements  = self::get_schema_sql( $wpdb->prefix, $charset_collate );

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $sql_statements as $sql ) {
			dbDelta( $sql );
		}

		update_option( self::OPTION_KEY, self::DB_VERSION );
	}

	/**
	 * Checks if schema is healthy and installed.
	 */
	public static function is_healthy(): bool {
		global $wpdb;
		if ( ! isset( $wpdb ) ) {
			return false;
		}

		$tables = self::get_table_names( $wpdb->prefix );
		foreach ( $tables as $table ) {
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $found !== $table ) {
				return false;
			}
		}

		return true;
	}
}
