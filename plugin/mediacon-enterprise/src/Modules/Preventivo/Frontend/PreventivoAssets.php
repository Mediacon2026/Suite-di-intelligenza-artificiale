<?php
/**
 * Preventivo assets.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Frontend;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/** Registers scoped, dependency-free calculator assets. */
final readonly class PreventivoAssets {

	private const HANDLE = 'mediacon-enterprise-preventivo';

	/**
	 * Create the asset integration.
	 *
	 * @param AssetManager    $assets   Core asset manager.
	 * @param SettingsManager $settings Core settings.
	 */
	public function __construct( private AssetManager $assets, private SettingsManager $settings ) {}

	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public function register(): void {
		$base = 'src/Modules/Preventivo/Assets/';
		$this->assets->registerStyle( self::HANDLE, $base . 'css/preventivo.css', array( 'mediacon-enterprise' ) );
		$this->assets->registerScript( self::HANDLE, $base . 'js/preventivo.js' );
	}

	/**
	 * Enqueue assets on the configured public page.
	 *
	 * @return void
	 */
	public function enqueueFrontend(): void {
		$config  = $this->settings->get( 'preventivo', array() );
		$config  = is_array( $config ) ? $config : array();
		$page_id = absint( $config['page_id'] ?? 0 );
		if ( empty( $config['frontend_enabled'] ) || 1 > $page_id || ! is_page() || get_queried_object_id() !== $page_id ) {
			return;
		}
		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::HANDLE );
		$this->assets->enqueueScript( self::HANDLE );
	}

	/**
	 * Enqueue assets on the Preventivo administration page.
	 *
	 * @param string $hook_suffix Administration hook.
	 * @return void
	 */
	public function enqueueAdmin( string $hook_suffix ): void {
		if ( 'mediacon-enterprise_page_mediacon-enterprise-preventivo' === $hook_suffix ) {
			$this->assets->enqueueStyle( 'mediacon-enterprise' );
			$this->assets->enqueueStyle( self::HANDLE );
		}
	}
}
