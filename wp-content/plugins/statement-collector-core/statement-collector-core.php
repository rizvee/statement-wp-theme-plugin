<?php
/*
Plugin Name: Statement Collector Core
Description: Durable domain foundation for Statement Collector's Piece.
Version: 0.1.0
Text Domain: statement-collector-core
*/

defined( 'ABSPATH' ) || exit;

define( 'STATEMENT_COLLECTOR_CORE_VERSION', '0.1.0' );
define( 'STATEMENT_COLLECTOR_CORE_FILE', __FILE__ );

require_once __DIR__ . '/src/Release/ReleaseState.php';
require_once __DIR__ . '/src/Product/Metadata.php';
require_once __DIR__ . '/src/Drop/Taxonomy.php';
require_once __DIR__ . '/src/PublicApi.php';
require_once __DIR__ . '/src/Catalog/Visibility.php';
require_once __DIR__ . '/src/Product/Access.php';
require_once __DIR__ . '/src/Product/Admin.php';
require_once __DIR__ . '/src/Release/Purchasability.php';
require_once __DIR__ . '/src/Plugin.php';

\Statement\Collector\Core\Plugin::boot();
