<?php

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'STATEMENT_COLLECTOR_THEME_VERSION' ) ) {
	define( 'STATEMENT_COLLECTOR_THEME_VERSION', '0.13.0-rc.24' );
}
define( 'STATEMENT_COLLECTOR_THEME_FILE', __FILE__ );
define( 'STATEMENT_COLLECTOR_THEME_PATH', trailingslashit( get_template_directory() ) );

require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/icons.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/setup.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/customizer.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/product.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/cart.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/checkout.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/assets.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/navigation.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/home.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/catalog.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/woocommerce.php';
