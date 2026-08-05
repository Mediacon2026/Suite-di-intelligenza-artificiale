<?php
/**
 * Mediation feature module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation;

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;

/**
 * Provides the initial mediation integration surface.
 */
final class MediationModule implements Module {

	/**
	 * Return the module identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'mediation';
	}

	/**
	 * Register module hooks.
	 *
	 * @param Container   $container Service container.
	 * @param HookManager $hooks     Hook manager.
	 * @return void
	 */
	public function register( Container $container, HookManager $hooks ): void {
		$hooks->filter( 'mediacon_enterprise_capabilities', array( $this, 'capabilities' ) );
	}

	/**
	 * Boot the module.
	 *
	 * @return void
	 */
	public function boot(): void {
		do_action( 'mediacon_enterprise_module_booted', $this->id() );
	}

	/**
	 * Add mediation capabilities to the plugin capability map.
	 *
	 * @param array<string,string> $capabilities Existing capabilities.
	 * @return array<string,string>
	 */
	public function capabilities( array $capabilities ): array {
		$capabilities['manage_mediations'] = esc_html__( 'Manage mediations', 'mediacon-enterprise' );

		return $capabilities;
	}
}
