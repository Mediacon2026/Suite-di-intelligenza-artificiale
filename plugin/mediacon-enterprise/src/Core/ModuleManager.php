<?php
/**
 * Feature module manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

use InvalidArgumentException;

/**
 * Registers, exposes, and boots feature modules.
 */
final class ModuleManager {

	/**
	 * Registered modules.
	 *
	 * @var array<string,Module>
	 */
	private array $modules = array();

	/**
	 * Create the module manager.
	 *
	 * @param Container       $container Service container.
	 * @param HookManager     $hooks     Hook manager.
	 * @param SettingsManager $settings  Settings manager.
	 */
	public function __construct(
		private readonly Container $container,
		private readonly HookManager $hooks,
		private readonly SettingsManager $settings
	) {}

	/**
	 * Register a module.
	 *
	 * @param Module $module Module instance.
	 * @return void
	 * @throws InvalidArgumentException When the module identifier is invalid or already registered.
	 */
	public function add( Module $module ): void {
		$id = sanitize_key( $module->id() );

		if ( '' === $id || isset( $this->modules[ $id ] ) ) {
			throw new InvalidArgumentException( 'Module identifiers must be unique and non-empty.' );
		}

		$this->modules[ $id ] = $module;
	}

	/**
	 * Register enabled modules.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( $this->enabled() as $module ) {
			$module->register( $this->container, $this->hooks );
		}
	}

	/**
	 * Boot enabled modules.
	 *
	 * @return void
	 */
	public function boot(): void {
		foreach ( $this->enabled() as $module ) {
			$module->boot();
		}
	}

	/**
	 * Return all registered modules.
	 *
	 * @return array<string,Module>
	 */
	public function all(): array {
		return $this->modules;
	}

	/**
	 * Return enabled modules.
	 *
	 * @return array<string,Module>
	 */
	private function enabled(): array {
		$enabled = $this->settings->get( 'enabled_modules', array( 'mediation', 'formation' ) );
		$enabled = is_array( $enabled ) ? array_map( 'sanitize_key', $enabled ) : array();

		return array_intersect_key( $this->modules, array_flip( $enabled ) );
	}
}
