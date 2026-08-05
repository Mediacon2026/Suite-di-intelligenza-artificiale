<?php
/**
 * Formation template fallback tests.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Formation\Controllers\TemplateController;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog;
use PHPUnit\Framework\TestCase;

/** Ensures disabled formation templates preserve the active theme output. */
final class FormationTemplateFallbackTest extends TestCase {

	/** A linked page with its formation template disabled must use the theme file. */
	public function testOriginalTemplateIsReturnedWhenFormationTemplateIsDisabled(): void {
		$GLOBALS['mediacon_test_is_page'] = true;
		$GLOBALS['mediacon_test_page_id'] = 84;
		$GLOBALS['mediacon_test_pages']   = array(
			'formazione-mediatori' => new \WP_Post( 84 ),
		);
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'formation' ),
			'formation'       => array(
				'pages'     => array(),
				'templates' => array( 'formation' => false ),
				'general'   => array(
					'detail_template'         => false,
					'teacher_detail_template' => false,
				),
			),
		);

		$settings   = new SettingsManager();
		$controller = new TemplateController(
			new PageCatalog( $settings ),
			new Content(),
			new CourseRepository( $settings ),
			new TeacherRepository( $settings ),
			$settings,
			new Template()
		);

		self::assertSame( '/theme/page.php', $controller->filterTemplate( '/theme/page.php' ) );
	}
}
