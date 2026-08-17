<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

require_once $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Secrets.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/SecretVault.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Crypto.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/RateLimiter.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/ConsentService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/GrantService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/SessionService.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/DropConfig.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Access/Schema.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/PublicApi.php';
require_once $root . '/wp-content/plugins/statement-collector-core/src/Marketing/SignupService.php';

use Statement\Collector\Core\Access\ConsentService;
use Statement\Collector\Core\Access\Crypto;
use Statement\Collector\Core\Access\DropConfig;
use Statement\Collector\Core\Access\GrantService;
use Statement\Collector\Core\Access\RateLimiter;
use Statement\Collector\Core\Access\Secrets;
use Statement\Collector\Core\Access\SecretVault;
use Statement\Collector\Core\Access\SessionService;
use Statement\Collector\Core\Marketing\SignupService;
use Statement\Collector\Core\PublicApi;
use Statement\Collector\Core\Release\ReleaseState;

$statement_assertions = 0;

function stmt_assert( bool $condition, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

function stmt_assert_same( $expected, $actual, string $message ): void {
	global $statement_assertions;
	++$statement_assertions;

	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . "\n" );
		fwrite( STDERR, 'Actual: ' . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

class TestRedirectException extends Exception {
	public string $url;
	public int $status;

	public function __construct( string $url, int $status ) {
		$this->url    = $url;
		$this->status = $status;
		parent::__construct( "Redirect to {$url} ({$status})" );
	}
}

// In-memory Mock WPDB
class MockSignupWpdb {
	public string $prefix = 'wp_';
	public int $insert_id = 0;
	public array $tables  = array(
		'wp_statement_access_rate_limits' => array(),
		'wp_statement_consent_events'     => array(),
		'wp_statement_access_grants'      => array(),
		'wp_statement_access_sessions'    => array(),
		'wp_statement_access_tokens'      => array(),
	);

	public function prepare( string $query, ...$args ): string {
		$formatted = $query;
		foreach ( $args as $arg ) {
			if ( is_int( $arg ) || is_float( $arg ) ) {
				$val = (string) $arg;
			} else {
				$val = "'" . addslashes( (string) $arg ) . "'";
			}
			$formatted = preg_replace( '/%[dsf]/', $val, $formatted, 1 );
		}
		return $formatted;
	}

	public function get_var( string $query ) {
		// Rate limit count check
		if ( preg_match( "/SELECT COUNT\(\*\) FROM wp_statement_access_rate_limits WHERE drop_term_id = (\d+) AND scope_type = '([^']+)' AND scope_hash = '([^']+)' AND attempted_at >= '([^']+)'/", $query, $matches ) ) {
			$drop_id    = (int) $matches[1];
			$scope_type = $matches[2];
			$scope_hash = $matches[3];
			$cutoff     = $matches[4];

			$count = 0;
			foreach ( $this->tables['wp_statement_access_rate_limits'] as $row ) {
				if ( (int) $row['drop_term_id'] === $drop_id && $row['scope_type'] === $scope_type && $row['scope_hash'] === $scope_hash && $row['attempted_at'] >= $cutoff ) {
					++$count;
				}
			}
			return (string) $count;
		}

		// Consent count
		if ( false !== strpos( $query, 'COUNT(DISTINCT email_hash)' ) ) {
			$hashes = array();
			foreach ( $this->tables['wp_statement_consent_events'] as $row ) {
				if ( ( $row['event_type'] ?? '' ) === 'consent_granted' ) {
					$hashes[ $row['email_hash'] ] = true;
				}
			}
			return (string) count( $hashes );
		}

		return '0';
	}

	public function get_row( string $query, string $output = ARRAY_A ) {
		// Consent query latest
		if ( preg_match( "/SELECT \* FROM wp_statement_consent_events WHERE email_hash = '([^']+)' ORDER BY id DESC LIMIT 1/", $query, $matches ) ) {
			$email_hash = $matches[1];
			$matching   = array();
			foreach ( $this->tables['wp_statement_consent_events'] as $row ) {
				if ( $row['email_hash'] === $email_hash ) {
					$matching[] = $row;
				}
			}
			if ( empty( $matching ) ) {
				return null;
			}
			return end( $matching );
		}

		// Grant query active
		if ( preg_match( "/SELECT \* FROM wp_statement_access_grants/i", $query ) && preg_match( "/drop_term_id = (\d+)/", $query, $m1 ) && preg_match( "/email_hash = '([^']+)'/", $query, $m2 ) ) {
			$drop_id    = (int) $m1[1];
			$email_hash = $m2[1];
			$matching   = array();
			foreach ( $this->tables['wp_statement_access_grants'] as $row ) {
				if ( (int) $row['drop_term_id'] === $drop_id && $row['email_hash'] === $email_hash && empty( $row['revoked_at'] ) ) {
					$matching[] = $row;
				}
			}
			if ( empty( $matching ) ) {
				return null;
			}
			return end( $matching );
		}

		return null;
	}

	public function get_results( string $query, string $output = ARRAY_A ): array {
		if ( preg_match( "/SELECT id FROM wp_statement_access_sessions WHERE grant_id = (\d+)/", $query, $matches ) ) {
			$grant_id = (int) $matches[1];
			$results  = array();
			foreach ( $this->tables['wp_statement_access_sessions'] as $row ) {
				if ( (int) $row['grant_id'] === $grant_id && empty( $row['revoked_at'] ) ) {
					$results[] = array( 'id' => $row['id'] );
				}
			}
			return $results;
		}
		return array();
	}

	public function insert( string $table, array $data ): bool {
		++$this->insert_id;
		$data['id'] = $this->insert_id;
		$this->tables[ $table ][] = $data;
		return true;
	}

	public function update( string $table, array $data, array $where ): int {
		$updated = 0;
		foreach ( $this->tables[ $table ] as &$row ) {
			$match = true;
			foreach ( $where as $k => $v ) {
				if ( null === $v ) {
					if ( isset( $row[ $k ] ) && null !== $row[ $k ] ) {
						$match = false;
						break;
					}
				} elseif ( ( $row[ $k ] ?? null ) != $v ) {
					$match = false;
					break;
				}
			}
			if ( $match ) {
				$row = array_merge( $row, $data );
				++$updated;
			}
		}
		return $updated;
	}
}

// Global mocks
global $wpdb, $mock_options, $mock_redirects, $mock_drop_configs;
$wpdb              = new MockSignupWpdb();
$mock_options      = array();
$mock_redirects    = array();
$mock_drop_configs = array();

function get_option( string $name, $default = false ) {
	global $mock_options;
	return $mock_options[ $name ] ?? $default;
}

function update_option( string $name, $value, $autoload = null ): bool {
	global $mock_options;
	$mock_options[ $name ] = $value;
	return true;
}

function delete_option( string $name ): bool {
	global $mock_options;
	unset( $mock_options[ $name ] );
	return true;
}

function add_option( string $name, $value, string $deprecated = '', $autoload = 'yes' ): bool {
	global $mock_options;
	if ( isset( $mock_options[ $name ] ) ) {
		return false;
	}
	$mock_options[ $name ] = $value;
	return true;
}

function wp_salt( string $scheme = 'auth' ): string {
	return 'unit-test-wp-auth-salt-64bytes-hex-0000000000000000000000000000000000';
}

function sanitize_email( string $email ): string {
	return filter_var( trim( $email ), FILTER_SANITIZE_EMAIL ) ?: '';
}

function sanitize_text_field( string $str ): string {
	return trim( strip_tags( $str ) );
}

function wp_unslash( $val ) {
	return $val;
}

function is_email( string $email ): bool {
	return false !== filter_var( $email, FILTER_VALIDATE_EMAIL );
}

function wp_verify_nonce( string $nonce, string $action ): bool {
	return 'valid_signup_nonce' === $nonce;
}

function wp_safe_redirect( string $url, int $status = 302 ): void {
	global $mock_redirects;
	$mock_redirects[] = array(
		'url'    => $url,
		'status' => $status,
	);
	throw new TestRedirectException( $url, $status );
}

function add_query_arg( ...$args ): string {
	if ( count( $args ) === 3 ) {
		$key   = $args[0];
		$val   = $args[1];
		$url   = $args[2];
		$delim = false !== strpos( $url, '?' ) ? '&' : '?';
		return $url . $delim . rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $val );
	}
	if ( count( $args ) === 2 && is_array( $args[0] ) ) {
		$url = $args[1];
		foreach ( $args[0] as $k => $v ) {
			$delim = false !== strpos( $url, '?' ) ? '&' : '?';
			$url  .= $delim . rawurlencode( (string) $k ) . '=' . rawurlencode( (string) $v );
		}
		return $url;
	}
	return $args[ count( $args ) - 1 ];
}

function remove_query_arg( $keys, string $url = '' ): string {
	$parsed = parse_url( $url );
	if ( empty( $parsed['query'] ) ) {
		return $url;
	}
	parse_str( $parsed['query'], $query_args );
	foreach ( (array) $keys as $k ) {
		unset( $query_args[ $k ] );
	}
	$new_query = http_build_query( $query_args );
	$base      = ( $parsed['scheme'] ?? 'http' ) . '://' . ( $parsed['host'] ?? 'localhost' ) . ( $parsed['path'] ?? '/' );
	return $new_query ? $base . '?' . $new_query : $base;
}

function wp_get_referer() {
	return 'https://example.com/release/';
}

function home_url( string $path = '/' ): string {
	return 'https://example.com' . $path;
}

function get_term_link( $term ): string {
	return 'https://example.com/drop/' . ( $term->slug ?? 'drop-001' ) . '/';
}

function is_wp_error( $thing ): bool {
	return false;
}

function is_ssl(): bool {
	return true;
}

function get_term_meta( int $term_id, string $key, bool $single = true ) {
	global $mock_drop_configs;
	return $mock_drop_configs[ $term_id ][ $key ] ?? '';
}

function update_term_meta( int $term_id, string $key, $value ): bool {
	global $mock_drop_configs;
	$mock_drop_configs[ $term_id ][ $key ] = $value;
	return true;
}

function delete_term_meta( int $term_id, string $key ): bool {
	global $mock_drop_configs;
	unset( $mock_drop_configs[ $term_id ][ $key ] );
	return true;
}

function wp_json_encode( $data ) {
	return json_encode( $data );
}

global $mock_terms, $mock_products_by_drop, $mock_products;
$mock_terms            = array();
$mock_products_by_drop = array();
$mock_products         = array();

function get_terms( $args ) {
	global $mock_terms;
	return $mock_terms;
}

function get_posts( $args ) {
	global $mock_products_by_drop;
	$term_id = $args['tax_query'][0]['terms'] ?? 0;
	return $mock_products_by_drop[ $term_id ] ?? array();
}

class MockSignupProduct {
	private int $id;
	private array $meta;

	public function __construct( int $id, array $meta = array() ) {
		$this->id   = $id;
		$this->meta = $meta;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_type(): string {
		return 'simple';
	}

	public function get_meta( string $key, bool $single = true ) {
		return $this->meta[ $key ] ?? '';
	}

	public function update_meta_data( string $key, $value ): void {
		$this->meta[ $key ] = $value;
	}

	public function save(): int {
		return $this->id;
	}
}

function wc_get_product( $product_id ) {
	global $mock_products;
	if ( is_object( $product_id ) ) {
		return $product_id;
	}
	return $mock_products[ $product_id ] ?? null;
}

// -------------------------------------------------------------
// TEST SUITE: SignupService Contracts and Runtime Behavior
// -------------------------------------------------------------

echo "Running Statement Marketing Signup Behavioral Test Suite...\n";

// 1. Static Contract Test: RateLimiter method inspection
stmt_assert( ! method_exists( RateLimiter::class, 'is_rate_limited' ), 'RateLimiter does NOT expose nonexistent is_rate_limited' );
$ref_is_allowed = new ReflectionMethod( RateLimiter::class, 'is_allowed' );
stmt_assert_same( 5, $ref_is_allowed->getNumberOfParameters(), 'RateLimiter::is_allowed takes exactly 5 parameters ($wpdb, $drop_term_id, $ip_hash, $email_hash, $now_ts)' );

$ref_record = new ReflectionMethod( RateLimiter::class, 'record_attempt' );
stmt_assert_same( 5, $ref_record->getNumberOfParameters(), 'RateLimiter::record_attempt takes exactly 5 parameters ($wpdb, $drop_term_id, $ip_hash, $email_hash, $now_ts)' );

// Initialize SecretVault for tests
$custom_vault = array(
	'identity_key'               => str_repeat( 'aa', 32 ),
	'rate_limit_key'            => str_repeat( 'bb', 32 ),
	'encryption_active_version' => 'v1',
	'encryption_keys'           => array(
		'v1' => str_repeat( 'cc', 32 ),
	),
);
SecretVault::create_vault( $custom_vault );
Secrets::reset_cache();
stmt_assert( Secrets::is_configured(), 'SecretVault is initialized and configured' );

// 2. Crypto Hashing Contract
$email_hash = Crypto::hash_email( 'collector@example.com' );
stmt_assert( null !== $email_hash && 64 === strlen( $email_hash ), 'Crypto::hash_email produces valid 64-char hex HMAC using identity key' );

$ip_hash = Crypto::hash_ip( '192.168.1.1' );
stmt_assert( null !== $ip_hash && 64 === strlen( $ip_hash ), 'Crypto::hash_ip produces valid 64-char hex HMAC using rate limit key' );

// 3. Encrypt Email Contract
$encrypted = Crypto::encrypt_email( 'collector@example.com' );
stmt_assert( is_array( $encrypted ) && isset( $encrypted['ciphertext'], $encrypted['nonce'], $encrypted['key_version'] ), 'Crypto::encrypt_email returns authenticated ciphertext payload' );
$decrypted = Crypto::decrypt_email( $encrypted );
stmt_assert_same( 'collector@example.com', $decrypted, 'Crypto::decrypt_email round-trips encrypted email accurately' );

// 4. Mode B Test: LIVE Drop Signup
$live_drop = (object) array(
	'term_id' => 101,
	'name'    => 'Drop 001 — Monogram Study',
	'slug'    => 'drop-001-monogram-study',
);
$wpdb->tables['wp_statement_access_rate_limits'] = array();
$wpdb->tables['wp_statement_consent_events']     = array();
$wpdb->tables['wp_statement_access_grants']      = array();
$wpdb->tables['wp_statement_access_sessions']    = array();
$mock_redirects                                  = array();

// Configure PublicApi drop
$mock_terms                 = array( $live_drop );
$mock_products_by_drop[101] = array( 1001 );
$mock_products[1001]        = new MockSignupProduct( 1001, array( '_statement_release_state' => 'LIVE' ) );

// Simulate POST submission for LIVE Drop
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR']    = '203.0.113.195';
$_POST                     = array(
	'statement_signup_submit' => '1',
	'statement_signup_nonce'  => 'valid_signup_nonce',
	'statement_email'         => 'collector-live@example.com',
);

try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}

stmt_assert( ! empty( $mock_redirects ), 'Submission triggered PRG redirect' );
stmt_assert_same( 303, $mock_redirects[0]['status'], 'PRG redirect uses HTTP 303' );
stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'signup_status=success' ), 'LIVE signup redirects with signup_status=success' );
stmt_assert_same( 0, count( $wpdb->tables['wp_statement_access_grants'] ), 'Mode B NEVER creates access grant' );
stmt_assert_same( 0, count( $wpdb->tables['wp_statement_access_sessions'] ), 'Mode B NEVER creates session' );
stmt_assert_same( 1, count( $wpdb->tables['wp_statement_consent_events'] ), 'Mode B records consent event' );
stmt_assert_same( 2, count( $wpdb->tables['wp_statement_access_rate_limits'] ), 'Mode B records IP and email rate limit attempts' );

