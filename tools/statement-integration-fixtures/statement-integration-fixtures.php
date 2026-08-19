<?php
/*
Plugin Name: Statement Integration Fixtures
Description: Temporary administrator-only runtime fixture tool for Statement Atomic integration testing.
Version: 0.3.4
Author: Statement Core Team
Text Domain: statement-integration-fixtures
*/

defined( 'ABSPATH' ) || exit;

define( 'STATEMENT_INTEGRATION_FIXTURES_VERSION', '0.3.4' );
define( 'STATEMENT_INTEGRATION_FIXTURES_FILE', __FILE__ );

require_once __DIR__ . '/src/FixtureService.php';
require_once __DIR__ . '/src/PrivateFixtureService.php';
require_once __DIR__ . '/src/VerificationService.php';
require_once __DIR__ . '/src/FinalCleanupService.php';
require_once __DIR__ . '/src/CleanupService.php';
require_once __DIR__ . '/src/QaTestService.php';
require_once __DIR__ . '/src/AdminPage.php';

\Statement\Integration\Fixtures\AdminPage::init();

// Register QA Payment Gateway lazily only when WooCommerce is ready and base class exists
add_filter(
	'woocommerce_payment_gateways',
	function ( $gateways ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return $gateways;
		}

		require_once __DIR__ . '/src/StatementQaGateway.php';

		$gateway_class = \Statement\Integration\Fixtures\StatementQaGateway::class;
		if ( class_exists( $gateway_class ) && ! in_array( $gateway_class, $gateways, true ) ) {
			$gateways[] = $gateway_class;
		}

		return $gateways;
	}
);
