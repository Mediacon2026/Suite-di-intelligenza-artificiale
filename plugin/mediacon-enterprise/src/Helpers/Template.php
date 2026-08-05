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

		if ( ! is_readable( $file ) ) {
			throw new RuntimeException( sprintf( 'Template "%s" could not be loaded.', esc_html( $name ) ) );
		}

		extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template variables are explicitly supplied by trusted plugin code.
		include $file;
	}
}
