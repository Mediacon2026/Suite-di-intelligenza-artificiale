<?php
/**
 * Public Preventivo module.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Preventivo\Admin\PreventivoAdminPage;
use Mediacon\Enterprise\Modules\Preventivo\Controllers\CalculationController;
use Mediacon\Enterprise\Modules\Preventivo\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Preventivo\Frontend\PreventivoAssets;
use Mediacon\Enterprise\Modules\Preventivo\Services\PricingConfiguration;
use Mediacon\Enterprise\Modules\Preventivo\Services\QuoteCalculator;
use Mediacon\Enterprise\Modules\Preventivo\Services\SimulationNumber;
use Mediacon\Enterprise\Modules\Preventivo\Support\QuoteRequestFactory;

defined( 'ABSPATH' ) || exit;

/** Integrates the public calculator with existing Core services. */
final class PreventivoModule implements Module {

	/**
	 * Return the module identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return 'preventivo';
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
		$assets = $container->get( PreventivoAssets::class );
		$page   = $container->get( TemplateController::class );
		$admin  = $container->get( PreventivoAdminPage::class );
		$hooks->filter( 'template_include', array( $page, 'filterTemplate' ), 80 );
		$hooks->action( 'init', array( $assets, 'register' ), 40 );
		$hooks->action( 'wp_enqueue_scripts', array( $assets, 'enqueueFrontend' ), 40 );
		$hooks->action( 'admin_enqueue_scripts', array( $assets, 'enqueueAdmin' ), 40 );
		$hooks->action( 'admin_menu', array( $admin, 'registerMenu' ), 40 );
		$router = $container->get( Router::class );
		$router->admin( 'mediacon_enterprise_save_preventivo', array( $admin, 'save' ) );
		$router->rest(
			'/preventivo/calculate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $container->get( CalculationController::class ), 'calculate' ),
				'permission_callback' => '__return_true',
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
	 * Register module-specific services.
	 *
	 * @param Container $container Service container.
	 * @return void
	 */
	private function registerServices( Container $container ): void {
		$container->singleton( PricingConfiguration::class, static fn ( Container $app ): PricingConfiguration => new PricingConfiguration( $app->get( SettingsManager::class ) ) );
		$container->singleton( QuoteCalculator::class, static fn ( Container $app ): QuoteCalculator => new QuoteCalculator( $app->get( PricingConfiguration::class ) ) );
		$container->singleton( QuoteRequestFactory::class, static fn (): QuoteRequestFactory => new QuoteRequestFactory() );
		$container->singleton( SimulationNumber::class, static fn ( Container $app ): SimulationNumber => new SimulationNumber( $app->get( SettingsManager::class ) ) );
		$container->singleton( CalculationController::class, static fn ( Container $app ): CalculationController => new CalculationController( $app->get( QuoteRequestFactory::class ), $app->get( QuoteCalculator::class ), $app->get( SimulationNumber::class ) ) );
		$container->singleton( TemplateController::class, static fn ( Container $app ): TemplateController => new TemplateController( $app->get( SettingsManager::class ) ) );
		$container->singleton( PreventivoAssets::class, static fn ( Container $app ): PreventivoAssets => new PreventivoAssets( $app->get( AssetManager::class ), $app->get( SettingsManager::class ) ) );
		$container->singleton( PreventivoAdminPage::class, static fn ( Container $app ): PreventivoAdminPage => new PreventivoAdminPage( $app->get( SettingsManager::class ), $app->get( Template::class ) ) );
	}
}
