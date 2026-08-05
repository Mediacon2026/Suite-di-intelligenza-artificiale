<?php
/**
 * Public editorial module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\Admin\EditorialAdminPage;
use Mediacon\Enterprise\Modules\Editorial\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Editorial\Frontend\EditorialAssets;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Editorial\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Editorial\Services\QualityAuditor;
use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;

defined( 'ABSPATH' ) || exit;

/** Integrates optional editorial templates with existing WordPress content. */
final class EditorialModule implements Module {

	/**
	 * Return the module identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'editorial';
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
		$assets     = $container->get( EditorialAssets::class );
		$controller = $container->get( TemplateController::class );
		$admin      = $container->get( EditorialAdminPage::class );

		$hooks->filter( 'template_include', array( $controller, 'filterTemplate' ), 70 );
		$hooks->action( 'init', array( $assets, 'register' ), 30 );
		$hooks->action( 'wp_enqueue_scripts', array( $assets, 'enqueueFrontend' ), 30 );
		$hooks->action( 'admin_enqueue_scripts', array( $assets, 'enqueueAdmin' ), 30 );
		$hooks->action( 'admin_menu', array( $admin, 'registerMenu' ), 30 );
		$container->get( Router::class )->admin( 'mediacon_enterprise_save_editorial', array( $admin, 'save' ) );
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
	 * Register shared module services.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	private function registerServices( Container $container ): void {
		if ( ! $container->has( CourseRepository::class ) ) {
			$container->singleton( CourseRepository::class, static fn ( Container $app ): CourseRepository => new CourseRepository( $app->get( SettingsManager::class ) ) );
		}
		if ( ! $container->has( TeacherRepository::class ) ) {
			$container->singleton( TeacherRepository::class, static fn ( Container $app ): TeacherRepository => new TeacherRepository( $app->get( SettingsManager::class ) ) );
		}
		$container->singleton( ArchiveCatalog::class, static fn ( Container $app ): ArchiveCatalog => new ArchiveCatalog( $app->get( SettingsManager::class ) ) );
		$container->singleton( CardPresenter::class, static fn ( Container $app ): CardPresenter => new CardPresenter( $app->get( SettingsManager::class ) ) );
		$container->singleton( EditorialRepository::class, static fn ( Container $app ): EditorialRepository => new EditorialRepository( $app->get( ArchiveCatalog::class ), $app->get( CardPresenter::class ) ) );
		$container->singleton( QualityAuditor::class, static fn (): QualityAuditor => new QualityAuditor() );
		$container->singleton(
			TemplateController::class,
			static fn ( Container $app ): TemplateController => new TemplateController( $app->get( ArchiveCatalog::class ), $app->get( EditorialRepository::class ), $app->get( CardPresenter::class ), $app->get( CourseRepository::class ), $app->get( TeacherRepository::class ), $app->get( Template::class ) )
		);
		$container->singleton(
			EditorialAssets::class,
			static fn ( Container $app ): EditorialAssets => new EditorialAssets( $app->get( AssetManager::class ), $app->get( ArchiveCatalog::class ), $app->get( CardPresenter::class ), $app->get( CourseRepository::class ), $app->get( TeacherRepository::class ) )
		);
		$container->singleton(
			EditorialAdminPage::class,
			static fn ( Container $app ): EditorialAdminPage => new EditorialAdminPage( $app->get( ArchiveCatalog::class ), $app->get( CardPresenter::class ), $app->get( QualityAuditor::class ), $app->get( SettingsManager::class ), $app->get( Template::class ) )
		);
	}
}
