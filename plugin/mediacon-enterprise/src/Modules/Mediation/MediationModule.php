<?php
/**
 * Public mediation module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Mediation\Admin\MediationAdminPage;
use Mediacon\Enterprise\Modules\Mediation\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Mediation\Frontend\MediationAssets;
use Mediacon\Enterprise\Modules\Mediation\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Mediation\Support\Content;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Integrates optional mediation templates with existing WordPress pages.
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
	 * Register module services and hooks.
	 *
	 * @param Container   $container Service container.
	 * @param HookManager $hooks     Hook manager.
	 * @return void
	 */
	public function register( Container $container, HookManager $hooks ): void {
		$this->registerServices( $container );

		$assets     = $container->get( MediationAssets::class );
		$controller = $container->get( TemplateController::class );
		$admin      = $container->get( MediationAdminPage::class );

		$hooks->filter( 'mediacon_enterprise_capabilities', array( $this, 'capabilities' ) );
		$hooks->filter( 'template_include', array( $controller, 'filterTemplate' ), 50 );
		$hooks->action( 'init', array( $assets, 'register' ), 20 );
		$hooks->action( 'wp_enqueue_scripts', array( $assets, 'enqueueFrontend' ), 20 );
		$hooks->action( 'admin_enqueue_scripts', array( $assets, 'enqueueAdmin' ), 20 );
		$hooks->action( 'admin_menu', array( $admin, 'registerMenu' ), 20 );

		$container->get( Router::class )->admin( 'mediacon_enterprise_save_mediation', array( $admin, 'save' ) );
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

	/**
	 * Register module-specific services in the Core container.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	private function registerServices( Container $container ): void {
		$container->singleton( Content::class, static fn (): Content => new Content() );
		$container->singleton(
			PageCatalog::class,
			static fn ( Container $app ): PageCatalog => new PageCatalog( $app->get( SettingsManager::class ) )
		);
		$container->singleton( EditorialRepository::class, static fn (): EditorialRepository => new EditorialRepository() );
		$container->singleton(
			TemplateController::class,
			static fn ( Container $app ): TemplateController => new TemplateController(
				$app->get( PageCatalog::class ),
				$app->get( Content::class ),
				$app->get( EditorialRepository::class ),
				$app->get( Template::class )
			)
		);
		$container->singleton(
			MediationAssets::class,
			static fn ( Container $app ): MediationAssets => new MediationAssets(
				$app->get( AssetManager::class ),
				$app->get( PageCatalog::class )
			)
		);
		$container->singleton(
			MediationAdminPage::class,
			static fn ( Container $app ): MediationAdminPage => new MediationAdminPage(
				$app->get( PageCatalog::class ),
				$app->get( SettingsManager::class ),
				$app->get( Template::class )
			)
		);
	}
}
