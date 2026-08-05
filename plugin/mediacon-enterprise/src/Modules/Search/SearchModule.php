<?php
/**
 * Public Search module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\CacheManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;
use Mediacon\Enterprise\Core\RateLimiter;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content as FormationContent;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog as FormationPages;
use Mediacon\Enterprise\Modules\Mediation\Support\Content as MediationContent;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog as MediationPages;
use Mediacon\Enterprise\Modules\Search\Admin\SearchAdminPage;
use Mediacon\Enterprise\Modules\Search\Controllers\AutocompleteController;
use Mediacon\Enterprise\Modules\Search\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Search\Frontend\SearchAssets;
use Mediacon\Enterprise\Modules\Search\Frontend\SearchComponent;
use Mediacon\Enterprise\Modules\Search\Services\RelevanceRanker;
use Mediacon\Enterprise\Modules\Search\Services\ResultPaginator;
use Mediacon\Enterprise\Modules\Search\Services\SearchRepository;
use Mediacon\Enterprise\Modules\Search\Services\SearchService;
use Mediacon\Enterprise\Modules\Search\Services\SuggestionLimiter;
use Mediacon\Enterprise\Modules\Search\Support\Highlighter;

defined( 'ABSPATH' ) || exit;

/**
 * Integrates a bounded WordPress-native search with Core services.
 */
final class SearchModule implements Module {

	/**
	 * Return the module identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'search';
	}

	/**
	 * Register services, routes and hooks.
	 *
	 * @param Container   $container Core container.
	 * @param HookManager $hooks     Core hook manager.
	 * @return void
	 */
	public function register( Container $container, HookManager $hooks ): void {
		$this->registerServices( $container );
		$page         = $container->get( TemplateController::class );
		$assets       = $container->get( SearchAssets::class );
		$component    = $container->get( SearchComponent::class );
		$admin        = $container->get( SearchAdminPage::class );
		$cache        = $container->get( CacheManager::class );
		$autocomplete = $container->get( AutocompleteController::class );
		$hooks->filter( 'template_include', array( $page, 'filterTemplate' ), 90 );
		$hooks->action( 'init', array( $component, 'register' ), 45 );
		$hooks->action( 'init', array( $assets, 'register' ), 45 );
		$hooks->action( 'wp_enqueue_scripts', array( $assets, 'enqueueFrontend' ), 45 );
		$hooks->action( 'admin_enqueue_scripts', array( $assets, 'enqueueAdmin' ), 45 );
		$hooks->action( 'admin_menu', array( $admin, 'registerMenu' ), 45 );
		foreach ( array( 'save_post', 'deleted_post', 'edited_term' ) as $hook ) {
			$hooks->action(
				$hook,
				static function () use ( $cache ): void {
					$cache->invalidate( 'search' );
				},
				20
			);
		}
		$router = $container->get( Router::class );
		$router->admin( 'mediacon_enterprise_save_search', array( $admin, 'save' ) );
		$router->rest(
			'/search/suggest',
			array(
				'methods'             => 'POST',
				'callback'            => array( $autocomplete, 'suggest' ),
				'permission_callback' => array( $autocomplete, 'permissions' ),
			)
		);
	}

	/**
	 * Announce module boot.
	 *
	 * @return void
	 */
	public function boot(): void {
		do_action( 'mediacon_enterprise_module_booted', $this->id() );
	}

	/**
	 * Register module services and shared integrations.
	 *
	 * @param Container $container Core container.
	 * @return void
	 */
	private function registerServices( Container $container ): void {
		if ( ! $container->has( MediationContent::class ) ) {
			$container->singleton( MediationContent::class, static fn (): MediationContent => new MediationContent() );
			$container->singleton( MediationPages::class, static fn ( Container $app ): MediationPages => new MediationPages( $app->get( SettingsManager::class ) ) );
		}
		if ( ! $container->has( FormationContent::class ) ) {
			$container->singleton( FormationContent::class, static fn (): FormationContent => new FormationContent() );
			$container->singleton( FormationPages::class, static fn ( Container $app ): FormationPages => new FormationPages( $app->get( SettingsManager::class ) ) );
		}
		if ( ! $container->has( CardPresenter::class ) ) {
			$container->singleton( CardPresenter::class, static fn ( Container $app ): CardPresenter => new CardPresenter( $app->get( SettingsManager::class ) ) );
		}
		if ( ! $container->has( CourseRepository::class ) ) {
			$container->singleton( CourseRepository::class, static fn ( Container $app ): CourseRepository => new CourseRepository( $app->get( SettingsManager::class ) ) );
		}
		$container->singleton( Highlighter::class, static fn (): Highlighter => new Highlighter() );
		$container->singleton( RelevanceRanker::class, static fn (): RelevanceRanker => new RelevanceRanker() );
		$container->singleton( ResultPaginator::class, static fn (): ResultPaginator => new ResultPaginator() );
		$container->singleton( SuggestionLimiter::class, static fn (): SuggestionLimiter => new SuggestionLimiter() );
		$container->singleton( SearchRepository::class, static fn ( Container $app ): SearchRepository => new SearchRepository( $app->get( SettingsManager::class ), $app->get( MediationPages::class ), $app->get( FormationPages::class ), $app->get( MediationContent::class ), $app->get( FormationContent::class ), $app->get( CardPresenter::class ), $app->get( CourseRepository::class ) ) );
		$container->singleton( SearchService::class, static fn ( Container $app ): SearchService => new SearchService( $app->get( SearchRepository::class ), $app->get( RelevanceRanker::class ), $app->get( CacheManager::class ), $app->get( SettingsManager::class ), $app->get( ResultPaginator::class ) ) );
		$container->singleton( TemplateController::class, static fn ( Container $app ): TemplateController => new TemplateController( $app->get( SearchService::class ), $app->get( SettingsManager::class ), $app->get( Highlighter::class ) ) );
		$container->singleton( AutocompleteController::class, static fn ( Container $app ): AutocompleteController => new AutocompleteController( $app->get( SearchService::class ), $app->get( SettingsManager::class ), $app->get( RateLimiter::class ), $app->get( SuggestionLimiter::class ) ) );
		$container->singleton( SearchAssets::class, static fn ( Container $app ): SearchAssets => new SearchAssets( $app->get( AssetManager::class ), $app->get( SettingsManager::class ) ) );
		$container->singleton( SearchComponent::class, static fn ( Container $app ): SearchComponent => new SearchComponent( $app->get( SettingsManager::class ) ) );
		$container->singleton( SearchAdminPage::class, static fn ( Container $app ): SearchAdminPage => new SearchAdminPage( $app->get( SettingsManager::class ), $app->get( CacheManager::class ), $app->get( Template::class ) ) );
	}
}
