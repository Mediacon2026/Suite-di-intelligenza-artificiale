<?php
/**
 * Immediate page-governance actions.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Admin;

use Mediacon\Enterprise\Enterprise\PageGovernance;

defined( 'ABSPATH' ) || exit;

/** Applies one reversible selector change per request. */
final readonly class GovernanceAdmin {

	/**
	 * Create the action handler.
	 *
	 * @param PageGovernance $governance Governance service.
	 */
	public function __construct( private PageGovernance $governance ) {}

	/** Save one ownership selector and redirect to its originating page. */
	public function save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to update page governance.', 'mediacon-enterprise' ) );
		}
		check_admin_referer( 'mediacon_enterprise_page_governance', 'mediacon_enterprise_governance_nonce' );
		$module   = sanitize_key( wp_unslash( $_POST['module'] ?? '' ) );
		$resource = sanitize_key( wp_unslash( $_POST['resource'] ?? '' ) );
		$mode     = sanitize_key( wp_unslash( $_POST['mode'] ?? '' ) );
		$success  = $this->governance->setMode( $module, $resource, $mode );
		$target   = wp_validate_redirect( wp_get_referer(), admin_url( 'admin.php?page=mediacon-enterprise' ) );
		wp_safe_redirect( add_query_arg( 'governance', $success ? 'updated' : 'invalid', $target ) );
		exit;
	}
}