// Check that no plaintext email is stored in options
$option_signups = get_option( 'statement_marketing_signups', null );
stmt_assert( null === $option_signups, 'No giant option storage is created or updated' );

// Check stored consent event
$consent_row = $wpdb->tables['wp_statement_consent_events'][0];
stmt_assert_same( 'consent_granted', $consent_row['event_type'], 'Consent event type is consent_granted' );
stmt_assert_same( 101, (int) $consent_row['drop_term_id'], 'Consent event links to LIVE drop ID' );
stmt_assert( null === $consent_row['grant_id'], 'Consent event has null grant_id in Mode B' );
stmt_assert( ! empty( $consent_row['encrypted_email'] ), 'Consent event stores encrypted email payload in Mode B' );

// 5. Mode C Test: Generic / No Drop Signup
$wpdb->tables['wp_statement_access_rate_limits'] = array();
$wpdb->tables['wp_statement_consent_events']     = array();
$mock_redirects                                  = array();
$mock_terms                                      = array();
$mock_products_by_drop                           = array();
$mock_products                                   = array();

$_POST['statement_email'] = 'collector-generic@example.com';

try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}

stmt_assert( ! empty( $mock_redirects ), 'Mode C triggered PRG redirect' );
stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'signup_status=success' ), 'Mode C redirects with signup_status=success' );
stmt_assert_same( 0, count( $wpdb->tables['wp_statement_access_grants'] ), 'Mode C NEVER creates access grant' );
stmt_assert_same( 0, count( $wpdb->tables['wp_statement_access_sessions'] ), 'Mode C NEVER creates session' );
stmt_assert_same( 1, count( $wpdb->tables['wp_statement_consent_events'] ), 'Mode C records consent event' );

