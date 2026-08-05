<?php
/**
 * Compatibility layer regression tests.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Compatibility\CompatibilityModule;
use Mediacon\Enterprise\Compatibility\CompatibilityAdminPage;
use Mediacon\Enterprise\Compatibility\LegacyAssetBridge;
use Mediacon\Enterprise\Compatibility\LegacyClassBridge;
use Mediacon\Enterprise\Compatibility\LegacyContractRegistry;
use Mediacon\Enterprise\Compatibility\LegacyDiagnostics;
use Mediacon\Enterprise\Compatibility\LegacyFunctionBridge;
use Mediacon\Enterprise\Compatibility\LegacyTemplateBridge;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use PHPUnit\Framework\TestCase;

/** Verifies guarded infrastructure contracts without a Design Core runtime. */
final class CompatibilityLayerTest extends TestCase {

	/** Reset test doubles. */
	protected function setUp(): void {
		$GLOBALS['mediacon_test_options']          = array();
		$GLOBALS['mediacon_test_actions']          = array();
		$GLOBALS['mediacon_test_filters']          = array();
		$GLOBALS['mediacon_test_styles']           = array();
		$GLOBALS['mediacon_test_scripts']          = array();
		$GLOBALS['mediacon_test_enqueued_styles']  = array();
		$GLOBALS['mediacon_test_enqueued_scripts'] = array();
		$GLOBALS['mediacon_test_plugins']          = array();
	}

	/** Constants, functions, assets, pages, settings, and templates must delegate safely. */
	public function testInfrastructureContractsAreAvailable(): void {
		$services = $this->services();
		$module   = new CompatibilityModule( $services['registry'] );
		$loaded   = 0;
		add_action(
			'mediacon_design_core_loaded',
			static function () use ( &$loaded ): void {
				++$loaded;
			}
		);
		$module->register( $services['container'], $services['hooks'] );
		$services['hooks']->register();
		$module->boot();

		self::assertTrue( $module->isActive() );
		self::assertSame( 1, $loaded );
		self::assertSame( '0.7.0', MEDIACON_DESIGN_CORE_VERSION );
		self::assertSame( $services['container'], mediacon_design_core() );
		self::assertStringEndsWith( '/assets/test.css', mediacon_design_core_url( 'assets/test.css' ) );
		self::assertTrue( mediacon_design_core_register_style( 'legacy-style', 'assets/test.css' ) );
		self::assertTrue( mediacon_design_core_register_script( 'legacy-script', 'assets/test.js' ) );
		self::assertArrayHasKey( 'legacy-style', $GLOBALS['mediacon_test_styles'] );
		self::assertArrayHasKey( 'legacy-script', $GLOBALS['mediacon_test_scripts'] );
		self::assertTrue( mediacon_design_core_register_page( 'legacy-home', 42 ) );
		self::assertSame( 42, mediacon_design_core_page_id( 'legacy-home' ) );
		self::assertSame( 'fallback', mediacon_design_core_setting( 'unknown', 'fallback' ) );

		ob_start();
		$rendered = mediacon_design_core_template(
			'admin-dashboard.php',
			array(
				'modules' => array(),
				'version' => 'test',
			)
		);
		$output   = (string) ob_get_clean();
		self::assertTrue( $rendered );
		self::assertStringContainsString( 'Mediacon Enterprise', $output );
		self::assertFalse( mediacon_design_core_template( 'missing-template.php' ) );
	}

	/** Traversal and missing requirements must be rejected and reported. */
	public function testFailuresAreReportedWithoutFabricatedResults(): void {
		$services = $this->services();
		self::assertFalse( $services['assets']->registerStyle( 'unsafe', '../secret.css' ) );
		self::assertFalse( $services['registry']->requireFunction( 'mediacon_unknown_legacy_function' ) );
		self::assertFalse( $services['registry']->requireClass( 'Mediacon_Unknown_Legacy_Class' ) );
		$report = $services['registry']->report();
		self::assertContains( 'mediacon_unknown_legacy_function', $report['missing_functions'] );
		self::assertContains( 'Mediacon_Unknown_Legacy_Class', $report['missing_classes'] );
		self::assertNotEmpty( $report['errors'] );
	}

