<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );

require $root . '/wp-content/plugins/statement-collector-core/src/Release/ReleaseState.php';

use Statement\Collector\Core\Release\ReleaseState;

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

$states = array(
	'UPCOMING',
	'PRIVATE_ACCESS',
	'LIVE',
	'SOLD_OUT',
	'ARCHIVED',
);

statement_assert_same( $states, ReleaseState::all(), 'Release states must match the canonical ordered set.' );
statement_assert_same( ReleaseState::UPCOMING, ReleaseState::normalize( null ), 'Missing state must default to UPCOMING.' );
statement_assert_same( ReleaseState::UPCOMING, ReleaseState::normalize( 'INVALID' ), 'Invalid persisted state must default to UPCOMING.' );

foreach ( $states as $from_index => $from ) {
	foreach ( $states as $to_index => $to ) {
		statement_assert_same(
			$to_index >= $from_index,
			ReleaseState::can_transition( $from, $to ),
			"Unexpected transition result for {$from} to {$to}."
		);
	}
}

statement_assert_same( false, ReleaseState::can_transition( ReleaseState::LIVE, 'INVALID' ), 'Invalid requested states must be rejected.' );

$terminal_expectations = array(
	ReleaseState::UPCOMING       => false,
	ReleaseState::PRIVATE_ACCESS => false,
	ReleaseState::LIVE           => false,
	ReleaseState::SOLD_OUT       => true,
	ReleaseState::ARCHIVED       => true,
);

foreach ( $terminal_expectations as $state => $expected ) {
	statement_assert_same( $expected, ReleaseState::is_terminal( $state ), "Unexpected terminal result for {$state}." );
}

fwrite( STDOUT, "PASS: M4 release-state domain passed ({$statement_assertions} assertions).\n" );
