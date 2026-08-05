<?php
/**
 * Plugin lifecycle service.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Services;

use Mediacon\Enterprise\Core\SettingsManager;

/**
 * Handles activation and deactivation tasks.
 */
final class Lifecycle {

	/**
	 * Initialize persistent plugin state.
	 *
	 * @return void
	 */
	public static function activate(): void {
		if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
			deactivate_plugins( plugin_basename( MEDIACON_ENTERPRISE_FILE ) );
			wp_die( esc_html__( 'Mediacon Enterprise requires PHP 8.2 or newer.', 'mediacon-enterprise' ) );
		}

		add_option( 'mediacon_enterprise_version', MEDIACON_ENTERPRISE_VERSION );
		add_option(
			'mediacon_enterprise_settings',
			array(
				'enabled_modules'     => SettingsManager::DEFAULT_MODULES,
				'delete_on_uninstall' => false,
			)
		);

		flush_rewrite_rules();
	}

	/**
	 * Flush runtime routing state.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
