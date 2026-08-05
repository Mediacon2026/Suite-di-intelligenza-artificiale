<?php
/**
 * Feature module contract.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

/**
 * Defines a pluggable Mediacon Enterprise feature module.
 */
interface Module {

	/**
	 * Return the stable module identifier.
	 *
	 * @return string
	 */
	public function id(): string;

	/**
	 * Register module hooks and services.
	 *
	 * @param Container   $container Service container.
	 * @param HookManager $hooks     Hook manager.
	 * @return void
	 */
	public function register( Container $container, HookManager $hooks ): void;

	/**
	 * Boot the module.
	 *
	 * @return void
	 */
	public function boot(): void;
}
