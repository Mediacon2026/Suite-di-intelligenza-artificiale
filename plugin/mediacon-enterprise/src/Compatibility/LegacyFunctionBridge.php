<?php
/**
 * Documented Design Core function facade.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\SettingsManager;

/** Routes guarded compatibility helpers into Enterprise services. */
final class LegacyFunctionBridge {

	/** Active facade instance.
	 *
	 * @var self|null
	 */
	private static ?self $active = null;

	/** Enterprise service container.
	 *
	 * @var Container|null
	 */
	private ?Container $container = null;

	/**
	 * Create the function bridge.
	 *
	 * @param LegacyAssetBridge      $assets    Asset bridge.
	 * @param LegacyTemplateBridge   $templates Template bridge.
	 * @param SettingsManager        $settings  Settings manager.
	 * @param LegacyContractRegistry $registry  Contract registry.
	 */
	public function __construct(
		private readonly LegacyAssetBridge $assets,
		private readonly LegacyTemplateBridge $templates,
		private readonly SettingsManager $settings,
		private readonly LegacyContractRegistry $registry
	) {}

	/**
	 * Connect the facade to the shared Enterprise container.
	 *
	 * @param Container $container Enterprise container.
	 * @return void
	 */
	public function activate( Container $container ): void {
		$this->container = $container;
		self::$active    = $this;
	}

	/** Return the active facade, when compatibility is enabled. @return self|null */
	public static function active(): ?self {
		return self::$active;
	}

	/** Return the shared Enterprise container. @return Container|null */
	public function container(): ?Container {
		return $this->container;
	}

	/**
	 * Resolve a safe path below the Enterprise root.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public function path( string $path = '' ): string {
		return (string) apply_filters( 'mediacon_design_core_path', $this->assets->path( $path ), $path );
	}

	/**
	 * Resolve a safe URL below the Enterprise root.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public function url( string $path = '' ): string {
		return (string) apply_filters( 'mediacon_design_core_url', $this->assets->url( $path ), $path );
	}

	/**
	 * Register a stylesheet through Enterprise.
	 *
	 * @param string            $handle       Asset handle.
	 * @param string            $path         Relative path.
	 * @param array<int,string> $dependencies Dependency handles.
	 * @return bool
	 */
	public function registerStyle( string $handle, string $path, array $dependencies = array() ): bool {
		return $this->assets->registerStyle( $handle, $path, $dependencies );
	}

	/**
	 * Register a script through Enterprise.
	 *
	 * @param string            $handle       Asset handle.
	 * @param string            $path         Relative path.
	 * @param array<int,string> $dependencies Dependency handles.
	 * @param bool              $in_footer    Whether to load in the footer.
	 * @return bool
	 */
	public function registerScript( string $handle, string $path, array $dependencies = array(), bool $in_footer = true ): bool {
		return $this->assets->registerScript( $handle, $path, $dependencies, $in_footer );
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
	 * Register a page identifier without creating content.
	 *
	 * @param string $key     Registry key.
	 * @param int    $page_id Page identifier.
	 * @return bool
	 */
	public function registerPage( string $key, int $page_id ): bool {
		if ( '' === sanitize_key( $key ) || 1 > $page_id ) {
			$this->registry->error( 'A legacy page registration was rejected because its key or page identifier was invalid.' );
			return false;
		}
		$this->registry->page( $key, $page_id );
		return true;
	}

	/**
	 * Resolve a registered page identifier.
	 *
	 * @param string $key Registry key.
	 * @return int
	 */
	public function pageId( string $key ): int {
		$pages = apply_filters( 'mediacon_design_core_pages', $this->registry->report()['pages'] );
		$pages = is_array( $pages ) ? $pages : array();
		return absint( $pages[ sanitize_key( $key ) ] ?? 0 );
	}

	/**
	 * Render an Enterprise template safely.
	 *
	 * @param string              $name Template name.
	 * @param array<string,mixed> $data Template data.
	 * @return bool
	 */
	public function template( string $name, array $data = array() ): bool {
		return $this->templates->render( $name, $data );
	}

	/**
	 * Render a common Enterprise component safely.
	 *
	 * @param string              $name Component name.
	 * @param array<string,mixed> $data Component data.
	 * @return bool
	 */
	public function component( string $name, array $data = array() ): bool {
		return $this->templates->component( $name, $data );
	}

	/**
	 * Read an Enterprise setting without exposing writes.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Safe fallback.
	 * @return mixed
	 */
	public function setting( string $key, mixed $fallback = null ): mixed {
		return $this->settings->get( sanitize_key( $key ), $fallback );
	}

	/** Return the filtered logo URL without fabricating a fallback. @return string */
	public function logoUrl(): string {
		return (string) apply_filters( 'mediacon_design_core_logo_url', '' );
	}
}
