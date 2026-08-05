<?php
/**
 * Core service provider.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Providers;

use Mediacon\Enterprise\Admin\AdminPage;
use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\ServiceProvider;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Frontend\Shortcode;
use Mediacon\Enterprise\Helpers\Template;

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
		$container->singleton( HookManager::class, static fn (): HookManager => new HookManager() );
		$container->singleton( Router::class, static fn (): Router => new Router() );
		$container->singleton( SettingsManager::class, static fn (): SettingsManager => new SettingsManager() );
		$container->singleton( AssetManager::class, static fn (): AssetManager => new AssetManager() );
		$container->singleton( Template::class, static fn (): Template => new Template() );
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
				$app->get( Template::class )
			)
		);
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
		$hooks      = $container->get( HookManager::class );
		$assets     = $container->get( AssetManager::class );
		$settings   = $container->get( SettingsManager::class );
		$admin_page = $container->get( AdminPage::class );
		$shortcode  = $container->get( Shortcode::class );

		$hooks->action( 'init', array( $assets, 'register' ) );
		$hooks->action( 'init', array( $shortcode, 'register' ) );
		$hooks->action( 'admin_init', array( $settings, 'register' ) );
		$hooks->action( 'admin_menu', array( $admin_page, 'registerMenu' ) );
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
	}
}
