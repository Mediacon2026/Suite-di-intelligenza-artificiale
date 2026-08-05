<?php
/**
 * Service provider contract.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

/**
 * Registers and boots a group of services.
 */
interface ServiceProvider {

	/**
	 * Register services in the container.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	public function register( Container $container ): void;

	/**
	 * Boot the provider after all registrations are complete.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	public function boot( Container $container ): void;
}
