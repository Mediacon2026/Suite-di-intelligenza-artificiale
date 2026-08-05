<?php
/**
 * Plugin Name:       Mediacon Enterprise
 * Plugin URI:        https://mediacon.it/
 * Description:       Modular enterprise foundation for mediation and professional formation workflows.
 * Version:           0.3.0
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Author:            Mediacon
 * Author URI:        https://mediacon.it/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mediacon-enterprise
 * Domain Path:       /languages
 *
 * @package MediaconEnterprise
 */

defined( 'ABSPATH' ) || exit;

define( 'MEDIACON_ENTERPRISE_VERSION', '0.3.0' );
define( 'MEDIACON_ENTERPRISE_FILE', __FILE__ );
define( 'MEDIACON_ENTERPRISE_PATH', plugin_dir_path( __FILE__ ) );
define( 'MEDIACON_ENTERPRISE_URL', plugin_dir_url( __FILE__ ) );

$mediacon_enterprise_autoloader = MEDIACON_ENTERPRISE_PATH . 'vendor/autoload.php';

if ( ! is_readable( $mediacon_enterprise_autoloader ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'Mediacon Enterprise cannot start because its Composer autoloader is missing.', 'mediacon-enterprise' )
			);
		}
	);
	return;
}

require_once $mediacon_enterprise_autoloader;

register_activation_hook( __FILE__, array( Mediacon\Enterprise\Services\Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Mediacon\Enterprise\Services\Lifecycle::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		Mediacon\Enterprise\Core\Plugin::instance()->boot();
	}
);
