<?php
/**
 * Search results template controller.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Controllers;

use InvalidArgumentException;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Search\Services\SearchService;
use Mediacon\Enterprise\Modules\Search\Support\Highlighter;

defined( 'ABSPATH' ) || exit;

/**
 * Selects and renders the configured results page.
 */
final class TemplateController {

	/**
	 * Create the results controller.
	 *
	 * @param SearchService   $search      Search service.
	 * @param SettingsManager $settings    Core settings.
	 * @param Highlighter     $highlighter Safe highlighter.
	 */
	public function __construct( private readonly SearchService $search, private readonly SettingsManager $settings, private readonly Highlighter $highlighter ) {}

	/**
	 * Select the configured results template.
	 *
	 * @param string $original Theme template.
	 * @return string
	 */
	public function filterTemplate( string $original ): string {
		$config  = $this->config();
		$page_id = absint( $config['page_id'] ?? 0 );
		$file    = dirname( __DIR__ ) . '/Templates/page-shell.php';
		return ! empty( $config['frontend_enabled'] ) && 0 < $page_id && is_page() && get_queried_object_id() === $page_id && is_readable( $file ) ? $file : $original;
	}

	/**
	 * Execute and render a shareable search request.
	 *
	 * @return void
	 */
	public function render(): void {
		$result = null;
		$error  = '';
		$input  = isset( $_GET ) && is_array( $_GET ) ? wp_unslash( $_GET ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only shareable public filters.
		if ( '' !== trim( (string) ( $input['q'] ?? '' ) ) ) {
			try {
				$result = $this->search->search( $this->search->queryFrom( $input ) );
			} catch ( InvalidArgumentException $exception ) {
				$error = __( 'Inserisci una ricerca entro i limiti indicati.', 'mediacon-enterprise' );
			}
		}
		$config      = $this->config();
		$highlighter = $this->highlighter;
		get_header();
		include dirname( __DIR__ ) . '/Templates/results.php';
		get_footer();
	}

	/**
	 * Return search configuration.
	 *
	 * @return array<string,mixed>
	 */
	private function config(): array {
		$value = $this->settings->get( 'search', array() );
		return is_array( $value ) ? $value : array();
	}
}
