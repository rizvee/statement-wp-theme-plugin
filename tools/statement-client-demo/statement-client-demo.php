<?php
/*
Plugin Name: Statement Client Demo
Description: Temporary administrator-only client demo content seeder and media importer for Statement Collector's Piece.
Version: 0.2.2
Author: Statement Core Team
Text Domain: statement-client-demo
*/

namespace Statement\ClientDemo;

defined( 'ABSPATH' ) || exit;

define( 'STATEMENT_CLIENT_DEMO_VERSION', '0.2.2' );
define( 'STATEMENT_CLIENT_DEMO_FILE', __FILE__ );
define( 'STATEMENT_CLIENT_DEMO_DIR', __DIR__ );

require_once __DIR__ . '/src/AssetRegistry.php';
require_once __DIR__ . '/src/ManifestService.php';
require_once __DIR__ . '/src/DemoSeederService.php';
require_once __DIR__ . '/src/AdminPage.php';

\Statement\ClientDemo\AdminPage::init();
