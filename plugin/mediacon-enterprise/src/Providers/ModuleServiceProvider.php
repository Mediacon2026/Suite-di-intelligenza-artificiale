<?php
/**
 * Feature module provider.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Providers;

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\ServiceProvider;
use Mediacon\Enterprise\Modules\Editorial\EditorialModule;
use Mediacon\Enterprise\Modules\Formation\FormationModule;
use Mediacon\Enterprise\Modules\Mediation\MediationModule;
use Mediacon\Enterprise\Modules\Preventivo\PreventivoModule;
use Mediacon\Enterprise\Modules\Search\SearchModule;

/**
 * Adds the built-in enterprise feature modules.
 */
final class ModuleServiceProvider implements ServiceProvider {

	/**
	 * Register module services.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( MediationModule::class, static fn (): MediationModule => new MediationModule() );
		$container->singleton( FormationModule::class, static fn (): FormationModule => new FormationModule() );
		$container->singleton( EditorialModule::class, static fn (): EditorialModule => new EditorialModule() );
		$container->singleton( PreventivoModule::class, static fn (): PreventivoModule => new PreventivoModule() );
		$container->singleton( SearchModule::class, static fn (): SearchModule => new SearchModule() );
	}

	/**
	 * Add built-in modules to the module manager.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$modules = $container->get( ModuleManager::class );
		$modules->add( $container->get( MediationModule::class ) );
		$modules->add( $container->get( FormationModule::class ) );
		$modules->add( $container->get( EditorialModule::class ) );
		$modules->add( $container->get( PreventivoModule::class ) );
		$modules->add( $container->get( SearchModule::class ) );
	}
}
