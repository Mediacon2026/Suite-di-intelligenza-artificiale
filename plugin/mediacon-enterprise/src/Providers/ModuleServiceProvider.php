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
use Mediacon\Enterprise\Compatibility\CompatibilityAdminPage;
use Mediacon\Enterprise\Compatibility\CompatibilityModule;
use Mediacon\Enterprise\Compatibility\LegacyAssetBridge;
use Mediacon\Enterprise\Compatibility\LegacyClassBridge;
use Mediacon\Enterprise\Compatibility\LegacyContractRegistry;
use Mediacon\Enterprise\Compatibility\LegacyDiagnostics;
use Mediacon\Enterprise\Compatibility\LegacyFunctionBridge;
use Mediacon\Enterprise\Compatibility\LegacyHookBridge;
use Mediacon\Enterprise\Compatibility\LegacyPageAdapter;
use Mediacon\Enterprise\Compatibility\LegacyTemplateBridge;
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
		$container->singleton( LegacyContractRegistry::class, static fn (): LegacyContractRegistry => new LegacyContractRegistry() );
		$container->singleton( LegacyAssetBridge::class, static fn ( Container $app ): LegacyAssetBridge => new LegacyAssetBridge( $app->get( \Mediacon\Enterprise\Assets\AssetManager::class ), $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( LegacyTemplateBridge::class, static fn ( Container $app ): LegacyTemplateBridge => new LegacyTemplateBridge( $app->get( \Mediacon\Enterprise\Helpers\Template::class ), $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( LegacyPageAdapter::class, static fn ( Container $app ): LegacyPageAdapter => new LegacyPageAdapter( $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( LegacyFunctionBridge::class, static fn ( Container $app ): LegacyFunctionBridge => new LegacyFunctionBridge( $app->get( LegacyAssetBridge::class ), $app->get( LegacyTemplateBridge::class ), $app->get( LegacyPageAdapter::class ), $app->get( \Mediacon\Enterprise\Core\SettingsManager::class ), $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( LegacyClassBridge::class, static fn ( Container $app ): LegacyClassBridge => new LegacyClassBridge( $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( LegacyHookBridge::class, static fn ( Container $app ): LegacyHookBridge => new LegacyHookBridge( $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( LegacyDiagnostics::class, static fn ( Container $app ): LegacyDiagnostics => new LegacyDiagnostics( $app->get( LegacyContractRegistry::class ) ) );
		$container->singleton( CompatibilityAdminPage::class, static fn ( Container $app ): CompatibilityAdminPage => new CompatibilityAdminPage( $app->get( LegacyDiagnostics::class ), $app->get( \Mediacon\Enterprise\Helpers\Template::class ), $app->get( \Mediacon\Enterprise\Enterprise\SiteInventory::class ), $app->get( \Mediacon\Enterprise\Enterprise\DiagnosticReport::class ), $app->get( \Mediacon\Enterprise\Enterprise\MigrationManager::class ) ) );
		$container->singleton( CompatibilityModule::class, static fn ( Container $app ): CompatibilityModule => new CompatibilityModule( $app->get( LegacyContractRegistry::class ) ) );
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
		$modules->add( $container->get( CompatibilityModule::class ) );
		$modules->add( $container->get( MediationModule::class ) );
		$modules->add( $container->get( FormationModule::class ) );
		$modules->add( $container->get( EditorialModule::class ) );
		$modules->add( $container->get( PreventivoModule::class ) );
		$modules->add( $container->get( SearchModule::class ) );
	}
}
