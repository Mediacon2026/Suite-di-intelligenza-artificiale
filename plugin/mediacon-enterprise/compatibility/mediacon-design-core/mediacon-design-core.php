<?php
/**
 * Plugin Name:       Mediacon Design Core Compatibility Bridge
 * Description:       Ponte di compatibilità che delega i servizi legacy a Mediacon Enterprise.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Requires Plugins:  mediacon-enterprise
 * Author:            Mediacon
 * License:           GPL-2.0-or-later
 * Text Domain:       mediacon-design-core
 *
 * @package MediaconDesignCoreBridge
 */

defined( 'ABSPATH' ) || exit;

$GLOBALS['mediacon_design_core_bridge_ready'] = false;

add_action(
	'plugins_loaded',
	static function (): void {
		if ( ! class_exists( \Mediacon\Enterprise\Core\Plugin::class ) ) {
			return;
		}

		$plugin = \Mediacon\Enterprise\Core\Plugin::instance();
		$plugin->boot();
		$container = $plugin->container();
		if ( ! $container->has( \Mediacon\Enterprise\Compatibility\CompatibilityModule::class ) ) {
			return;
		}

		$compatibility = $container->get( \Mediacon\Enterprise\Compatibility\CompatibilityModule::class );
		if ( ! $compatibility->isActive() ) {
			return;
		}

		$GLOBALS['mediacon_design_core_bridge_ready'] = true;
		do_action( 'mediacon_enterprise_compatibility_bridge_loaded', __FILE__ );
	},
	20
);

add_action(
	'admin_notices',
	static function (): void {
		if ( ! current_user_can( 'activate_plugins' ) || ! empty( $GLOBALS['mediacon_design_core_bridge_ready'] ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Mediacon Design Core Compatibility Bridge requires an active Mediacon Enterprise installation with the Compatibility module enabled.', 'mediacon-design-core' )
		);
	}
);
