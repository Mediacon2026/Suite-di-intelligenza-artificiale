<?php
/**
 * Validated search query.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Support;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/**
 * Holds shareable public search parameters.
 */
final readonly class SearchQuery {

	public const FILTERS = array( 'all', 'mediation', 'formation', 'blog', 'jurisprudence', 'legislation', 'insights', 'courses', 'faq' );

	/**
	 * Create a validated query.
	 *
	 * @param string $text       Search text.
	 * @param string $filter     Content filter.
	 * @param string $sort       Sort mode.
	 * @param int    $year       Optional year.
	 * @param int    $page       Current page.
	 * @param int    $per_page   Results per page.
	 * @param int    $min_chars  Minimum text length.
	 * @param int    $max_chars  Maximum text length.
	 * @throws InvalidArgumentException When parameters are outside configured bounds.
	 */
	public function __construct(
		public string $text,
		public string $filter,
		public string $sort,
		public int $year,
		public int $page,
		public int $per_page,
		int $min_chars,
		int $max_chars
	) {
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
		if ( $length < $min_chars || $length > $max_chars ) {
			throw new InvalidArgumentException( 'Search query length is outside the allowed range.' );
		}
		if ( ! in_array( $filter, self::FILTERS, true ) || ! in_array( $sort, array( 'relevance', 'date' ), true ) ) {
			throw new InvalidArgumentException( 'Unsupported search filter or order.' );
		}
	}

	/**
	 * Return stable cache-key data.
	 *
	 * @return array<string,mixed>
	 */
	public function cacheParts(): array {
		return array(
			'q'        => $this->text,
			'filter'   => $this->filter,
			'sort'     => $this->sort,
			'year'     => $this->year,
			'page'     => $this->page,
			'per_page' => $this->per_page,
		);
	}
}
