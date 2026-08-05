<?php
/**
 * Editorial WordPress fallback test.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Editorial\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use PHPUnit\Framework\TestCase;

/** Ensures inactive editorial templates preserve the theme template. */
final class EditorialTemplateFallbackTest extends TestCase {

	/** The original file must remain unchanged while archive templates are disabled. */
	public function testOriginalTemplateIsReturnedWhenEditorialTemplateIsDisabled(): void {
		$GLOBALS['mediacon_test_is_home'] = true;
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'editorial' ),
			'editorial'       => array(
				'templates' => array( 'blog' => false ),
				'general'   => array( 'single_template' => false ),
			),
		);
		$settings   = new SettingsManager();
		$catalog    = new ArchiveCatalog( $settings );
		$cards      = new CardPresenter( $settings );
		$controller = new TemplateController(
			$catalog,
			new EditorialRepository( $catalog, $cards ),
			$cards,
			new CourseRepository( $settings ),
			new TeacherRepository( $settings ),
			new Template()
		);

		self::assertSame( '/theme/archive.php', $controller->filterTemplate( '/theme/archive.php' ) );
	}
}
