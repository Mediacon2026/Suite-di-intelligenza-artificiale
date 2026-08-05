<?php
/**
 * Safe template renderer.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Helpers;

use RuntimeException;

/**
 * Renders templates contained in the plugin template directory.
 */
final class Template {

	/**
	 * Render a plugin template.
	 *
	 * @param string              $name Template file name.
	 * @param array<string,mixed> $data Variables exposed to the template.
	 * @return void
	 * @throws RuntimeException When the requested template is not readable.
	 */
	public function render( string $name, array $data = array() ): void {
		$name = ltrim( str_replace( array( '../', '..\\' ), '', $name ), '/\\' );
		$file = MEDIACON_ENTERPRISE_PATH . 'templates/' . $name;
		$this->renderFile( $file, $data );
	}

	/**
	 * Render an absolute template located within the plugin directory.
	 *
	 * @param string              $file Absolute template path.
	 * @param array<string,mixed> $data Variables exposed to the template.
	 * @return void
	 * @throws RuntimeException When the template is outside the plugin or is not readable.
	 */
	public function renderFile( string $file, array $data = array() ): void {
		$plugin_root = realpath( MEDIACON_ENTERPRISE_PATH );
		$resolved    = realpath( $file );

		$plugin_prefix = false === $plugin_root ? '' : rtrim( $plugin_root, '/\\' ) . DIRECTORY_SEPARATOR;
		if ( false === $resolved || '' === $plugin_prefix || ! str_starts_with( $resolved, $plugin_prefix ) || ! is_readable( $resolved ) ) {
			throw new RuntimeException( esc_html__( 'The requested plugin template could not be loaded.', 'mediacon-enterprise' ) );
		}

		extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template variables are explicitly supplied by trusted plugin code.
		include $resolved;
	}
}
