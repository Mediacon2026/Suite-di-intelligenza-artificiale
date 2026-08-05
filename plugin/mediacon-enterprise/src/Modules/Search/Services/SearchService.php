<?php
/**
 * Public search orchestration service.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

use Mediacon\Enterprise\Core\CacheManager;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Search\Support\SearchQuery;

defined( 'ABSPATH' ) || exit;

/**
 * Coordinates synonyms, repository, ranking, filters, pagination and cache.
 */
final readonly class SearchService {

	/**
	 * Create the search service.
	 *
	 * @param SearchRepository $repository Repository.
	 * @param RelevanceRanker  $ranker     Ranker.
	 * @param CacheManager     $cache      Core cache.
	 * @param SettingsManager  $settings   Core settings.
	 * @param ResultPaginator  $paginator  Result paginator.
	 */
	public function __construct(
		private SearchRepository $repository,
		private RelevanceRanker $ranker,
		private CacheManager $cache,
		private SettingsManager $settings,
		private ResultPaginator $paginator
	) {}

	/**
	 * Execute a bounded search.
	 *
	 * @param SearchQuery $query Validated query.
	 * @return array<string,mixed>
	 */
	public function search( SearchQuery $query ): array {
		$config = $this->config();
		if ( ! empty( $config['cache_enabled'] ) ) {
			$cached = $this->cache->get( 'search', $query->cacheParts() );
			if ( is_array( $cached ) ) {
				$cached['cached'] = true;
				return $cached;
			}
		}
		$synonyms = new SynonymMap( is_array( $config['synonyms'] ?? null ) ? $config['synonyms'] : array() );
		$terms    = $synonyms->expand( $query->text );
		$ranked   = $this->ranker->rank( $this->repository->find( $terms, (int) $config['max_candidates'] ), $terms, $query->text, (array) $config['priorities'] );
		$page     = $this->paginator->apply( $ranked, $query );
		$result   = array(
			'query'       => $query->text,
			'terms'       => $terms,
			'items'       => $page['items'],
			'total'       => $page['total'],
			'pages'       => $page['pages'],
			'page'        => $page['page'],
			'suggestions' => $this->suggestions( $terms, 0 === $page['total'] ),
			'cached'      => false,
		);
		if ( ! empty( $config['cache_enabled'] ) ) {
			$this->cache->set( 'search', $query->cacheParts(), $result, (int) $config['cache_ttl'] );
		}
		return $result;
	}

	/**
	 * Create a query from untrusted request values.
	 *
	 * @param array<string,mixed> $input Request values.
	 * @return SearchQuery
	 */
	public function queryFrom( array $input ): SearchQuery {
		$config = $this->config();
		$text   = sanitize_text_field( wp_unslash( (string) ( $input['q'] ?? '' ) ) );

		return new SearchQuery(
			$text,
			sanitize_key( (string) ( $input['type'] ?? 'all' ) ),
			sanitize_key( (string) ( $input['order'] ?? 'relevance' ) ),
			max( 0, absint( $input['year'] ?? 0 ) ),
			max( 1, absint( $input['search_page'] ?? 1 ) ),
			(int) $config['per_page'],
			(int) $config['min_chars'],
			(int) $config['max_chars']
		);
	}

	/**
	 * Build transparent related suggestions.
	 *
	 * @param array<string> $terms      Expanded terms.
	 * @param bool          $no_results Whether the result set is empty.
	 * @return array<array<string,string>>
	 */
	private function suggestions( array $terms, bool $no_results ): array {
		$suggestions = array();
		$cost_terms  = array( 'costi', 'tariffe', 'indennità', 'preventivo' );
		$preventivo  = $this->settings->get( 'preventivo', array() );
		$enabled     = $this->settings->get( 'enabled_modules', array() );
		if ( array_intersect( $terms, $cost_terms ) && is_array( $preventivo ) && ! empty( $preventivo['frontend_enabled'] ) && in_array( 'preventivo', (array) $enabled, true ) ) {
			$page_id = absint( $preventivo['page_id'] ?? 0 );
			if ( 0 < $page_id ) {
				$suggestions[] = array(
					'label' => 'Calcola un preventivo di mediazione',
					'url'   => (string) get_permalink( $page_id ),
				);
			}
		}
		if ( $no_results && array() === $suggestions ) {
			$suggestions[] = array(
				'label' => 'Prova un sinonimo o riduci i filtri',
				'url'   => '',
			);
		}
		return $suggestions;
	}

	/**
	 * Return normalized search configuration.
	 *
	 * @return array<string,mixed>
	 */
	private function config(): array {
		$value = $this->settings->get( 'search', array() );
		return is_array( $value ) ? $value : array();
	}
}
