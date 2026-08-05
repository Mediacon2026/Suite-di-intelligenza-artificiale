<?php
/**
 * Mediation frontend assets.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Frontend;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Registers scoped module assets through the Core asset manager.
 */
final class MediationAssets {

	/**
	 * Module stylesheet handle.
	 *
	 * @var string
	 */
	private const STYLE = 'mediacon-enterprise-mediation';

	/**
	 * Module script handle.
	 *
	 * @var string
	 */
	private const SCRIPT = 'mediacon-enterprise-mediation';

	/**
	 * Create the asset integration.
	 *
	 * @param AssetManager $assets  Core asset manager.
	 * @param PageCatalog  $catalog Supported page catalog.
	 */
	public function __construct(
		private readonly AssetManager $assets,
		private readonly PageCatalog $catalog
	) {}

	/**
	 * Register module assets.
	 *
	 * @return void
	 */
	public function register(): void {
		$base = 'src/Modules/Mediation/Assets/';
		$this->assets->registerStyle( self::STYLE, $base . 'css/mediation.css', array( 'mediacon-enterprise' ) );
		$this->assets->registerScript( self::SCRIPT, $base . 'js/mediation.js' );
	}

	/**
	 * Enqueue assets only on an enabled mediation template.
	 *
	 * @return void
	 */
	public function enqueueFrontend(): void {
		if ( null === $this->catalog->current() ) {
			return;
		}

		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::STYLE );
		$this->assets->enqueueScript( self::SCRIPT );
	}

	/**
	 * Enqueue module styles on its administration page.
	 *
	 * @param string $hook_suffix Current administration hook suffix.
	 * @return void
	 */
	public function enqueueAdmin( string $hook_suffix ): void {
		if ( 'mediacon-enterprise_page_mediacon-enterprise-mediation' !== $hook_suffix ) {
			return;
		}

		$this->assets->enqueueStyle( 'mediacon-enterprise' );
		$this->assets->enqueueStyle( self::STYLE );
	}
}
