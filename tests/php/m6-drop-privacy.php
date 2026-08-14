<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$statement_assertions = 0;

function is_admin(): bool {
	return false;
}

function wp_doing_cron(): bool {
	return false;
}

function wp_doing_ajax(): bool {
	return false;
}

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

final class Statement_Drop_Privacy_Query_Double {
	private $vars = array();

	public function is_main_query(): bool {
		return true;
	}

	public function is_post_type_archive( string $post_type ): bool {
		return false;
	}

	public function is_tax( string $taxonomy = '' ): bool {
		return 'statement_drop' === $taxonomy;
	}

	public function get( string $key ) {
		return $this->vars[ $key ] ?? null;
	}

	public function set( string $key, $value ): void {
		$this->vars[ $key ] = $value;
	}
}

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Product/Metadata.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Drop/Taxonomy.php';
require $root . '/wp-content/plugins/statement-collector-core/src/Catalog/Visibility.php';

$query = new Statement_Drop_Privacy_Query_Double();
\Statement\Collector\Core\Catalog\Visibility::apply_live_constraint( $query );
$clause = $query->get( 'meta_query' )[0] ?? array();

statement_assert_same( '_statement_release_state', $clause['key'] ?? null, 'Drop privacy must use the canonical release-state key before selection.' );
statement_assert_same( 'LIVE', $clause['value'] ?? null, 'Drop privacy must require LIVE before selection.' );
statement_assert_same( '=', $clause['compare'] ?? null, 'Drop privacy must use an exact state comparison.' );

$records = array(
	array( 'id' => 1, 'title' => 'Live Piece', 'state' => 'LIVE' ),
	array( 'id' => 2, 'title' => 'Private Piece', 'state' => 'PRIVATE_ACCESS' ),
	array( 'id' => 3, 'title' => 'Upcoming Piece', 'state' => 'UPCOMING' ),
	array( 'id' => 4, 'title' => 'Sold Piece', 'state' => 'SOLD_OUT' ),
	array( 'id' => 5, 'title' => 'Archived Piece', 'state' => 'ARCHIVED' ),
	array( 'id' => 6, 'title' => 'Missing State Piece', 'state' => null ),
);
$eligible = array_values(
	array_filter(
		$records,
		static function ( array $record ) use ( $clause ): bool {
			return '=' === ( $clause['compare'] ?? null ) && ( $clause['value'] ?? null ) === $record['state'];
		}
	)
);

statement_assert_same( array( 1 ), array_column( $eligible, 'id' ), 'Mixed Drop data must expose only the LIVE product.' );
statement_assert_same( array( 'Live Piece' ), array_column( $eligible, 'title' ), 'Hidden product titles must not pass the query constraint.' );

$hidden_only = array_slice( $records, 1 );
$hidden_eligible = array_values(
	array_filter(
		$hidden_only,
		static function ( array $record ) use ( $clause ): bool {
			return ( $clause['value'] ?? null ) === $record['state'];
		}
	)
);
statement_assert_same( array(), $hidden_eligible, 'A Drop containing no LIVE products must yield no product results.' );
statement_assert_same( false, in_array( 2, array_column( $eligible, 'id' ), true ), 'PRIVATE_ACCESS product ID must not pass the query constraint.' );
statement_assert_same( false, in_array( 'Private Piece', array_column( $eligible, 'title' ), true ), 'PRIVATE_ACCESS title must not pass the query constraint.' );

fwrite( STDOUT, "PASS: M6 Drop privacy passed ({$statement_assertions} assertions).\n" );
