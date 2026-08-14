<?php
/*
Plugin Name: Statement Integration Fixtures
Description: Temporary administrator-only runtime fixture tool for Statement Atomic integration testing.
Version: 0.1.0
Text Domain: statement-integration-fixtures
*/

defined( 'ABSPATH' ) || exit;

define( 'STATEMENT_INTEGRATION_FIXTURES_VERSION', '0.1.0' );
define( 'STATEMENT_INTEGRATION_FIXTURES_FILE', __FILE__ );
define( 'STATEMENT_INTEGRATION_FIXTURES_PATH', plugin_dir_path( __FILE__ ) );

require_once STATEMENT_INTEGRATION_FIXTURES_PATH . 'src/FixtureService.php';
require_once STATEMENT_INTEGRATION_FIXTURES_PATH . 'src/VerificationService.php';
require_once STATEMENT_INTEGRATION_FIXTURES_PATH . 'src/CleanupService.php';
require_once STATEMENT_INTEGRATION_FIXTURES_PATH . 'src/AdminPage.php';

add_action( 'plugins_loaded', array( 'Statement\\Integration\\Fixtures\\AdminPage', 'init' ) );
