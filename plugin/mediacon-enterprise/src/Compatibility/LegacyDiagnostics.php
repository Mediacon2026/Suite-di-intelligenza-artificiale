<?php
/**
 * Non-sensitive compatibility diagnostics.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

/** Inspects installed Mediacon plugins and the runtime contract registry. */
final readonly class LegacyDiagnostics {

	private const BRIDGE_PLUGIN = 'mediacon-design-core/mediacon-design-core.php';

	/**
	 * Create the diagnostics service.
	 *
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private LegacyContractRegistry $registry ) {}

	/**
	 * Build a diagnostics report containing metadata only.
	 *
	 * @return array<string,mixed>
	 */
	public function report(): array {
		$plugins = $this->plugins();
		$runtime = $this->registry->report();

		return array_merge(
			$runtime,
			array(
				'enterprise_active' => defined( 'MEDIACON_ENTERPRISE_VERSION' ),
				'bridge_installed'  => isset( $plugins[ self::BRIDGE_PLUGIN ] ),
				'bridge_active'     => $this->isActive( self::BRIDGE_PLUGIN ),
				'plugins'           => $plugins,
			)
		);
	}

	/** Render a generic administrative warning for unresolved contracts. @return void */
	public function renderNotice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$runtime = $this->registry->report();
		$count   = count( $runtime['missing_functions'] ) + count( $runtime['missing_classes'] ) + count( $runtime['errors'] );
		if ( 0 === $count ) {
			return;
		}
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of compatibility issues. */
					_n( 'Mediacon legacy compatibility detected %d unresolved contract. Review the diagnostics page.', 'Mediacon legacy compatibility detected %d unresolved contracts. Review the diagnostics page.', $count, 'mediacon-enterprise' ),
					$count
				)
			)
		);
	}

	/**
	 * Return metadata for Mediacon plugins only.
	 *
	 * @return array<string,array{name:string,active:bool,requires_core:bool}>
	 */
	private function plugins(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			$plugin_library = ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_readable( $plugin_library ) ) {
				require_once $plugin_library;
			}
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			return array();
		}

		$result = array();
		foreach ( get_plugins() as $file => $data ) {
			$name = sanitize_text_field( (string) ( $data['Name'] ?? '' ) );
			if ( ! str_contains( strtolower( $file . ' ' . $name ), 'mediacon' ) ) {
				continue;
			}
			$requires        = $data['RequiresPlugins'] ?? '';
			$requires        = is_array( $requires ) ? $requires : array_map( 'trim', explode( ',', (string) $requires ) );
			$result[ $file ] = array(
				'name'          => $name,
				'active'        => $this->isActive( $file ),
				'requires_core' => in_array( 'mediacon-design-core', $requires, true ),
			);
		}

		ksort( $result );
		return $result;
	}

	/**
	 * Determine whether a plugin basename is active.
	 *
	 * @param string $file Plugin basename.
	 * @return bool
	 */
	private function isActive( string $file ): bool {
		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( $file );
		}
		$active = get_option( 'active_plugins', array() );
		return is_array( $active ) && in_array( $file, $active, true );
	}
}
