<?php
/**
 * Public Search module tests.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\CacheManager;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Editorial\Services\CardPresenter;
use Mediacon\Enterprise\Modules\Formation\Services\CourseRepository;
use Mediacon\Enterprise\Modules\Formation\Support\Content as FormationContent;
use Mediacon\Enterprise\Modules\Formation\Support\PageCatalog as FormationPages;
use Mediacon\Enterprise\Modules\Mediation\Support\Content as MediationContent;
use Mediacon\Enterprise\Modules\Mediation\Support\PageCatalog as MediationPages;
use Mediacon\Enterprise\Modules\Search\Services\RelevanceRanker;
use Mediacon\Enterprise\Modules\Search\Services\ResultPaginator;
use Mediacon\Enterprise\Modules\Search\Services\SearchRepository;
use Mediacon\Enterprise\Modules\Search\Services\SuggestionLimiter;
use Mediacon\Enterprise\Modules\Search\Services\SynonymMap;
use Mediacon\Enterprise\Modules\Search\Support\SearchQuery;
use PHPUnit\Framework\TestCase;

/**
 * Verifies deterministic search behavior without an external index.
 */
final class SearchModuleTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['mediacon_test_options']    = array();
		$GLOBALS['mediacon_test_transients'] = array();
	}

	/**
	 * @dataProvider contentProvider
	 */
	public function testSearchableContentTypes( string $type ): void {
		$ranked = ( new RelevanceRanker() )->rank( array( $this->document( $type, 'Mediazione pratica' ) ), array( 'mediazione' ), 'mediazione', array() );
		self::assertCount( 1, $ranked );
		self::assertSame( $type, $ranked[0]['type'] );
	}

	/** @return array<string,array{string}> */
	public static function contentProvider(): array {
		return array(
			'pages' => array( 'pages' ), 'blog' => array( 'blog' ), 'sentences' => array( 'jurisprudence' ),
			'legislation' => array( 'legislation' ), 'courses' => array( 'courses' ), 'faq' => array( 'faq' ),
		);
	}

	public function testPrivateContentIsExcluded(): void {
		$document           = $this->document( 'pages', 'Mediazione' );
		$document['status'] = 'private';
		self::assertSame( array(), ( new RelevanceRanker() )->rank( array( $document ), array( 'mediazione' ), 'mediazione', array() ) );
	}

	public function testExactTitleHasHighestRanking(): void {
		$exact = $this->document( 'pages', 'Mediazione' );
		$body  = $this->document( 'blog', 'Notizia', 'Approfondimento sulla mediazione' );
		$ranked = ( new RelevanceRanker() )->rank( array( $body, $exact ), array( 'mediazione' ), 'mediazione', array() );
		self::assertSame( 'Mediazione', $ranked[0]['title'] );
		self::assertGreaterThan( $ranked[1]['score'], $ranked[0]['score'] );
	}

	public function testConfiguredSynonymsExpandKnownTerm(): void {
		$map = new SynonymMap( array( array( 'costi', 'tariffe', 'indennità', 'preventivo' ) ) );
		$terms = $map->expand( 'costi' );
		self::assertContains( 'tariffe', $terms );
		self::assertContains( 'preventivo', $terms );
	}

	public function testFiltersAndYearAreApplied(): void {
		$items = array( $this->document( 'blog', 'Uno', '', 2025 ), $this->document( 'courses', 'Due', '', 2026 ), $this->document( 'formation', 'Tre', '', 2026 ) );
		$query = new SearchQuery( 'corso', 'formation', 'relevance', 2026, 1, 10, 2, 80 );
		$result = ( new ResultPaginator() )->apply( $items, $query );
		self::assertSame( 2, $result['total'] );
	}

	public function testPaginationIsBounded(): void {
		$items = array();
		for ( $index = 1; $index <= 12; ++$index ) {
			$items[] = $this->document( 'blog', 'Elemento ' . $index );
		}
		$query = new SearchQuery( 'elemento', 'all', 'relevance', 0, 2, 5, 2, 80 );
		$result = ( new ResultPaginator() )->apply( $items, $query );
		self::assertCount( 5, $result['items'] );
		self::assertSame( 3, $result['pages'] );
	}

	public function testCacheCanBeInvalidatedByNamespace(): void {
		$cache = new CacheManager();
		$cache->set( 'search', array( 'q' => 'mediazione' ), array( 'value' => 1 ), 60 );
		self::assertSame( array( 'value' => 1 ), $cache->get( 'search', array( 'q' => 'mediazione' ) ) );
		$cache->invalidate( 'search' );
		self::assertNull( $cache->get( 'search', array( 'q' => 'mediazione' ) ) );
	}

	public function testRepositoryAlwaysQueriesPublishedBoundedContent(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array( 'search' => array( 'excluded_ids' => array( 7 ) ) );
		$settings   = new SettingsManager();
		$repository = new SearchRepository( $settings, new MediationPages( $settings ), new FormationPages( $settings ), new MediationContent(), new FormationContent(), new CardPresenter( $settings ), new CourseRepository( $settings ) );
		$args       = $repository->buildQueryArgs( 'mediazione', 500 );
		self::assertSame( 'publish', $args['post_status'] );
		self::assertSame( 50, $args['posts_per_page'] );
		self::assertSame( array( 7 ), $args['post__not_in'] );
	}

	public function testQueryLengthLimitsAreEnforced(): void {
		$this->expectException( InvalidArgumentException::class );
		new SearchQuery( 'a', 'all', 'relevance', 0, 1, 10, 2, 80 );
	}

	public function testAutocompleteNeverExceedsTenItems(): void {
		$items = array_fill( 0, 20, $this->document( 'pages', 'Mediazione' ) );
		self::assertCount( 10, ( new SuggestionLimiter() )->limit( $items, 20 ) );
	}

	/** @return array<string,mixed> */
	private function document( string $type, string $title, string $content = '', int $year = 2026 ): array {
		return array(
			'id' => $title . $type, 'status' => 'publish', 'type' => $type, 'title' => $title,
			'excerpt' => $content, 'content' => $content, 'taxonomy' => $type, 'date' => $year . '-01-01', 'year' => $year,
		);
	}
}
