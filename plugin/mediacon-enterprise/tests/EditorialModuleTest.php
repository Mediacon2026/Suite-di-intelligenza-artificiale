<?php
/**
 * Editorial module integration tests.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Tests;

use Mediacon\Enterprise\Assets\AssetManager;
use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\Router;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\EditorialModule;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Editorial\Services\EditorialRepository;
use Mediacon\Enterprise\Modules\Editorial\Support\ArchiveCatalog;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use PHPUnit\Framework\TestCase;

/** Verifies card, filter, fallback, and Formation integration contracts. */
final class EditorialModuleTest extends TestCase {

	/** Reset shared WordPress state. */
	protected function setUp(): void {
		$GLOBALS['mediacon_test_options']   = array();
		$GLOBALS['mediacon_test_is_home']   = false;
		$GLOBALS['mediacon_test_is_search'] = false;
	}

	/** Card text and CSS must enforce uniform two-line presentation. */
	public function testUniformCardContract(): void {
		$presenter = new CardPresenter( new SettingsManager() );
		self::assertSame( '12345…', $presenter->truncate( '123456789', 5 ) );
		$css = file_get_contents( dirname( __DIR__ ) . '/src/Modules/Editorial/Assets/css/editorial.css' );
		self::assertIsString( $css );
		self::assertStringContainsString( '-webkit-line-clamp: 2', $css );
		self::assertStringContainsString( 'object-fit: cover', $css );
		self::assertStringContainsString( 'height: 100%', $css );
	}

	/** Filters and pagination must be represented by standard WP_Query arguments. */
	public function testFilterAndPaginationArguments(): void {
		$settings   = new SettingsManager();
		$catalog    = new ArchiveCatalog( $settings );
		$repository = new EditorialRepository( $catalog, new CardPresenter( $settings ) );
		$args       = $repository->buildQueryArgs(
			'blog',
			array(
				'search'   => 'mediazione',
				'category' => 7,
				'year'     => 2026,
				'topic'    => 4,
				'order'    => 'oldest',
				'type'     => '',
				'page'     => 3,
			)
		);

		self::assertSame( 'mediazione', $args['s'] );
		self::assertSame( 7, $args['cat'] );
		self::assertSame( 2026, $args['year'] );
		self::assertSame( 4, $args['tag_id'] );
		self::assertSame( 3, $args['paged'] );
		self::assertSame( 'ASC', $args['order'] );
	}

	/** The Editorial module must reuse an existing Formation repository instance. */
	public function testFormationRepositoryIsReused(): void {
		$container = new Container();
		$settings  = new SettingsManager();
		$courses   = new CourseRepository( $settings );
		$container->instance( SettingsManager::class, $settings );
		$container->instance( CourseRepository::class, $courses );
		$container->instance( TeacherRepository::class, new TeacherRepository( $settings ) );
		$container->instance( AssetManager::class, new AssetManager() );
		$container->instance( Template::class, new Template() );
		$container->instance( Router::class, new Router() );

		( new EditorialModule() )->register( $container, new HookManager() );

		self::assertSame( $courses, $container->get( CourseRepository::class ) );
	}
}
