<?php
/**
 * Lightweight public endpoint rate limiter.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Limits repeated requests per anonymized client and endpoint scope.
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
	 * @param string $scope  Endpoint scope.
	 * @param string $client Client identifier.
	 * @param int    $limit  Requests per minute.
	 * @return bool
	 */
	public function allow( string $scope, string $client, int $limit ): bool {
		$parts = array(
			'client' => hash( 'sha256', $client ),
			'minute' => gmdate( 'YmdHi' ),
		);
		$count = (int) ( $this->cache->get( sanitize_key( $scope ) . '_rate', $parts ) ?? 0 );
		if ( $count >= max( 1, $limit ) ) {
			return false;
		}
		$this->cache->set( sanitize_key( $scope ) . '_rate', $parts, $count + 1, 70 );
		return true;
	}
}
