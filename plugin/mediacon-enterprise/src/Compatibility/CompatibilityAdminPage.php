<?php
/**
 * Legacy compatibility administration screen.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Enterprise\DiagnosticReport;
use Mediacon\Enterprise\Enterprise\MigrationManager;
use Mediacon\Enterprise\Enterprise\SiteInventory;
use Mediacon\Enterprise\Helpers\Template;

defined( 'ABSPATH' ) || exit;

/** Renders the compatibility matrix and reversible migration controls. */
final readonly class CompatibilityAdminPage {
	private const ARCHIVE = 'compatibility/mediacon-design-core-compatibility-bridge.zip';

	/**
	 * Create the compatibility screen.
	 *
	 * @param LegacyDiagnostics $diagnostics Legacy contract diagnostics.
	 * @param Template          $template Template renderer.
	 * @param SiteInventory     $inventory Optional runtime inventory.
	 * @param DiagnosticReport  $diagnostic_report Optional normalized diagnostics.
	 * @param MigrationManager  $migrations Optional migration service.
	 */
	public function __construct(
		private LegacyDiagnostics $diagnostics,
		private Template $template,
		private ?SiteInventory $inventory = null,
		private ?DiagnosticReport $diagnostic_report = null,
		private ?MigrationManager $migrations = null
	) {}

	/** Register the compatibility submenu. */
	public function registerMenu(): void {
		add_submenu_page( 'mediacon-enterprise', __( 'Compatibilità', 'mediacon-enterprise' ), __( 'Compatibilità', 'mediacon-enterprise' ), 'manage_options', 'mediacon-enterprise-compatibility', array( $this, 'render' ) );
	}

	/** Render the compatibility matrix. */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'mediacon-enterprise' ) );
		}
		$archive = MEDIACON_ENTERPRISE_PATH . self::ARCHIVE;
		$report  = $this->diagnostics->report();
		if ( null !== $this->inventory ) {
			$report['plugins'] = $this->inventory->plugins();
		}
		$this->template->render(
			'compatibility-admin.php',
			array(
				'report'         => $report,
				'issues'         => null !== $this->diagnostic_report ? $this->diagnostic_report->issues() : array(),
				'runtime_counts' => null !== $this->diagnostic_report ? $this->diagnostic_report->runtimeCounts() : array(
					'fatal'    => 0,
					'warnings' => 0,
				),
				'archive_ready'  => is_readable( $archive ),
				'archive_hash'   => is_readable( $archive ) ? (string) hash_file( 'sha256', $archive ) : '',
				'download_url'   => wp_nonce_url( admin_url( 'admin-post.php?action=mediacon_enterprise_download_bridge' ), 'mediacon_enterprise_download_bridge' ),
			)
		);
	}

	/** Run one authenticated migration operation. */
	public function migrationAction(): void {
		if ( ! current_user_can( 'manage_options' ) || null === $this->migrations ) {
			wp_die( esc_html__( 'You are not allowed to run this migration action.', 'mediacon-enterprise' ) );
		}
		check_admin_referer( 'mediacon_enterprise_legacy_migration', 'mediacon_enterprise_migration_nonce' );
		$plugin    = sanitize_text_field( wp_unslash( $_POST['plugin'] ?? '' ) );
		$operation = sanitize_key( wp_unslash( $_POST['operation'] ?? '' ) );
		$result    = match ( $operation ) {
			'analyze'  => array(
				'success' => true,
				'message' => wp_json_encode( $this->migrations->analyze( $plugin ), JSON_UNESCAPED_SLASHES ),
			),
			'migrate'  => $this->migrations->migrate( $plugin ),
			'rollback' => $this->migrations->rollback( $plugin ),
			'verify'   => $this->migrations->verify( $plugin ),
			default    => array(
				'success' => false,
				'message' => __( 'Operazione non valida.', 'mediacon-enterprise' ),
			),
		};
		set_transient( 'mediacon_enterprise_migration_' . get_current_user_id(), $result, 60 );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => 'mediacon-enterprise-compatibility',
					'migration' => $result['success'] ? 'success' : 'error',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/** Stream the verified, bundled bridge archive. */
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
		readfile( $archive ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streams a fixed bundled archive.
		exit;
	}
}