	/** Existing types must never be replaced. */
	public function testExistingClassIsNotReplaced(): void {
		$registry = new LegacyContractRegistry();
		$bridge   = new LegacyClassBridge( $registry );
		self::assertFalse( $bridge->alias( ExistingLegacyType::class, CompatibilityModule::class ) );
		self::assertNotEmpty( $registry->report()['collisions'] );
	}

	/** A disabled module must not register or boot compatibility behavior. */
	public function testCompatibilityModuleCanBeDisabled(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules'       => array( 'mediation' ),
			'compatibility_enabled' => false,
		);
		$services = $this->services();
		$module   = new CompatibilityModule( $services['registry'] );
		$manager  = new ModuleManager( $services['container'], $services['hooks'], new SettingsManager() );
		$manager->add( $module );
		$manager->register();
		$manager->boot();
		self::assertFalse( $module->isActive() );
	}

	/** Existing saved module lists must gain compatibility without losing choices. */
	public function testExistingSettingsEnableCompatibilityNonDestructively(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'mediation', 'search' ),
		);
		$modules = ( new SettingsManager() )->get( 'enabled_modules', array() );
		self::assertSame( array( 'mediation', 'search', 'compatibility' ), $modules );
	}

	/** Diagnostics must identify exact Requires Plugins declarations. */
	public function testDiagnosticsFindsBridgeDependency(): void {
		$GLOBALS['mediacon_test_plugins']                   = array(
			'mediacon-legacy/legacy.php' => array(
				'Name'            => 'Mediacon Legacy',
				'RequiresPlugins' => 'mediacon-design-core',
			),
			'mediacon-design-core/mediacon-design-core.php' => array( 'Name' => 'Mediacon Design Core Compatibility Bridge' ),
		);
		$GLOBALS['mediacon_test_options']['active_plugins'] = array( 'mediacon-legacy/legacy.php' );
		$report = ( new LegacyDiagnostics( new LegacyContractRegistry() ) )->report();
		self::assertTrue( $report['bridge_installed'] );
		self::assertFalse( $report['bridge_active'] );
		self::assertTrue( $report['plugins']['mediacon-legacy/legacy.php']['requires_core'] );
		self::assertTrue( $report['plugins']['mediacon-legacy/legacy.php']['active'] );
	}

	/**
	 * Build isolated compatibility services.
	 *
	 * @return array{container:Container,hooks:HookManager,registry:LegacyContractRegistry,assets:LegacyAssetBridge}
	 */
	private function services(): array {
		$container = new Container();
		$hooks     = new HookManager();
		$registry  = new LegacyContractRegistry();
		$assets    = new LegacyAssetBridge( new AssetManager(), $registry );
		$templates = new LegacyTemplateBridge( new Template(), $registry );
		$container->instance( Container::class, $container );
		$container->instance( LegacyAssetBridge::class, $assets );
		$container->instance( LegacyTemplateBridge::class, $templates );
		$container->instance( LegacyFunctionBridge::class, new LegacyFunctionBridge( $assets, $templates, new SettingsManager(), $registry ) );
		$container->instance( \Mediacon\Enterprise\Compatibility\LegacyHookBridge::class, new \Mediacon\Enterprise\Compatibility\LegacyHookBridge( $registry ) );
		$diagnostics = new LegacyDiagnostics( $registry );
		$container->instance( LegacyDiagnostics::class, $diagnostics );
		$container->instance( CompatibilityAdminPage::class, new CompatibilityAdminPage( $diagnostics, new Template() ) );

		return compact( 'container', 'hooks', 'registry', 'assets' );
	}
}

/** Existing legacy class test double. */
final class ExistingLegacyType {}
