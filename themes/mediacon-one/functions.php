<?php
/**
 * Mediacon One bootstrap.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

define( 'MEDIACON_ONE_VERSION', '1.0.1' );
define( 'MEDIACON_ONE_PATH', trailingslashit( get_template_directory() ) );
define( 'MEDIACON_ONE_URL', trailingslashit( get_template_directory_uri() ) );

require_once MEDIACON_ONE_PATH . 'inc/setup.php';
require_once MEDIACON_ONE_PATH . 'inc/assets.php';
require_once MEDIACON_ONE_PATH . 'inc/site-config.php';
require_once MEDIACON_ONE_PATH . 'inc/template-tags.php';
require_once MEDIACON_ONE_PATH . 'inc/enterprise.php';
require_once MEDIACON_ONE_PATH . 'inc/content.php';
require_once MEDIACON_ONE_PATH . 'inc/preflight.php';
