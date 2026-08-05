<?php
/**
 * Mediation administration page.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Admin;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Displays page connections and saves non-destructive template preferences.
 */
final class MediationAdminPage {

	/**
	 * Create the administration integration.
	 *
	 * @param PageCatalog     $catalog  Supported page catalog.
	 * @param SettingsManager $settings Core settings manager.
	 * @param Template        $template Core template service.
	 */
	public function __construct(
		private readonly PageCatalog $catalog,
		private readonly SettingsManager $settings,
		private readonly Template $template
	) {}

	/**
	 * Register the Mediazione submenu.
	 *
	 * @return void
	 */
	public function registerMenu(): void {
		add_submenu_page(
			'mediacon-enterprise',
			esc_html__( 'Mediazione', 'mediacon-enterprise' ),
			esc_html__( 'Mediazione', 'mediacon-enterprise' ),
			'manage_options',
			'mediacon-enterprise-mediation',
			array( $this, 'render' )
		);
	}

	/**
	 * Render module status and page settings.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}

		$this->template->renderFile(
			dirname( __DIR__ ) . '/Templates/admin-page.php',
			array(
				'rows'  => $this->catalog->rows(),
				'pages' => get_pages(
					array(
						'post_status' => array( 'publish', 'draft', 'private' ),
						'sort_column' => 'post_title',
						'sort_order'  => 'ASC',
					)
				),
			)
		);
	}

	/**
	 * Save page associations and per-page template switches.
	 *
	 * @return void
	 */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update these settings.', 'mediacon-enterprise' ) );
		}

		check_admin_referer( 'mediacon_enterprise_save_mediation', 'mediacon_enterprise_nonce' );

		$submitted_pages     = isset( $_POST['mediation_pages'] ) && is_array( $_POST['mediation_pages'] )
			? wp_unslash( $_POST['mediation_pages'] )
			: array();
		$submitted_templates = isset( $_POST['mediation_templates'] ) && is_array( $_POST['mediation_templates'] )
			? wp_unslash( $_POST['mediation_templates'] )
			: array();
		$pages               = array();
		$templates           = array();

		foreach ( array_keys( $this->catalog->definitions() ) as $key ) {
			$pages[ $key ]     = isset( $submitted_pages[ $key ] ) ? absint( $submitted_pages[ $key ] ) : 0;
			$templates[ $key ] = ! empty( $submitted_templates[ $key ] );
		}

		$this->settings->set(
			'mediation',
			array(
				'pages'     => $pages,
				'templates' => $templates,
			)
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'mediacon-enterprise-mediation',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
