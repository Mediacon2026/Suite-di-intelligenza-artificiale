<?php
/**
 * Search frontend assets.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Frontend;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Registers scoped search assets through the Core manager.
 */
final readonly class SearchAssets {

	private const HANDLE = 'mediacon-enterprise-search';

	/**
	 * Create the asset integration.
	 *
	 * @param AssetManager    $assets   Core assets.
	 * @param SettingsManager $settings Core settings.
	 */
	public function __construct( private AssetManager $assets, private SettingsManager $settings ) {}

	/**
	 * Register search assets.
	 *
	 * @return void
	 */
	public function register(): void {
		$base = 'src/Modules/Search/Assets/';
		$this->assets->registerStyle( self::HANDLE, $base . 'css/search.css', array( 'mediacon-enterprise' ) );
		$this->assets->registerScript( self::HANDLE, $base . 'js/search.js' );
	}

	/**
	 * Enqueue assets while public search is enabled.
	 *
	 * @return void
	 */
	public function enqueueFrontend(): void {
		$config = $this->settings->get( 'search', array() );
		if ( ! is_array( $config ) || empty( $config['frontend_enabled'] ) ) {
			return;
		}
		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::HANDLE );
		if ( ! empty( $config['autocomplete'] ) ) {
			$this->assets->enqueueScript( self::HANDLE );
		}
	}

	/**
	 * Enqueue administration styles.
	 *
	 * @param string $hook_suffix Administration hook.
	 * @return void
	 */
	public function enqueueAdmin( string $hook_suffix ): void {
		if ( 'mediacon-enterprise_page_mediacon-enterprise-search' === $hook_suffix ) {
			$this->assets->enqueueStyle( 'mediacon-enterprise' );
			$this->assets->enqueueStyle( self::HANDLE );
		}
	}
}
