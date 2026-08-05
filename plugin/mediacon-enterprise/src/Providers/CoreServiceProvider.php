<?php
/**
 * Core service provider.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Providers;

use Mediacon\Enterprise\Admin\AdminPage;
use Mediacon\Enterprise\Admin\GovernanceAdmin;
use Mediacon\Enterprise\Admin\SettingsAdminPage;
use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\CacheManager;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\RateLimiter;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\ServiceProvider;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Frontend\Shortcode;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Enterprise\DiagnosticReport;
use Mediacon\Enterprise\Enterprise\MigrationManager;
use Mediacon\Enterprise\Enterprise\PageGovernance;
use Mediacon\Enterprise\Enterprise\RuntimeMonitor;
use Mediacon\Enterprise\Enterprise\SiteInventory;

/**
 * Registers cross-cutting plugin services and their WordPress hooks.
 */
final class CoreServiceProvider implements ServiceProvider {

	/**
	 * Register core services.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	public function register( Container $container ): void {
		$container->singleton( CacheManager::class, static fn (): CacheManager => new CacheManager() );
		$container->singleton( RateLimiter::class, static fn ( Container $app ): RateLimiter => new RateLimiter( $app->get( CacheManager::class ) ) );
		$container->singleton( HookManager::class, static fn (): HookManager => new HookManager() );
		$container->singleton( Router::class, static fn (): Router => new Router() );
		$container->singleton( SettingsManager::class, static fn (): SettingsManager => new SettingsManager() );
		$container->singleton( AssetManager::class, static fn (): AssetManager => new AssetManager() );
		$container->singleton( Template::class, static fn (): Template => new Template() );
		$container->singleton( SiteInventory::class, static fn (): SiteInventory => new SiteInventory() );
		$container->singleton( PageGovernance::class, static fn ( Container $app ): PageGovernance => new PageGovernance( $app->get( SettingsManager::class ) ) );
		$container->singleton( RuntimeMonitor::class, static fn ( Container $app ): RuntimeMonitor => new RuntimeMonitor( $app->get( SettingsManager::class ) ) );
		$container->singleton( DiagnosticReport::class, static fn ( Container $app ): DiagnosticReport => new DiagnosticReport( $app->get( SiteInventory::class ), $app->get( RuntimeMonitor::class ) ) );
		$container->singleton( MigrationManager::class, static fn ( Container $app ): MigrationManager => new MigrationManager( $app->get( SiteInventory::class ), $app->get( PageGovernance::class ), $app->get( SettingsManager::class ) ) );
		$container->singleton( GovernanceAdmin::class, static fn ( Container $app ): GovernanceAdmin => new GovernanceAdmin( $app->get( PageGovernance::class ) ) );
		$container->singleton(
			ModuleManager::class,
			static fn ( Container $app ): ModuleManager => new ModuleManager(
				$app,
				$app->get( HookManager::class ),
				$app->get( SettingsManager::class )
			)
		);
		$container->singleton(
			AdminPage::class,
			static fn ( Container $app ): AdminPage => new AdminPage(
				$app->get( ModuleManager::class ),
				$app->get( Template::class ),
				$app->get( SettingsManager::class )
			)
		);
		$container->singleton( SettingsAdminPage::class, static fn ( Container $app ): SettingsAdminPage => new SettingsAdminPage( $app->get( SettingsManager::class ), $app->get( ModuleManager::class ), $app->get( Template::class ) ) );
		$container->singleton(
			Shortcode::class,
			static fn ( Container $app ): Shortcode => new Shortcode( $app->get( ModuleManager::class ) )
		);
	}

	/**
	 * Queue core WordPress hooks.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	public function boot( Container $container ): void {
		$hooks         = $container->get( HookManager::class );
		$assets        = $container->get( AssetManager::class );
		$settings      = $container->get( SettingsManager::class );
		$admin_page    = $container->get( AdminPage::class );
		$shortcode     = $container->get( Shortcode::class );
		$settings_page = $container->get( SettingsAdminPage::class );
		$router        = $container->get( Router::class );

		$hooks->action( 'init', array( $assets, 'register' ) );
		$hooks->action( 'init', array( $shortcode, 'register' ) );
		$hooks->action( 'admin_init', array( $settings, 'register' ) );
		$hooks->action( 'admin_menu', array( $admin_page, 'registerMenu' ) );
		$hooks->action( 'admin_menu', array( $settings_page, 'registerMenu' ), 90 );
		$hooks->action( 'admin_enqueue_scripts', array( $assets, 'enqueueAdmin' ) );
		$hooks->action( 'wp_enqueue_scripts', array( $assets, 'enqueueFrontend' ) );
		$hooks->action(
			'init',
			static function (): void {
				load_plugin_textdomain(
					'mediacon-enterprise',
					false,
					dirname( plugin_basename( MEDIACON_ENTERPRISE_FILE ) ) . '/languages'
				);
			}
		);
		$router->admin( 'mediacon_enterprise_page_governance', array( $container->get( GovernanceAdmin::class ), 'save' ) );
		$router->admin( 'mediacon_enterprise_save_settings', array( $settings_page, 'save' ) );
		$container->get( RuntimeMonitor::class )->register();
	}
}
