<?php
/**
 * Editorial administration page.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Admin;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Editorial\Services\QualityAuditor;
use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;

defined( 'ABSPATH' ) || exit;

/** Saves non-destructive editorial presentation settings. */
final class EditorialAdminPage {

	/**
	 * Create the administration integration.
	 *
	 * @param ArchiveCatalog  $catalog Archive catalog.
	 * @param CardPresenter   $cards Card settings.
	 * @param QualityAuditor  $auditor Quality auditor.
	 * @param SettingsManager $settings Core settings manager.
	 * @param Template        $template Core template renderer.
	 */
	public function __construct(
		private readonly ArchiveCatalog $catalog,
		private readonly CardPresenter $cards,
		private readonly QualityAuditor $auditor,
		private readonly SettingsManager $settings,
		private readonly Template $template
	) {}

	/**
	 * Register the Editoriale submenu.
	 *
	 * @return void
	 */
	public function registerMenu(): void {
		add_submenu_page(
			'mediacon-enterprise',
			esc_html__( 'Editoriale', 'mediacon-enterprise' ),
			esc_html__( 'Editoriale', 'mediacon-enterprise' ),
			'manage_options',
			'mediacon-enterprise-editorial',
			array( $this, 'render' )
		);
	}

	/**
	 * Render settings and non-destructive quality signals.
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
				'rows'       => $this->catalog->rows(),
				'general'    => $this->cards->general(),
				'categories' => get_categories( array( 'hide_empty' => false ) ),
				'audit'      => $this->auditor->recent(),
			)
		);
	}

	/**
	 * Save validated editorial settings.
	 *
	 * @return void
	 */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update these settings.', 'mediacon-enterprise' ) );
		}

		check_admin_referer( 'mediacon_enterprise_save_editorial', 'mediacon_enterprise_editorial_nonce' );
		$submitted            = isset( $_POST['editorial'] ) && is_array( $_POST['editorial'] ) ? wp_unslash( $_POST['editorial'] ) : array();
		$submitted_categories = isset( $submitted['categories'] ) && is_array( $submitted['categories'] ) ? $submitted['categories'] : array();
		$submitted_templates  = isset( $submitted['templates'] ) && is_array( $submitted['templates'] ) ? $submitted['templates'] : array();
		$general              = isset( $submitted['general'] ) && is_array( $submitted['general'] ) ? $submitted['general'] : array();
		$categories           = array();
		$templates            = array();

		foreach ( array_keys( $this->catalog->definitions() ) as $key ) {
			$categories[ $key ] = absint( $submitted_categories[ $key ] ?? 0 );
			$templates[ $key ]  = ! empty( $submitted_templates[ $key ] );
		}

		$this->settings->set(
			'editorial',
			array(
				'categories' => $categories,
				'templates'  => $templates,
				'general'    => array(
					'title_length'    => absint( $general['title_length'] ?? 70 ),
					'excerpt_length'  => absint( $general['excerpt_length'] ?? 160 ),
					'image_ratio'     => sanitize_key( $general['image_ratio'] ?? '16-9' ),
					'columns'         => absint( $general['columns'] ?? 3 ),
					'posts_per_page'  => absint( $general['posts_per_page'] ?? 9 ),
					'single_template' => ! empty( $general['single_template'] ),
				),
			)
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'mediacon-enterprise-editorial',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
