<?php
/**
 * Lightweight autocomplete rate limiter.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

use Mediacon\Enterprise\Core\CacheManager;

defined( 'ABSPATH' ) || exit;

/**
 * Limits repeated requests per anonymized client key.
 */
final readonly class RateLimiter {

	/**
	 * Create the rate limiter.
	 *
	 * @param CacheManager $cache Core cache.
	 */
	public function __construct( private CacheManager $cache ) {}

	/**
	 * Consume one request from the current minute.
	 *
	 * @param string $client Client identifier.
	 * @param int    $limit  Requests per minute.
	 * @return bool
	 */
	public function allow( string $client, int $limit ): bool {
		$parts = array(
			'client' => hash( 'sha256', $client ),
			'minute' => gmdate( 'YmdHi' ),
		);
		$count = (int) ( $this->cache->get( 'search_rate', $parts ) ?? 0 );
		if ( $count >= $limit ) {
			return false;
		}
		$this->cache->set( 'search_rate', $parts, $count + 1, 70 );
		return true;
	}
}
