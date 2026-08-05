<?php
/**
 * Legacy compatibility administration screen.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Helpers\Template;

/** Renders diagnostics and serves the prebuilt bridge archive. */
final readonly class CompatibilityAdminPage {

	private const ARCHIVE = 'compatibility/mediacon-design-core-compatibility-bridge.zip';

	/**
	 * Create the administration page.
	 *
	 * @param LegacyDiagnostics $diagnostics Diagnostics service.
	 * @param Template          $template    Template renderer.
	 */
	public function __construct( private LegacyDiagnostics $diagnostics, private Template $template ) {}

	/** Register the compatibility submenu. @return void */
	public function registerMenu(): void {
		add_submenu_page(
			'mediacon-enterprise',
			__( 'Compatibilità legacy', 'mediacon-enterprise' ),
			__( 'Compatibilità legacy', 'mediacon-enterprise' ),
			'manage_options',
			'mediacon-enterprise-compatibility',
			array( $this, 'render' )
		);
	}

	/** Render diagnostics for administrators. @return void */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}
		$archive = MEDIACON_ENTERPRISE_PATH . self::ARCHIVE;
		$this->template->render(
			'compatibility-admin.php',
			array(
				'report'        => $this->diagnostics->report(),
				'archive_ready' => is_readable( $archive ),
				'archive_hash'  => is_readable( $archive ) ? (string) hash_file( 'sha256', $archive ) : '',
				'download_url'  => wp_nonce_url( admin_url( 'admin-post.php?action=mediacon_enterprise_download_bridge' ), 'mediacon_enterprise_download_bridge' ),
			)
		);
	}

	/** Stream the verified, prebuilt bridge archive. @return void */
	public function downloadBridge(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to download this file.', 'mediacon-enterprise' ) );
		}
		check_admin_referer( 'mediacon_enterprise_download_bridge' );
		$archive = MEDIACON_ENTERPRISE_PATH . self::ARCHIVE;
		if ( ! is_readable( $archive ) || false === hash_file( 'sha256', $archive ) ) {
			wp_die( esc_html__( 'The compatibility bridge archive is unavailable or could not be verified.', 'mediacon-enterprise' ) );
		}

		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="mediacon-design-core-compatibility-bridge.zip"' );
		header( 'Content-Length: ' . (string) filesize( $archive ) );
		readfile( $archive ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streams a fixed, bundled archive after capability and nonce checks.
		exit;
	}
}