$c_rate_rows = $wpdb->tables['wp_statement_access_rate_limits'];
stmt_assert_same( 2, count( $c_rate_rows ), 'Mode C records rate limit attempts' );
stmt_assert_same( 0, (int) $c_rate_rows[0]['drop_term_id'], 'Mode C uses reserved 0 drop scope for global rate limit' );

// 6. Mode A Test: PRIVATE_ACCESS Drop Signup
$private_drop = (object) array(
	'term_id' => 202,
	'name'    => 'Drop 002 — Private Vault',
	'slug'    => 'drop-002-private-vault',
);
$wpdb->tables['wp_statement_access_rate_limits'] = array();
$wpdb->tables['wp_statement_consent_events']     = array();
$wpdb->tables['wp_statement_access_grants']      = array();
$wpdb->tables['wp_statement_access_sessions']    = array();
$mock_redirects                                  = array();

// Configure DropConfig for private drop
$now = time();
$mock_drop_configs[202] = array(
	DropConfig::META_CLOSES_AT     => gmdate( 'Y-m-d H:i:s', $now + 7200 ),
	DropConfig::META_DURATION      => 2,
	DropConfig::META_DURATION_UNIT => 'hours',
);

$mock_terms                 = array( $private_drop );
$mock_products_by_drop[202] = array( 2001 );
$mock_products[2001]        = new MockSignupProduct( 2001, array( '_statement_release_state' => 'PRIVATE_ACCESS' ) );

