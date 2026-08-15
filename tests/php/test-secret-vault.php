<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

// In-memory WordPress options and salt mock
global $mock_options, $mock_salts;
$mock_options = array();
$mock_salts   = array( 'auth' => 'test_wp_salt_auth_key_12345678901234567890' );

if ( ! function_exists( 'get_option' ) ) {
	function get_option( string $option, $default = false ) {
		global $mock_options;
		return isset( $mock_options[ $option ] ) ? $mock_options[ $option ]['value'] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( string $option, $value, $autoload = null ): bool {
		global $mock_options;
		$mock_options[ $option ] = array(
			'value'    => $value,
			'autoload' => 'no' === $autoload || false === $autoload ? 'no' : 'yes',
		);
		return true;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	function add_option( string $option, $value = '', $deprecated = '', $autoload = 'yes' ): bool {
		global $mock_options;
		$mock_options[ $option ] = array(
			'value'    => $value,
			'autoload' => 'no' === $autoload || false === $autoload ? 'no' : 'yes',
		);
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( string $option ): bool {
		global $mock_options;
		unset( $mock_options[ $option ] );
		return true;
	}
}

if ( ! function_exists( 'wp_salt' ) ) {
	function wp_salt( string $scheme = 'auth' ): string {
		global $mock_salts;
		return $mock_salts[ $scheme ] ?? 'default_test_salt_key_1234567890';
	}
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/SecretVault.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';

use Statement\Collector\Core\Access\SecretVault;
use Statement\Collector\Core\Access\Secrets;
use Statement\Collector\Core\Access\Crypto;

$vault_assertions = 0;

function vault_assert_same( $expected, $actual, string $message ): void {
	global $vault_assertions;
	++$vault_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// 1. Initial State without wp-config or vault => unavailable / fail closed
Secrets::reset_cache();
vault_assert_same( 'unavailable', Secrets::get_provider(), 'Provider must be unavailable when no wp-config or vault exists.' );
vault_assert_same( false, Secrets::is_configured(), 'is_configured must be false when provider is unavailable.' );
vault_assert_same( '', Secrets::get_identity_key(), 'get_identity_key must be empty when unavailable.' );

// 2. Secret Vault Creation & Round-trip Decryption
$custom_bundle = array(
	'identity_key'               => '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef',
	'rate_limit_key'            => '123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef0',
	'encryption_active_version' => 'v1',
	'encryption_keys'           => array(
		'v1' => '23456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef01',
	),
);

$create_ok = SecretVault::create_vault( $custom_bundle );
vault_assert_same( true, $create_ok, 'SecretVault::create_vault must return true.' );
vault_assert_same( true, SecretVault::is_initialized(), 'Vault must be initialized.' );

// Check database option structure & autoload = false
global $mock_options;
$vault_opt = $mock_options[ SecretVault::OPTION_NAME ];
vault_assert_same( 'no', $vault_opt['autoload'], 'Vault option MUST use autoload = false (no).' );

$raw_stored_payload = $vault_opt['value'];
vault_assert_same( 1, $raw_stored_payload['schema_version'], 'Stored payload must record schema version 1.' );
vault_assert_same( true, isset( $raw_stored_payload['ciphertext'] ), 'Stored payload must contain ciphertext.' );

// DB COMPROMISE TEST: Prove plaintext keys do NOT exist in raw stored payload
$json_stored = json_encode( $raw_stored_payload );
vault_assert_same( false, strpos( $json_stored, $custom_bundle['identity_key'] ), 'DB option MUST NOT contain plaintext identity key.' );
vault_assert_same( false, strpos( $json_stored, $custom_bundle['rate_limit_key'] ), 'DB option MUST NOT contain plaintext rate limit key.' );
vault_assert_same( false, strpos( $json_stored, $custom_bundle['encryption_keys']['v1'] ), 'DB option MUST NOT contain plaintext encryption key.' );

// 3. Provider via Encrypted Vault
Secrets::reset_cache();
vault_assert_same( 'encrypted_vault', Secrets::get_provider(), 'Provider must be encrypted_vault.' );
vault_assert_same( true, Secrets::is_configured(), 'is_configured must be true when vault is valid.' );
vault_assert_same( $custom_bundle['identity_key'], Secrets::get_identity_key(), 'get_identity_key must match vault bundle.' );
vault_assert_same( $custom_bundle['rate_limit_key'], Secrets::get_rate_limit_key(), 'get_rate_limit_key must match vault bundle.' );
vault_assert_same( 'v1', Secrets::get_active_key_version(), 'get_active_key_version must match vault bundle.' );
vault_assert_same( $custom_bundle['encryption_keys']['v1'], Secrets::get_encryption_key( 'v1' ), 'get_encryption_key must match vault bundle.' );

// 4. Crypto Semantics with Vault Provider
$email_hash = Crypto::hash_email( 'vault.user@statement.store' );
vault_assert_same( 64, strlen( (string) $email_hash ), 'Crypto::hash_email with vault provider must produce 64-char sha256 hex string.' );

$encrypted_email = Crypto::encrypt_email( 'vault.user@statement.store' );
vault_assert_same( true, is_array( $encrypted_email ), 'Crypto::encrypt_email with vault provider must encrypt successfully.' );

$decrypted_email = Crypto::decrypt_email( $encrypted_email );
vault_assert_same( 'vault.user@statement.store', $decrypted_email, 'Crypto::decrypt_email with vault provider must restore plaintext email.' );

// 5. Ciphertext Tampering & Wrong Salt Tests
$tampered_payload = $raw_stored_payload;
$tampered_payload['ciphertext'] = base64_encode( 'corrupted_ciphertext' );
$mock_options[ SecretVault::OPTION_NAME ]['value'] = $tampered_payload;
Secrets::reset_cache();
vault_assert_same( null, SecretVault::decrypt_bundle(), 'Tampered ciphertext must fail authentication and return null.' );
vault_assert_same( 'unavailable', Secrets::get_provider(), 'Provider must fail closed when ciphertext is tampered.' );

// Restore valid payload and test wrong salt
$mock_options[ SecretVault::OPTION_NAME ]['value'] = $raw_stored_payload;
$mock_salts['auth'] = 'DIFFERENT_WP_SALT_99999999999999999';
Secrets::reset_cache();
vault_assert_same( null, SecretVault::decrypt_bundle(), 'Wrong WP salt must fail decryption and return null.' );
vault_assert_same( 'unavailable', Secrets::get_provider(), 'Provider must fail closed when salt changes.' );

// Restore salt
$mock_salts['auth'] = 'test_wp_salt_auth_key_12345678901234567890';
Secrets::reset_cache();
vault_assert_same( 'encrypted_vault', Secrets::get_provider(), 'Provider must recover when correct salt is restored.' );

// 6. Delete Vault Test
SecretVault::delete_vault();
Secrets::reset_cache();
vault_assert_same( 'unavailable', Secrets::get_provider(), 'Provider must return unavailable after delete_vault.' );

echo "PASS: SecretVault & Provider Precedence test passed ({$vault_assertions} assertions).\n";
