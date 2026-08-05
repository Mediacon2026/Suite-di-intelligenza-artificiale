<?php
/**
 * Cross-module audit regression tests.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\Container;
use Mediacon\Enterprise\Core\CacheManager;
use Mediacon\Enterprise\Core\HookManager;
use Mediacon\Enterprise\Core\ModuleManager;
use Mediacon\Enterprise\Core\RateLimiter;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Helpers\Template;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Services\TeacherRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content as FormationContent;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog as FormationPages;
use Mediacon\Enterprise\Modules\Mediation\Support\Content as MediationContent;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog as MediationPages;
use Mediacon\Enterprise\Modules\Preventivo\Domain\DisputeValue;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Money;
use Mediacon\Enterprise\Modules\Preventivo\Support\QuoteRequestFactory;
use Mediacon\Enterprise\Modules\Search\Services\SearchRepository;
use Mediacon\Enterprise\Modules\Search\Support\Highlighter;
use PHPUnit\Framework\TestCase;

/**
 * Protects fixes found during the full plugin audit.
 */
final class AuditRegressionTest extends TestCase {

	/** Reset persistent test doubles. */
	protected function setUp(): void {
		$GLOBALS['mediacon_test_options']    = array();
		$GLOBALS['mediacon_test_transients'] = array();
	}

	/** New installations and runtime defaults must use one module list. */
	public function testDefaultModuleListIsCentralized(): void {
		$manager = new ModuleManager( new Container(), new HookManager(), new SettingsManager() );
		foreach ( SettingsManager::DEFAULT_MODULES as $module_id ) {
			$manager->add( new AuditModule( $module_id ) );
		}

		self::assertSame( SettingsManager::DEFAULT_MODULES, array_keys( $manager->enabled() ) );
	}

	/** Teacher listings must never issue an unbounded query. */
	public function testTeacherQueryIsBounded(): void {
		$query = ( new TeacherRepository( new SettingsManager() ) )->query();

		self::assertSame( 100, $query->args['posts_per_page'] );
		self::assertTrue( $query->args['no_found_rows'] );
	}

	/** FAQ candidates must respect the global repository limit. */
	public function testSearchCandidatesRemainBoundedWithVirtualFaqs(): void {
		$settings   = new SettingsManager();
		$repository = new SearchRepository(
			$settings,
			new MediationPages( $settings ),
			new FormationPages( $settings ),
			new MediationContent(),
			new FormationContent(),
			new CardPresenter( $settings ),
			new CourseRepository( $settings )
		);

		self::assertCount( 1, $repository->find( array( 'mediazione' ), 1 ) );
	}

	/** Public endpoint limits must be shared and scope-isolated. */
	public function testCoreRateLimiterIsBoundedAndScoped(): void {
		$limiter = new RateLimiter( new CacheManager() );

		self::assertTrue( $limiter->allow( 'search', 'client', 1 ) );
		self::assertFalse( $limiter->allow( 'search', 'client', 1 ) );
		self::assertTrue( $limiter->allow( 'preventivo', 'client', 1 ) );
	}

	/** Highlighting must not process markup inserted by an earlier term. */
	public function testHighlighterProcessesAllTermsInOnePass(): void {
		$result = ( new Highlighter() )->highlight( 'Mediazione mark', array( 'mediazione', 'mark' ) );

		self::assertSame( '<mark>Mediazione</mark> <mark>mark</mark>', $result );
	}

	/** Malformed arrays cannot produce an empty quote. */
	public function testQuoteFactoryRejectsRowsWithoutValidParties(): void {
		$this->expectException( InvalidArgumentException::class );

		( new QuoteRequestFactory() )->create( array( 'parties' => array( 'invalid' ) ) );
	}

	/** A quote requires at least one claimant. */
	public function testQuoteFactoryRejectsRequestsWithoutClaimant(): void {
		$this->expectException( InvalidArgumentException::class );

		( new QuoteRequestFactory() )->create(
			array(
				'parties' => array(
					array(
						'id'       => 'invited-1',
						'name'     => 'Invitata',
						'role'     => 'invited',
						'status'   => 'present',
						'centers'  => array( 'Centro 1' ),
						'expenses' => array(),
						'paid'     => 0,
					),
				),
			)
		);
	}

	/** Non-finite public numeric values must be rejected. */
	public function testNonFiniteDomainValuesAreRejected(): void {
		try {
			new DisputeValue( INF );
			self::fail( 'Infinite dispute value was accepted.' );
		} catch ( InvalidArgumentException ) {
			self::assertTrue( true );
		}

		$this->expectException( InvalidArgumentException::class );
		Money::euros( INF );
	}

	/** Absolute template paths outside the plugin root must be rejected. */
	public function testTemplateRendererRejectsOutsideFile(): void {
		$this->expectException( \RuntimeException::class );

		( new Template() )->renderFile( __FILE__ . '.outside' );
	}
}

/** Minimal auditable module double. */
final class AuditModule implements \Mediacon\Enterprise\Core\Module {
	/** @param string $id Module identifier. */
	public function __construct( private readonly string $id ) {}

	/** @return string */
	public function id(): string {
		return $this->id;
	}

	/** @param Container $container Container. @param HookManager $hooks Hooks. @return void */
	public function register( Container $container, HookManager $hooks ): void {}

	/** @return void */
	public function boot(): void {}
}
