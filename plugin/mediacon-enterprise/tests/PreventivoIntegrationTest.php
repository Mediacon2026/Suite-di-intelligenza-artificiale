<?php
/**
 * Preventivo integration tests.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Preventivo\Controllers\TemplateController;
use PHPUnit\Framework\TestCase;

/** Verifies frontend fallback and printable layout contracts. */
final class PreventivoIntegrationTest extends TestCase {

	protected function tearDown(): void {
		$GLOBALS['mediacon_test_is_page'] = false;
		$GLOBALS['mediacon_test_page_id'] = 0;
	}

	public function testFrontendDisabledPreservesWordPressTemplate(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array( 'preventivo' => array( 'frontend_enabled' => false, 'page_id' => 42 ) );
		$GLOBALS['mediacon_test_is_page'] = true;
		$GLOBALS['mediacon_test_page_id'] = 42;
		$controller = new TemplateController( new SettingsManager() );
		self::assertSame( 'theme.php', $controller->filterTemplate( 'theme.php' ) );
	}

	public function testOtherPagePreservesWordPressTemplate(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array( 'preventivo' => array( 'frontend_enabled' => true, 'page_id' => 42 ) );
		$GLOBALS['mediacon_test_is_page'] = true;
		$GLOBALS['mediacon_test_page_id'] = 41;
		$controller = new TemplateController( new SettingsManager() );
		self::assertSame( 'theme.php', $controller->filterTemplate( 'theme.php' ) );
	}

	public function testCalculatorShortcodeIsRegistered(): void {
		$controller = new TemplateController( new SettingsManager() );
		$controller->registerShortcode();
		self::assertArrayHasKey( 'mediacon_calcolatore', $GLOBALS['mediacon_test_shortcodes'] );
		self::assertSame( array( $controller, 'renderShortcode' ), $GLOBALS['mediacon_test_shortcodes']['mediacon_calcolatore'] );
	}

	public function testPrintStylesTargetA4AndResultOnly(): void {
		$css = file_get_contents( dirname( __DIR__ ) . '/src/Modules/Preventivo/Assets/css/preventivo.css' );
		self::assertStringContainsString( '@page{size:A4', $css );
		self::assertStringContainsString( 'body *{visibility:hidden', $css );
		self::assertStringContainsString( '.mce-preventivo__result', $css );
	}
}
