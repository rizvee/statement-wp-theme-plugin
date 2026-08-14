<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Access/Schema.php';

use Statement\Collector\Core\Access\Secrets;
use Statement\Collector\Core\Access\Crypto;
use Statement\Collector\Core\Access\Schema;

$statement_assertions = 0;

function statement_assert_same( $expected, $actual, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

// 1. Secrets check without defined constants (should fail closed / return false or empty)
statement_assert_same( false, Secrets::has_identity_key(), 'Secrets::has_identity_key must return false when constant missing.' );
statement_assert_same( false, Secrets::has_rate_limit_key(), 'Secrets::has_rate_limit_key must return false when constant missing.' );
statement_assert_same( false, Secrets::has_encryption_config(), 'Secrets::has_encryption_config must return false when constants missing.' );

// Define test constants
define( 'STATEMENT_ACCESS_IDENTITY_KEY', 'test_identity_key_12345678901234567890' );
define( 'STATEMENT_ACCESS_RATE_LIMIT_KEY', 'test_rate_limit_key_12345678901234567890' );
define( 'STATEMENT_ACCESS_ENCRYPTION_ACTIVE_VERSION', 'v1' );
define(
	'STATEMENT_ACCESS_ENCRYPTION_KEYS',
	json_encode( array(
		'v1' => '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef',
		'v2' => 'abcdef0123456789abcdef0123456789abcdef0123456789abcdef0123456789',
	) )
);

statement_assert_same( true, Secrets::has_identity_key(), 'Secrets::has_identity_key must return true when constant defined.' );
statement_assert_same( true, Secrets::has_rate_limit_key(), 'Secrets::has_rate_limit_key must return true when constant defined.' );
statement_assert_same( true, Secrets::has_encryption_config(), 'Secrets::has_encryption_config must return true when constants defined.' );
statement_assert_same( 'v1', Secrets::get_active_key_version(), 'Secrets must return active key version.' );

// 2. Identity HMAC
$email = 'User.Test@Example.COM ';
$normalized_email = Crypto::normalize_email( $email );
statement_assert_same( 'user.test@example.com', $normalized_email, 'Email normalization must lowercase and trim.' );

$hash1 = Crypto::hash_email( $email );
$hash2 = Crypto::hash_email( 'user.test@example.com' );
statement_assert_same( $hash1, $hash2, 'Email HMAC must be deterministic after normalization.' );
statement_assert_same( 64, strlen( $hash1 ), 'Email HMAC must be 64-char sha256 hex string.' );

$ip = '192.168.1.100';
$ip_hash = Crypto::hash_ip( $ip );
statement_assert_same( 64, strlen( $ip_hash ), 'IP HMAC must be 64-char sha256 hex string.' );

// 3. Encrypted Email & Keyring Versioning
$encrypted = Crypto::encrypt_email( 'collector@statement.store' );
statement_assert_same( true, is_array( $encrypted ), 'Encrypt email must return array metadata.' );
statement_assert_same( 'v1', $encrypted['key_version'], 'Encrypted email must record active key version.' );

$decrypted = Crypto::decrypt_email( $encrypted );
statement_assert_same( 'collector@statement.store', $decrypted, 'Decrypt email must restore original plaintext email.' );

// Test decrypt with key version v2 (historical key)
$encrypted_v2 = Crypto::encrypt_email_with_version( 'historical@statement.store', 'v2' );
statement_assert_same( 'v2', $encrypted_v2['key_version'], 'Must support encrypting/decrypting with historical key version v2.' );

$decrypted_v2 = Crypto::decrypt_email( $encrypted_v2 );
statement_assert_same( 'historical@statement.store', $decrypted_v2, 'Decrypt email v2 must restore original plaintext email.' );

// Test decrypt with unknown key version => must fail safely without throwing fatal error
$encrypted_unknown = $encrypted;
$encrypted_unknown['key_version'] = 'v999';
$decrypted_unknown = Crypto::decrypt_email( $encrypted_unknown );
statement_assert_same( null, $decrypted_unknown, 'Decrypt email with unknown key version must return null.' );

// Tamper tests
$tampered_ct = $encrypted;
$tampered_ct['ciphertext'] = base64_encode( 'tampered_data_string' );
statement_assert_same( null, Crypto::decrypt_email( $tampered_ct ), 'Tampered ciphertext must fail authentication and return null.' );

$tampered_nonce = $encrypted;
$tampered_nonce['nonce'] = base64_encode( str_repeat( "\x00", 12 ) );
statement_assert_same( null, Crypto::decrypt_email( $tampered_nonce ), 'Tampered nonce must fail authentication and return null.' );

if ( isset( $encrypted['tag'] ) ) {
	$tampered_tag = $encrypted;
	$tampered_tag['tag'] = base64_encode( str_repeat( "\x00", 16 ) );
	statement_assert_same( null, Crypto::decrypt_email( $tampered_tag ), 'Tampered tag must fail authentication and return null.' );
}

$unsupported_algo = $encrypted;
$unsupported_algo['algo'] = 'unsupported_cipher_3000';
statement_assert_same( null, Crypto::decrypt_email( $unsupported_algo ), 'Unsupported crypto backend must return null (fail closed).' );

// 4. Database Schema Tables List
$table_names = Schema::get_table_names( 'wp_' );
statement_assert_same( 5, count( $table_names ), 'Schema must define 5 operational tables.' );
statement_assert_same( 'wp_statement_access_grants', $table_names['grants'], 'Grants table name must include WP prefix.' );

echo "PASS: M10 persistence & secrets test passed ({$statement_assertions} assertions).\n";