$_POST['statement_email'] = 'collector-private@example.com';

try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}

stmt_assert( ! empty( $mock_redirects ), 'Mode A triggered PRG redirect' );
stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'access_granted=1' ), 'Mode A redirects to Drop with access_granted=1' );
stmt_assert_same( 1, count( $wpdb->tables['wp_statement_access_grants'] ), 'Mode A creates canonical access grant' );
stmt_assert_same( 1, count( $wpdb->tables['wp_statement_access_sessions'] ), 'Mode A creates browser session' );
stmt_assert_same( 64, strlen( $wpdb->tables['wp_statement_access_sessions'][0]['token_hash'] ), 'Mode A generates cryptographically hashed session token' );
stmt_assert_same( 'statement_drop_access_202', SessionService::get_cookie_name( 202 ), 'Mode A cookie name matches drop 202' );

// 7. Mode A Idempotent Submission: Re-submitting same email reuses active grant
$mock_redirects = array();
try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}
stmt_assert_same( 1, count( $wpdb->tables['wp_statement_access_grants'] ), 'Mode A repeated submission DOES NOT duplicate grant' );

// 8. Invalid Email Rejection
$mock_redirects           = array();
$_POST['statement_email'] = 'not-an-email';
try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}
stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'signup_status=invalid' ), 'Invalid email redirects with signup_status=invalid' );

