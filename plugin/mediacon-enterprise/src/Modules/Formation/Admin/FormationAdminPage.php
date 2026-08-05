<?php
/**
 * Formation administration page.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Admin;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Saves non-destructive public formation integration settings.
 */
final class FormationAdminPage {

	/**
	 * Create the administration integration.
	 *
	 * @param PageCatalog     $catalog  Page catalog.
	 * @param SettingsManager $settings Core settings manager.
	 * @param Template        $template Core template service.
	 */
	public function __construct(
		private readonly PageCatalog $catalog,
		private readonly SettingsManager $settings,
		private readonly Template $template
	) {}

	/** Register the Formazione submenu. @return void */
	public function registerMenu(): void {
		add_submenu_page(
			'mediacon-enterprise',
			esc_html__( 'Formazione', 'mediacon-enterprise' ),
			esc_html__( 'Formazione', 'mediacon-enterprise' ),
			'manage_options',
			'mediacon-enterprise-formation',
			array( $this, 'render' )
		);
	}

	/** Render module status and settings. @return void */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}

		$formation = $this->settings->get( 'formation', array() );
		$general   = isset( $formation['general'] ) && is_array( $formation['general'] ) ? $formation['general'] : array();
		$general   = wp_parse_args(
			$general,
			array(
				'course_category'         => 'corsi',
				'teacher_category'        => 'docenti',
				'insight_category'        => 'formazione',
				'enrollment_url'          => '',
				'posts_per_page'          => 9,
				'detail_template'         => false,
				'teacher_detail_template' => false,
			)
		);
		$this->template->renderFile(
			dirname( __DIR__ ) . '/Templates/admin-page.php',
			array(
				'rows'    => $this->catalog->rows(),
				'general' => $general,
				'pages'   => get_pages(
					array(
						'post_status' => array( 'publish', 'draft', 'private' ),
						'sort_column' => 'post_title',
						'sort_order'  => 'ASC',
					)
				),
			)
		);
	}

	/** Save page, template, and general public-course settings. @return void */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update these settings.', 'mediacon-enterprise' ) );
		}

		check_admin_referer( 'mediacon_enterprise_save_formation', 'mediacon_enterprise_formation_nonce' );

		$submitted           = isset( $_POST['formation'] ) && is_array( $_POST['formation'] ) ? wp_unslash( $_POST['formation'] ) : array();
		$submitted_pages     = isset( $submitted['pages'] ) && is_array( $submitted['pages'] ) ? $submitted['pages'] : array();
		$submitted_templates = isset( $submitted['templates'] ) && is_array( $submitted['templates'] ) ? $submitted['templates'] : array();
		$submitted_general   = isset( $submitted['general'] ) && is_array( $submitted['general'] ) ? $submitted['general'] : array();
		$pages               = array();
		$templates           = array();

		foreach ( array_keys( $this->catalog->definitions() ) as $key ) {
			$pages[ $key ]     = isset( $submitted_pages[ $key ] ) ? absint( $submitted_pages[ $key ] ) : 0;
			$templates[ $key ] = ! empty( $submitted_templates[ $key ] );
		}

		$this->settings->set(
			'formation',
			array(
				'pages'     => $pages,
				'templates' => $templates,
				'general'   => array(
					'course_category'         => sanitize_title( $submitted_general['course_category'] ?? 'corsi' ),
					'teacher_category'        => sanitize_title( $submitted_general['teacher_category'] ?? 'docenti' ),
					'insight_category'        => sanitize_title( $submitted_general['insight_category'] ?? 'formazione' ),
					'enrollment_url'          => esc_url_raw( $submitted_general['enrollment_url'] ?? '' ),
					'posts_per_page'          => absint( $submitted_general['posts_per_page'] ?? 9 ),
					'detail_template'         => ! empty( $submitted_general['detail_template'] ),
					'teacher_detail_template' => ! empty( $submitted_general['teacher_detail_template'] ),
				),
			)
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'mediacon-enterprise-formation',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
