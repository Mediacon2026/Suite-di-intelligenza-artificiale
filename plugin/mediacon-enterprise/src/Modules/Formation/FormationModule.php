<?php
/**
 * Public formation module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Formation\Admin\FormationAdminPage;
use Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Formation\Frontend\FormationAssets;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Integrates optional public formation templates with existing WordPress content.
 */
final class FormationModule implements Module {

	/**
	 * Return the module identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'formation';
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

		$assets     = $container->get( FormationAssets::class );
		$controller = $container->get( TemplateController::class );
		$admin      = $container->get( FormationAdminPage::class );

		$hooks->filter( 'mediacon_enterprise_capabilities', array( $this, 'capabilities' ) );
		$hooks->filter( 'template_include', array( $controller, 'filterTemplate' ), 60 );
		$hooks->action( 'init', array( $assets, 'register' ), 20 );
		$hooks->action( 'wp_enqueue_scripts', array( $assets, 'enqueueFrontend' ), 20 );
		$hooks->action( 'admin_enqueue_scripts', array( $assets, 'enqueueAdmin' ), 20 );
		$hooks->action( 'admin_menu', array( $admin, 'registerMenu' ), 20 );

		$container->get( Router::class )->admin( 'mediacon_enterprise_save_formation', array( $admin, 'save' ) );
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
	 * Add formation capabilities to the plugin capability map.
	 *
	 * @param array<string,string> $capabilities Existing capabilities.
	 * @return array<string,string>
	 */
	public function capabilities( array $capabilities ): array {
		$capabilities['manage_formation'] = esc_html__( 'Manage formation', 'mediacon-enterprise' );

		return $capabilities;
	}

	/**
	 * Register module services in the Core container.
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
		$container->singleton(
			CourseRepository::class,
			static fn ( Container $app ): CourseRepository => new CourseRepository( $app->get( SettingsManager::class ) )
		);
		$container->singleton(
			TeacherRepository::class,
			static fn ( Container $app ): TeacherRepository => new TeacherRepository( $app->get( SettingsManager::class ) )
		);
		$container->singleton(
			TemplateController::class,
			static fn ( Container $app ): TemplateController => new TemplateController(
				$app->get( PageCatalog::class ),
				$app->get( Content::class ),
				$app->get( CourseRepository::class ),
				$app->get( TeacherRepository::class ),
				$app->get( SettingsManager::class ),
				$app->get( Template::class )
			)
		);
		$container->singleton(
			FormationAssets::class,
			static fn ( Container $app ): FormationAssets => new FormationAssets(
				$app->get( AssetManager::class ),
				$app->get( PageCatalog::class ),
				$app->get( CourseRepository::class ),
				$app->get( TeacherRepository::class )
			)
		);
		$container->singleton(
			FormationAdminPage::class,
			static fn ( Container $app ): FormationAdminPage => new FormationAdminPage(
				$app->get( PageCatalog::class ),
				$app->get( SettingsManager::class ),
				$app->get( Template::class )
			)
		);
	}
}
