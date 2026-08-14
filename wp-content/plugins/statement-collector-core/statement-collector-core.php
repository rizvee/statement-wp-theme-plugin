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
require_once __DIR__ . '/src/Access/Secrets.php';
require_once __DIR__ . '/src/Access/Crypto.php';
require_once __DIR__ . '/src/Access/Schema.php';
require_once __DIR__ . '/src/Access/GrantService.php';
require_once __DIR__ . '/src/Access/SessionService.php';
require_once __DIR__ . '/src/Access/TokenService.php';
require_once __DIR__ . '/src/Access/RateLimiter.php';
require_once __DIR__ . '/src/Access/ConsentService.php';
require_once __DIR__ . '/src/Access/DropConfig.php';
require_once __DIR__ . '/src/Access/Precheck.php';
require_once __DIR__ . '/src/Access/EligibilityService.php';
require_once __DIR__ . '/src/Access/MakeDropLive.php';
require_once __DIR__ . '/src/Access/PrivateAccessGate.php';
require_once __DIR__ . '/src/Access/OrderAudit.php';
require_once __DIR__ . '/src/Access/EmailAccessGranted.php';
require_once __DIR__ . '/src/Access/EmailAccessReminder.php';
require_once __DIR__ . '/src/Access/ReminderService.php';
require_once __DIR__ . '/src/Access/UnsubscribeService.php';
require_once __DIR__ . '/src/Access/AdminUi.php';
require_once __DIR__ . '/src/Access/RetentionService.php';
require_once __DIR__ . '/src/Access/CacheHardening.php';
require_once __DIR__ . '/src/Catalog/Visibility.php';
require_once __DIR__ . '/src/Cart/Integrity.php';
require_once __DIR__ . '/src/Product/Access.php';
require_once __DIR__ . '/src/Product/Admin.php';
require_once __DIR__ . '/src/Release/Purchasability.php';
require_once __DIR__ . '/src/Order/Provenance.php';
require_once __DIR__ . '/src/Order/Completion.php';
require_once __DIR__ . '/src/Order/AdminOrderView.php';
require_once __DIR__ . '/src/Order/CustomerOrderView.php';
require_once __DIR__ . '/src/Order/EmailIntegration.php';
require_once __DIR__ . '/src/Plugin.php';

if ( function_exists( 'register_activation_hook' ) ) {
	register_activation_hook( STATEMENT_COLLECTOR_CORE_FILE, array( \Statement\Collector\Core\Access\Schema::class, 'install' ) );
}

\Statement\Collector\Core\Plugin::boot();