// 9. Bad Nonce Rejection
$mock_redirects                  = array();
$_POST['statement_email']        = 'valid@example.com';
$_POST['statement_signup_nonce'] = 'forged_nonce';
try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}
stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'signup_status=invalid' ), 'Bad nonce redirects with signup_status=invalid' );

// 10. Rate Limiting Enforcement
$wpdb->tables['wp_statement_access_rate_limits'] = array();
$mock_redirects                                  = array();
$_POST['statement_signup_nonce']                 = 'valid_signup_nonce';
$_POST['statement_email']                        = 'ratelimit@example.com';
$_SERVER['REMOTE_ADDR']                          = '10.0.0.1';

// Simulate 3 prior email attempts in short window (exceeding EMAIL_SHORT_LIMIT = 3)
$now_str = date( 'Y-m-d H:i:s', time() );
$email_h = Crypto::hash_email( 'ratelimit@example.com' );
$ip_h    = Crypto::hash_ip( '10.0.0.1' );

$wpdb->tables['wp_statement_access_rate_limits'][] = array( 'drop_term_id' => 0, 'scope_type' => 'email', 'scope_hash' => $email_h, 'attempted_at' => $now_str, 'expires_at' => $now_str );
$wpdb->tables['wp_statement_access_rate_limits'][] = array( 'drop_term_id' => 0, 'scope_type' => 'email', 'scope_hash' => $email_h, 'attempted_at' => $now_str, 'expires_at' => $now_str );
$wpdb->tables['wp_statement_access_rate_limits'][] = array( 'drop_term_id' => 0, 'scope_type' => 'email', 'scope_hash' => $email_h, 'attempted_at' => $now_str, 'expires_at' => $now_str );

