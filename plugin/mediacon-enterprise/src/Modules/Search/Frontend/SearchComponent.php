<?php
/**
 * Reusable global search component.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Frontend;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Provides a shortcode suitable for headers and page content.
 */
final readonly class SearchComponent {

	/**
	 * Create the reusable component.
	 *
	 * @param SettingsManager $settings Core settings.
	 */
	public function __construct( private SettingsManager $settings ) {}

	/**
	 * Register the search shortcode.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'mediacon_search', array( $this, 'render' ) );
	}

	/**
	 * Render the public search form.
	 *
	 * @return string
	 */
	public function render(): string {
		$config  = $this->settings->get( 'search', array() );
		$config  = is_array( $config ) ? $config : array();
		$page_id = absint( $config['page_id'] ?? 0 );
		if ( empty( $config['frontend_enabled'] ) || 0 === $page_id ) {
			return '';
		}
		$search_action = (string) get_permalink( $page_id );
		ob_start();
		include dirname( __DIR__ ) . '/Templates/search-form.php';
		return (string) ob_get_clean();
	}
}
