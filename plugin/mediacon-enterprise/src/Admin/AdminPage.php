<?php
/**
 * Plugin administration page.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Admin;

use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Helpers\Template;

/**
 * Registers and renders the main administration page.
 */
final class AdminPage {

	/**
	 * Create the administration page.
	 *
	 * @param ModuleManager $modules Module manager.
	 * @param Template      $template Template renderer.
	 */
	public function __construct(
		private readonly ModuleManager $modules,
		private readonly Template $template
	) {}

	/**
	 * Register the top-level administration menu.
	 *
	 * @return void
	 */
	public function registerMenu(): void {
		add_menu_page(
			esc_html__( 'Mediacon Enterprise', 'mediacon-enterprise' ),
			esc_html__( 'Mediacon Enterprise', 'mediacon-enterprise' ),
			'manage_options',
			'mediacon-enterprise',
			array( $this, 'render' ),
			'dashicons-networking',
			58
		);
	}

	/**
	 * Render the administration page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}

		$this->template->render(
			'admin-dashboard.php',
			array(
				'modules' => $this->modules->all(),
				'version' => MEDIACON_ENTERPRISE_VERSION,
			)
		);
	}
}
