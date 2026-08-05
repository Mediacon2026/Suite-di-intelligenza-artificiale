<?php
/**
 * Legacy asset facade.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Assets\AssetManager;

/** Delegates legacy asset work to the Enterprise Asset Manager. */
final readonly class LegacyAssetBridge {

	public const STYLE_HANDLE  = 'mediacon-design-core';
	public const SCRIPT_HANDLE = 'mediacon-design-core';

	/**
	 * Create the asset bridge.
	 *
	 * @param AssetManager           $assets   Core asset manager.
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private AssetManager $assets, private LegacyContractRegistry $registry ) {}

	/** Register the documented compatibility handles. @return void */
	public function registerDefaults(): void {
		$this->registerStyle( self::STYLE_HANDLE, 'assets/css/core.css' );
		$this->registerScript( self::SCRIPT_HANDLE, 'assets/js/core.js' );
	}

	/**
	 * Register a legacy stylesheet.
	 *
	 * @param string            $handle       Asset handle.
	 * @param string            $path         Relative path.
	 * @param array<int,string> $dependencies Dependency handles.
	 * @return bool
	 */
	public function registerStyle( string $handle, string $path, array $dependencies = array() ): bool {
		if ( ! $this->validRelativePath( $path ) ) {
			$this->registry->error( 'A legacy stylesheet path was rejected because it was not relative to Mediacon Enterprise.' );
			return false;
		}
		$this->assets->registerStyle( $handle, $path, $dependencies );
		$this->registry->asset( $handle, 'style' );
		return true;
	}

	/**
	 * Register a legacy script.
	 *
	 * @param string            $handle       Asset handle.
	 * @param string            $path         Relative path.
	 * @param array<int,string> $dependencies Dependency handles.
	 * @param bool              $in_footer    Whether to load in the footer.
	 * @return bool
	 */
	public function registerScript( string $handle, string $path, array $dependencies = array(), bool $in_footer = true ): bool {
		if ( ! $this->validRelativePath( $path ) ) {
			$this->registry->error( 'A legacy script path was rejected because it was not relative to Mediacon Enterprise.' );
			return false;
		}
		$this->assets->registerScript( $handle, $path, $dependencies, $in_footer );
		$this->registry->asset( $handle, 'script' );
		return true;
	}

	/**
	 * Enqueue a registered stylesheet.
	 *
	 * @param string $handle Asset handle.
	 * @return void
	 */
	public function enqueueStyle( string $handle ): void {
		$this->assets->enqueueStyle( $handle );
	}

	/**
	 * Enqueue a registered script.
	 *
	 * @param string $handle Asset handle.
	 * @return void
	 */
	public function enqueueScript( string $handle ): void {
		$this->assets->enqueueScript( $handle );
	}

	/**
	 * Resolve a safe Enterprise path.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public function path( string $path = '' ): string {
		return $this->validRelativePath( $path ) ? MEDIACON_ENTERPRISE_PATH . ltrim( $path, '/\\' ) : '';
	}

	/**
	 * Resolve a safe Enterprise URL.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public function url( string $path = '' ): string {
		return $this->validRelativePath( $path ) ? MEDIACON_ENTERPRISE_URL . ltrim( $path, '/\\' ) : '';
	}

	/**
	 * Validate a relative Enterprise path.
	 *
	 * @param string $path Relative path.
	 * @return bool
	 */
	private function validRelativePath( string $path ): bool {
		return ! str_contains( str_replace( '\\', '/', $path ), '../' ) && ! preg_match( '#^[a-z][a-z0-9+.-]*://#i', $path );
	}
}
