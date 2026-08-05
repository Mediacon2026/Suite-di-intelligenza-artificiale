<?php
/**
 * Preventivo administration page.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Admin;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Scenario;

defined( 'ABSPATH' ) || exit;

/** Manages non-destructive calculator parameters. */
final readonly class PreventivoAdminPage {

	/**
	 * Create the administration page.
	 *
	 * @param SettingsManager $settings Core settings.
	 * @param Template        $template Template renderer.
	 */
	public function __construct( private SettingsManager $settings, private Template $template ) {}

	/**
	 * Register the Preventivo submenu.
	 *
	 * @return void
	 */
	public function registerMenu(): void {
		add_submenu_page( 'mediacon-enterprise', __( 'Preventivo', 'mediacon-enterprise' ), __( 'Preventivo', 'mediacon-enterprise' ), 'manage_options', 'mediacon-enterprise-preventivo', array( $this, 'render' ) );
	}

	/**
	 * Render the protected settings page.
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
				'config'    => $this->settings->get( 'preventivo', array() ),
				'scenarios' => Scenario::labels(),
			)
		);
	}

	/**
	 * Validate and save calculator settings.
	 *
	 * @return void
	 */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update these settings.', 'mediacon-enterprise' ) );
		}
		check_admin_referer( 'mediacon_enterprise_save_preventivo', 'mediacon_enterprise_preventivo_nonce' );
		$input    = isset( $_POST['preventivo'] ) && is_array( $_POST['preventivo'] ) ? wp_unslash( $_POST['preventivo'] ) : array();
		$brackets = array();
		$lines    = preg_split( '/\R/', (string) ( $input['brackets_text'] ?? '' ) );
		foreach ( false !== $lines ? $lines : array() as $line ) {
			$columns = array_map( 'trim', explode( '|', $line ) );
			if ( 2 === count( $columns ) && is_numeric( $columns[0] ) && is_numeric( $columns[1] ) ) {
				$brackets[] = array(
					'max' => (float) $columns[0],
					'fee' => (float) $columns[1],
				);
			}
		}
		$current                     = $this->settings->get( 'preventivo', array() );
		$current                     = is_array( $current ) ? $current : array();
		$current['page_id']          = absint( $input['page_id'] ?? 0 );
		$current['frontend_enabled'] = ! empty( $input['frontend_enabled'] );
		$current['brackets']         = $brackets;
		$current['reductions']       = is_array( $input['reductions'] ?? null ) ? $input['reductions'] : array();
		$current['increases']        = is_array( $input['increases'] ?? null ) ? $input['increases'] : array();
		$current['expenses']         = is_array( $input['expenses'] ?? null ) ? $input['expenses'] : array();
		$current['texts']            = is_array( $input['texts'] ?? null ) ? $input['texts'] : array();
		$current['print']            = is_array( $input['print'] ?? null ) ? $input['print'] : array();
		$current['simulation']       = is_array( $input['simulation'] ?? null ) ? $input['simulation'] : array();
		$this->settings->set( 'preventivo', $current );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'mediacon-enterprise-preventivo',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
