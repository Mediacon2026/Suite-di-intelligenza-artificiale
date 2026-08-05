<?php
/**
 * Template fallback tests.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Mediation\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Mediation\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Mediation\Support\Content;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog;
use PHPUnit\Framework\TestCase;

/** Ensures the active theme template is preserved when no module template is active. */
final class TemplateFallbackTest extends TestCase {

	/** The original theme template must be returned untouched. */
	public function testOriginalTemplateIsReturnedWhenModulePageIsInactive(): void {
		$GLOBALS['mediacon_test_is_page'] = true;
		$GLOBALS['mediacon_test_page_id'] = 42;
		$GLOBALS['mediacon_test_pages']   = array(
			'come-funziona-la-mediazione' => new \WP_Post( 42 ),
		);
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'mediation' ),
			'mediation'       => array(
				'pages'     => array(),
				'templates' => array( 'how-it-works' => false ),
			),
		);
		$controller = new TemplateController(
			new PageCatalog( new SettingsManager() ),
			new Content(),
			new EditorialRepository(),
			new Template()
		);

		self::assertSame( '/theme/page.php', $controller->filterTemplate( '/theme/page.php' ) );
	}
}
