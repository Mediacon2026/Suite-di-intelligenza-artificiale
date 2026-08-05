<?php
/**
 * Autocomplete suggestion limiter.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Enforces the absolute ten-suggestion limit.
 */
final class SuggestionLimiter {

	/**
	 * Limit result items.
	 *
	 * @param array<array<string,mixed>> $items Requested items.
	 * @param int                        $limit Configured limit.
	 * @return array<array<string,mixed>>
	 */
	public function limit( array $items, int $limit ): array {
		return array_slice( $items, 0, min( 10, max( 1, $limit ) ) );
	}
}
