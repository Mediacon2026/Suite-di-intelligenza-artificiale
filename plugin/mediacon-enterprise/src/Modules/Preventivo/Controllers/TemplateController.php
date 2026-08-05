<?php
/**
 * Preventivo page template controller.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Controllers;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/** Selects the opt-in calculator page and preserves the theme fallback. */
final class TemplateController {

	/**
	 * Create the template controller.
	 *
	 * @param SettingsManager $settings Core settings.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Select the opt-in calculator template.
	 *
	 * @param string $original Theme template.
	 * @return string
	 */
	public function filterTemplate( string $original ): string {
		$config  = $this->settings->get( 'preventivo', array() );
		$config  = is_array( $config ) ? $config : array();
		$page_id = absint( $config['page_id'] ?? 0 );
		if ( empty( $config['frontend_enabled'] ) || 1 > $page_id || ! is_page() || get_queried_object_id() !== $page_id ) {
			return $original;
		}
		$file = dirname( __DIR__ ) . '/Templates/page-shell.php';
		return is_readable( $file ) ? $file : $original;
	}

	/**
	 * Render the calculator inside the theme shell.
	 *
	 * @return void
	 */
	public function render(): void {
		$config = $this->settings->get( 'preventivo', array() );
		get_header();
		include dirname( __DIR__ ) . '/Templates/wizard.php';
		get_footer();
	}
}
