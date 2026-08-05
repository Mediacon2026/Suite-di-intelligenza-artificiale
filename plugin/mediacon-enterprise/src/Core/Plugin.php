<?php
/**
 * Main plugin application.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

use Mediacon\Enterprise\Providers\CoreServiceProvider;
use Mediacon\Enterprise\Providers\ModuleServiceProvider;

/**
 * Builds and boots the Mediacon Enterprise application.
 */
final class Plugin {

	/**
	 * Shared application instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Whether the application has booted.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Providers used by the application.
	 *
	 * @var array<int,ServiceProvider>
	 */
	private array $providers = array();

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {
		$this->container = new Container();
		$this->container->instance( Container::class, $this->container );
	}

	/**
	 * Return the shared plugin instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register and boot the application once.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->providers = array(
			new CoreServiceProvider(),
			new ModuleServiceProvider(),
		);

		foreach ( $this->providers as $provider ) {
			$provider->register( $this->container );
		}

		foreach ( $this->providers as $provider ) {
			$provider->boot( $this->container );
		}

		$this->container->get( ModuleManager::class )->register();
		$this->container->get( Router::class )->register();
		$this->container->get( HookManager::class )->register();
		$this->container->get( ModuleManager::class )->boot();

		$this->booted = true;
		do_action( 'mediacon_enterprise_booted', $this->container );
	}

	/**
	 * Expose the service container for integrations.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}
}
