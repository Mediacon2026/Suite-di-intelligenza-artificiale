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

	/** Register the public calculator shortcode. */
	public function registerShortcode(): void {
		add_shortcode( 'mediacon_calcolatore', array( $this, 'renderShortcode' ) );
	}

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
		get_header();
		$this->renderWizard();
		get_footer();
	}

	/**
	 * Render the same calculator used by the page template as shortcode output.
	 *
	 * @param array<string,mixed> $attributes Shortcode attributes, reserved for compatibility.
	 * @return string
	 */
	public function renderShortcode( array $attributes = array() ): string {
		unset( $attributes );
		ob_start();
		$this->renderWizard();
		return (string) ob_get_clean();
	}

	/** Render the shared calculator template. */
	private function renderWizard(): void {
		$config = $this->settings->get( 'preventivo', array() );
		include dirname( __DIR__ ) . '/Templates/wizard.php';
	}
}
