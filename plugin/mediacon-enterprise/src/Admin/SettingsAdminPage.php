<?php
/**
 * Enterprise settings administration.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Admin;

use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;

defined( 'ABSPATH' ) || exit;

/** Renders and saves reversible module switches. */
final class SettingsAdminPage {

	/**
	 * Create the settings page.
	 *
	 * @param SettingsManager $settings Settings service.
	 * @param ModuleManager   $modules Module registry.
	 * @param Template        $template Template renderer.
	 */
	public function __construct(
		private readonly SettingsManager $settings,
		private readonly ModuleManager $modules,
		private readonly Template $template
	) {}

	/** Register the settings submenu. */
	public function registerMenu(): void {
		add_submenu_page( 'mediacon-enterprise', __( 'Impostazioni', 'mediacon-enterprise' ), __( 'Impostazioni', 'mediacon-enterprise' ), 'manage_options', 'mediacon-enterprise-settings', array( $this, 'render' ) );
	}

	/** Render module and diagnostic switches. */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}
		$this->template->render(
			'settings-admin.php',
			array(
				'modules'             => $this->modules->all(),
				'enabled'             => $this->settings->get( 'enabled_modules', SettingsManager::DEFAULT_MODULES ),
				'diagnostics_enabled' => (bool) $this->settings->get( 'diagnostics_enabled', true ),
				'delete_on_uninstall' => (bool) $this->settings->get( 'delete_on_uninstall', false ),
			)
		);
	}

	/** Save all switches without removing preserved settings. */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update these settings.', 'mediacon-enterprise' ) );
		}
		check_admin_referer( 'mediacon_enterprise_save_settings', 'mediacon_enterprise_settings_nonce' );
		$enabled = isset( $_POST['enabled_modules'] ) && is_array( $_POST['enabled_modules'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['enabled_modules'] ) ) : array();
		$this->settings->set( 'enabled_modules', $enabled );
		$this->settings->set( 'compatibility_enabled', in_array( 'compatibility', $enabled, true ) );
		$this->settings->set( 'diagnostics_enabled', ! empty( $_POST['diagnostics_enabled'] ) );
		$this->settings->set( 'delete_on_uninstall', ! empty( $_POST['delete_on_uninstall'] ) );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'mediacon-enterprise-settings',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
