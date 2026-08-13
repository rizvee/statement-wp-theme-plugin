<?php

defined( 'ABSPATH' ) || exit;

define( 'STATEMENT_COLLECTOR_THEME_VERSION', '0.1.0' );
define( 'STATEMENT_COLLECTOR_THEME_PATH', trailingslashit( get_template_directory() ) );

require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/setup.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/assets.php';
require_once STATEMENT_COLLECTOR_THEME_PATH . 'inc/woocommerce.php';
