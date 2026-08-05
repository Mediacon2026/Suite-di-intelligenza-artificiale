<?php
/**
 * Guarded global Design Core compatibility functions.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Compatibility\LegacyFunctionBridge;

if ( ! function_exists( 'mediacon_design_core' ) ) {
	/** Return the shared Enterprise container when compatibility is active. @return \Mediacon\Enterprise\Core\Container|null */
	function mediacon_design_core(): ?\Mediacon\Enterprise\Core\Container {
		return LegacyFunctionBridge::active()?->container();
	}
}

if ( ! function_exists( 'mediacon_design_core_path' ) ) {
	/**
	 * Resolve a safe Enterprise path.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	function mediacon_design_core_path( string $path = '' ): string {
		return LegacyFunctionBridge::active()?->path( $path ) ?? '';
	}
}

if ( ! function_exists( 'mediacon_design_core_url' ) ) {
	/**
	 * Resolve a safe Enterprise URL.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	function mediacon_design_core_url( string $path = '' ): string {
		return LegacyFunctionBridge::active()?->url( $path ) ?? '';
	}
}

if ( ! function_exists( 'mediacon_design_core_register_style' ) ) {
	/**
	 * Register an Enterprise stylesheet.
	 *
	 * @param string            $handle       Asset handle.
	 * @param string            $path         Relative path.
	 * @param array<int,string> $dependencies Dependency handles.
	 * @return bool
	 */
	function mediacon_design_core_register_style( string $handle, string $path, array $dependencies = array() ): bool {
		return LegacyFunctionBridge::active()?->registerStyle( $handle, $path, $dependencies ) ?? false;
	}
}

if ( ! function_exists( 'mediacon_design_core_register_script' ) ) {
	/**
	 * Register an Enterprise script.
	 *
	 * @param string            $handle       Asset handle.
	 * @param string            $path         Relative path.
	 * @param array<int,string> $dependencies Dependency handles.
	 * @param bool              $in_footer    Whether to load in the footer.
	 * @return bool
	 */
	function mediacon_design_core_register_script( string $handle, string $path, array $dependencies = array(), bool $in_footer = true ): bool {
		return LegacyFunctionBridge::active()?->registerScript( $handle, $path, $dependencies, $in_footer ) ?? false;
	}
}

if ( ! function_exists( 'mediacon_design_core_enqueue_style' ) ) {
	/**
	 * Enqueue a registered stylesheet.
	 *
	 * @param string $handle Asset handle.
	 * @return bool
	 */
	function mediacon_design_core_enqueue_style( string $handle ): bool {
		$bridge = LegacyFunctionBridge::active();
		if ( null === $bridge ) {
			return false;
		}
		$bridge->enqueueStyle( $handle );
		return true;
	}
}

if ( ! function_exists( 'mediacon_design_core_enqueue_script' ) ) {
	/**
	 * Enqueue a registered script.
	 *
	 * @param string $handle Asset handle.
	 * @return bool
	 */
	function mediacon_design_core_enqueue_script( string $handle ): bool {
		$bridge = LegacyFunctionBridge::active();
		if ( null === $bridge ) {
			return false;
		}
		$bridge->enqueueScript( $handle );
		return true;
	}
}

if ( ! function_exists( 'mediacon_design_core_register_page' ) ) {
	/**
	 * Register a page identifier.
	 *
	 * @param string $key     Registry key.
	 * @param int    $page_id Page identifier.
	 * @return bool
	 */
	function mediacon_design_core_register_page( string $key, int $page_id ): bool {
		return LegacyFunctionBridge::active()?->registerPage( $key, $page_id ) ?? false;
	}
}

if ( ! function_exists( 'mediacon_design_core_page_id' ) ) {
	/**
	 * Resolve a registered page.
	 *
	 * @param string $key Registry key.
	 * @return int
	 */
	function mediacon_design_core_page_id( string $key ): int {
		return LegacyFunctionBridge::active()?->pageId( $key ) ?? 0;
	}
}

if ( ! function_exists( 'mediacon_design_core_template' ) ) {
	/**
	 * Render a safe Enterprise template.
	 *
	 * @param string              $name Template name.
	 * @param array<string,mixed> $data Template data.
	 * @return bool
	 */
	function mediacon_design_core_template( string $name, array $data = array() ): bool {
		return LegacyFunctionBridge::active()?->template( $name, $data ) ?? false;
	}
}

if ( ! function_exists( 'mediacon_design_core_component' ) ) {
	/**
	 * Render a safe Enterprise component.
	 *
	 * @param string              $name Component name.
	 * @param array<string,mixed> $data Component data.
	 * @return bool
	 */
	function mediacon_design_core_component( string $name, array $data = array() ): bool {
		return LegacyFunctionBridge::active()?->component( $name, $data ) ?? false;
	}
}

if ( ! function_exists( 'mediacon_design_core_setting' ) ) {
	/**
	 * Read an Enterprise setting.
	 *
	 * @param string $key      Setting key.
	 * @param mixed  $fallback Safe fallback.
	 * @return mixed
	 */
	function mediacon_design_core_setting( string $key, mixed $fallback = null ): mixed {
		return LegacyFunctionBridge::active()?->setting( $key, $fallback ) ?? $fallback;
	}
}

if ( ! function_exists( 'mediacon_design_core_logo_url' ) ) {
	/** Return the filtered logo URL. @return string */
	function mediacon_design_core_logo_url(): string {
		return LegacyFunctionBridge::active()?->logoUrl() ?? '';
	}
}

if ( ! function_exists( 'mdc_register_page' ) ) {
	/**
	 * Register a legacy page definition with Enterprise routing.
	 *
	 * @param array<string,mixed> $definition Legacy page definition.
	 * @return bool
	 */
	function mdc_register_page( array $definition ): bool {
		return LegacyFunctionBridge::active()?->registerPageDefinition( $definition ) ?? false;
	}
}

if ( ! function_exists( 'mdc_render_page_hero' ) ) {
	/**
	 * Render the documented legacy page hero.
	 *
	 * @param string $eyebrow    Eyebrow text.
	 * @param string $title      Page title.
	 * @param string $description Introductory text.
	 * @return void
	 */
	function mdc_render_page_hero( string $eyebrow, string $title, string $description ): void {
		LegacyFunctionBridge::active()?->renderPageHero( $eyebrow, $title, $description );
	}
}

if ( ! function_exists( 'mdc_render_footer' ) ) {
	/** Render the documented legacy footer. @return void */
	function mdc_render_footer(): void {
		LegacyFunctionBridge::active()?->renderFooter();
	}
}
