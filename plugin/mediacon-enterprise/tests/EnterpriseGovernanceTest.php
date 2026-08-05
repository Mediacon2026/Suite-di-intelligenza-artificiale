<?php
/**
 * Enterprise 1.0 governance regression tests.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Enterprise\PageGovernance;
use PHPUnit\Framework\TestCase;

/** Verifies immediate reversible ownership and module settings. */
final class EnterpriseGovernanceTest extends TestCase {

	/** Reset isolated WordPress options. */
	protected function setUp(): void {
		$GLOBALS['mediacon_test_options'] = array();
	}

	/** All three ownership modes round-trip through the sanitized option. */
	public function testOwnershipModesAreImmediateAndReversible(): void {
		$settings   = new SettingsManager();
		$governance = new PageGovernance( $settings );

		self::assertTrue( $governance->setMode( 'mediation', 'costs', PageGovernance::ENTERPRISE ) );
		self::assertSame( PageGovernance::ENTERPRISE, $governance->mode( 'mediation', 'costs' ) );
		self::assertTrue( $settings->get( 'mediation' )['templates']['costs'] );

		self::assertTrue( $governance->setMode( 'mediation', 'costs', PageGovernance::LEGACY ) );
		self::assertSame( PageGovernance::LEGACY, $governance->mode( 'mediation', 'costs' ) );
		self::assertFalse( $settings->get( 'mediation' )['templates']['costs'] );

		self::assertTrue( $governance->setMode( 'mediation', 'costs', PageGovernance::WORDPRESS ) );
		self::assertSame( PageGovernance::WORDPRESS, $governance->mode( 'mediation', 'costs' ) );
	}

	/** Invalid resources cannot alter the settings option. */
	public function testInvalidOwnershipRequestIsRejected(): void {
		$governance = new PageGovernance( new SettingsManager() );
		self::assertFalse( $governance->setMode( 'mediation', 'unknown', PageGovernance::ENTERPRISE ) );
		self::assertSame( array(), $governance->allModes() );
	}

	/** Editorial single ownership remains compatible with its historic setting. */
	public function testEditorialSingleSynchronizesHistoricFlag(): void {
		$settings   = new SettingsManager();
		$governance = new PageGovernance( $settings );
		$governance->setMode( 'editorial', 'single', PageGovernance::ENTERPRISE );
		self::assertTrue( $settings->get( 'editorial' )['general']['single_template'] );
		$governance->setMode( 'editorial', 'single', PageGovernance::WORDPRESS );
		self::assertFalse( $settings->get( 'editorial' )['general']['single_template'] );
	}
}
