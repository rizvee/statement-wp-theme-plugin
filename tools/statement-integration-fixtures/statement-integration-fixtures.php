<?php
/*
Plugin Name: Statement Integration Fixtures
Description: Temporary administrator-only runtime fixture tool for Statement Atomic integration testing.
Version: 0.2.1
Author: Statement Core Team
Text Domain: statement-integration-fixtures
*/

defined( 'ABSPATH' ) || exit;

define( 'STATEMENT_INTEGRATION_FIXTURES_VERSION', '0.2.1' );
define( 'STATEMENT_INTEGRATION_FIXTURES_FILE', __FILE__ );

require_once __DIR__ . '/src/FixtureService.php';
require_once __DIR__ . '/src/PrivateFixtureService.php';
require_once __DIR__ . '/src/VerificationService.php';
require_once __DIR__ . '/src/CleanupService.php';
require_once __DIR__ . '/src/AdminPage.php';

\Statement\Integration\Fixtures\AdminPage::init();
