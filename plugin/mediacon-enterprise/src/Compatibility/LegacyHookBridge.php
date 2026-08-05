<?php
/**
 * Legacy lifecycle hooks.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;

/** Publishes the documented compatibility lifecycle without duplicating Core. */
final class LegacyHookBridge {

	/**
	 * Shared Enterprise container.
	 *
	 * @var Container|null
	 */
	private ?Container $container = null;

	/**
	 * Create the hook bridge.
	 *
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private readonly LegacyContractRegistry $registry ) {}

	/**
	 * Register compatibility lifecycle hooks.
	 *
	 * @param Container   $container Enterprise container.
	 * @param HookManager $hooks     Hook manager.
	 * @return void
	 */
	public function register( Container $container, HookManager $hooks ): void {
		$this->container = $container;
		$hooks->action( 'init', array( $this, 'announceInit' ), 1, 0 );
		$hooks->action( 'mediacon_enterprise_compatibility_bridge_loaded', array( $this, 'bridgeLoaded' ) );
		$this->registry->hook( 'mediacon_design_core_loaded', 'action' );
		$this->registry->hook( 'mediacon_design_core_init', 'action' );
		$this->registry->hook( 'mediacon_design_core_path', 'filter' );
		$this->registry->hook( 'mediacon_design_core_url', 'filter' );
		$this->registry->hook( 'mediacon_design_core_logo_url', 'filter' );
		$this->registry->hook( 'mediacon_design_core_pages', 'filter' );
	}

	/** Announce availability after all Enterprise services are ready. @return void */
	public function announceLoaded(): void {
		do_action( 'mediacon_design_core_loaded', $this->container );
	}

	/** Announce WordPress initialization. @return void */
	public function announceInit(): void {
		do_action( 'mediacon_design_core_init', $this->container );
	}

	/**
	 * Record bridge connection without exposing its full path.
	 *
	 * @param string $bridge_file Bridge main file.
	 * @return void
	 */
	public function bridgeLoaded( string $bridge_file = '' ): void {
		if ( '' !== $bridge_file ) {
			$this->registry->template( 'bridge:' . basename( $bridge_file ) );
		}
	}
}
