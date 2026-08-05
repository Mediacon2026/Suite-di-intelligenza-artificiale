<?php
/**
 * Module manager behavior tests.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Module;
use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\SettingsManager;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that disabled modules do not alter WordPress behavior.
 */
final class ModuleManagerTest extends TestCase {

	/** Reset shared option state. */
	protected function setUp(): void {
		$GLOBALS['mediacon_test_options'] = array();
	}

	/** Disabled modules must not register or boot. */
	public function testDisabledModuleIsInert(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'mediation' ),
		);
		$module  = new TestModule( 'formation' );
		$manager = new ModuleManager( new Container(), new HookManager(), new SettingsManager() );
		$manager->add( $module );
		$manager->register();
		$manager->boot();

		self::assertSame( 0, $module->registrations );
		self::assertSame( 0, $module->boots );
	}

	/** Enabled modules must register and boot once. */
	public function testEnabledModuleRegistersAndBoots(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'mediation' ),
		);
		$module  = new TestModule( 'mediation' );
		$manager = new ModuleManager( new Container(), new HookManager(), new SettingsManager() );
		$manager->add( $module );
		$manager->register();
		$manager->boot();

		self::assertSame( 1, $module->registrations );
		self::assertSame( 1, $module->boots );
	}

	/** The Editorial module must remain inert when excluded from enabled modules. */
	public function testDisabledEditorialModuleIsInert(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'mediation', 'formation' ),
		);
		$module  = new TestModule( 'editorial' );
		$manager = new ModuleManager( new Container(), new HookManager(), new SettingsManager() );
		$manager->add( $module );
		$manager->register();
		$manager->boot();

		self::assertSame( 0, $module->registrations );
		self::assertSame( 0, $module->boots );
	}

	/** The Search module must remain inert when excluded from enabled modules. */
	public function testDisabledSearchModuleIsInert(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'mediation', 'formation', 'editorial' ),
		);
		$module  = new TestModule( 'search' );
		$manager = new ModuleManager( new Container(), new HookManager(), new SettingsManager() );
		$manager->add( $module );
		$manager->register();
		$manager->boot();

		self::assertSame( 0, $module->registrations );
		self::assertSame( 0, $module->boots );
	}
}

/** Test module double. */
final class TestModule implements Module {
	/** @param string $module_id Module identifier. */
	public function __construct( private readonly string $module_id ) {}

	/** Registration count. @var int */
	public int $registrations = 0;

	/** Boot count. @var int */
	public int $boots = 0;

	/** @return string */
	public function id(): string {
		return $this->module_id;
	}

	/** @param Container $container Container. @param HookManager $hooks Hooks. @return void */
	public function register( Container $container, HookManager $hooks ): void {
		++$this->registrations;
	}

	/** @return void */
	public function boot(): void {
		++$this->boots;
	}
}