$mock_terms                                        = array();
$mock_products_by_drop                             = array();
$mock_products                                     = array();

try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}

stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'signup_status=rate_limited' ), 'Exhausted rate limit redirects with signup_status=rate_limited' );

// 11. Fail-Closed on Unconfigured Secrets / Unavailable Vault
SecretVault::delete_vault();
Secrets::reset_cache();
stmt_assert( ! Secrets::is_configured(), 'SecretVault deleted; secrets are unconfigured' );

$mock_redirects           = array();
$_POST['statement_email'] = 'secretless@example.com';
try {
	SignupService::handle_submission();
} catch ( TestRedirectException $e ) {
	// Expected PRG redirect
}

stmt_assert( false !== strpos( $mock_redirects[0]['url'], 'signup_status=unavailable' ), 'Unconfigured vault causes fail-closed redirect with signup_status=unavailable' );

// Verify NO persistence occurred during fail-closed
$found_plaintext = false;
foreach ( $wpdb->tables['wp_statement_consent_events'] as $row ) {
	if ( false !== strpos( json_encode( $row ), 'secretless@example.com' ) ) {
		$found_plaintext = true;
	}
}
stmt_assert( ! $found_plaintext, 'Fail-closed state NEVER persists plaintext email to database' );

// 12. Active Consent Count Helper
// Restore vault
SecretVault::create_vault( $custom_vault );
Secrets::reset_cache();
$wpdb->tables['wp_statement_consent_events'] = array(
	array( 'email_hash' => 'hash1', 'event_type' => 'consent_granted' ),
	array( 'email_hash' => 'hash2', 'event_type' => 'consent_granted' ),
	array( 'email_hash' => 'hash1', 'event_type' => 'consent_granted' ), // duplicate hash
);
stmt_assert_same( 2, SignupService::get_signup_count(), 'SignupService::get_signup_count accurately queries database distinct consented emails' );

echo "PASS: All {$statement_assertions} Statement Marketing Signup behavioral assertions passed cleanly.\n";
