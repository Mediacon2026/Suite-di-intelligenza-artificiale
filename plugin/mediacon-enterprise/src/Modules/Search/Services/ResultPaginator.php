<?php
/**
 * Search result filtering and pagination.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

use Mediacon\Enterprise\Modules\Search\Support\SearchQuery;

defined( 'ABSPATH' ) || exit;

/**
 * Applies transparent filters and bounded pagination.
 */
final class ResultPaginator {

	/**
	 * Filter, sort and paginate ranked documents.
	 *
	 * @param array<array<string,mixed>> $ranked Ranked documents.
	 * @param SearchQuery                $query  Search query.
	 * @return array{items:array<array<string,mixed>>,total:int,pages:int,page:int}
	 */
	public function apply( array $ranked, SearchQuery $query ): array {
		$ranked = array_values( array_filter( $ranked, fn ( array $item ): bool => $this->matches( $item, $query ) ) );
		if ( 'date' === $query->sort ) {
			usort( $ranked, static fn ( array $left, array $right ): int => strcmp( (string) ( $right['date'] ?? '' ), (string) ( $left['date'] ?? '' ) ) );
		}
		$total = count( $ranked );

		return array(
			'items' => array_slice( $ranked, ( $query->page - 1 ) * $query->per_page, $query->per_page ),
			'total' => $total,
			'pages' => max( 1, (int) ceil( $total / $query->per_page ) ),
			'page'  => $query->page,
		);
	}

	/**
	 * Determine whether an item matches filters.
	 *
	 * @param array<string,mixed> $item  Search item.
	 * @param SearchQuery         $query Search query.
	 * @return bool
	 */
	private function matches( array $item, SearchQuery $query ): bool {
		$type         = (string) ( $item['type'] ?? '' );
		$filter_match = match ( $query->filter ) {
			'all'       => true,
			'formation' => in_array( $type, array( 'formation', 'courses', 'teachers' ), true ),
			default     => $type === $query->filter,
		};

		return $filter_match && ( 0 === $query->year || (int) ( $item['year'] ?? 0 ) === $query->year );
	}
}
