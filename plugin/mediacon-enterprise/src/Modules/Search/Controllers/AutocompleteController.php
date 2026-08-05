<?php
/**
 * Search autocomplete REST controller.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Controllers;

use InvalidArgumentException;
use Mediacon\Enterprise\Core\RateLimiter;
use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Search\Services\SearchService;
use Mediacon\Enterprise\Modules\Search\Services\SuggestionLimiter;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * Returns at most ten accessible autocomplete suggestions.
 */
final readonly class AutocompleteController {

	/**
	 * Create the autocomplete controller.
	 *
	 * @param SearchService     $search      Search service.
	 * @param SettingsManager   $settings    Core settings.
	 * @param RateLimiter       $limiter     Rate limiter.
	 * @param SuggestionLimiter $suggestions Suggestion limiter.
	 */
	public function __construct( private SearchService $search, private SettingsManager $settings, private RateLimiter $limiter, private SuggestionLimiter $suggestions ) {}

	/**
	 * Validate the public REST nonce before dispatching autocomplete.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|WP_Error
	 */
	public function permissions( WP_REST_Request $request ): bool|WP_Error {
		return wp_verify_nonce( (string) $request->get_header( 'X-WP-Nonce' ), 'wp_rest' )
			? true
			: new WP_Error( 'mediacon_search_nonce', __( 'Sessione non valida.', 'mediacon-enterprise' ), array( 'status' => 403 ) );
	}

	/**
	 * Return bounded public suggestions.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return array<string,mixed>|WP_Error
	 */
	public function suggest( WP_REST_Request $request ): array|WP_Error {
		$permission = $this->permissions( $request );
		if ( true !== $permission ) {
			return $permission;
		}
		$config = $this->settings->get( 'search', array() );
		$config = is_array( $config ) ? $config : array();
		$client = (string) ( $_SERVER['REMOTE_ADDR'] ?? 'guest' );
		if ( ! $this->limiter->allow( 'search', $client, (int) ( $config['rate_limit'] ?? 30 ) ) ) {
			return new WP_Error( 'mediacon_search_rate', __( 'Troppe richieste. Riprova tra poco.', 'mediacon-enterprise' ), array( 'status' => 429 ) );
		}
		try {
			$input                = (array) $request->get_json_params();
			$input['type']        = 'all';
			$input['order']       = 'relevance';
			$input['search_page'] = 1;
			$result               = $this->search->search( $this->search->queryFrom( $input ) );
			$limit                = min( 10, (int) ( $config['suggestion_limit'] ?? 10 ) );
			$items                = $this->suggestions->limit( $result['items'], $limit );
			return array(
				'items' => array_map(
					static fn ( array $item ): array => array(
						'title' => (string) $item['title'],
						'url'   => (string) $item['url'],
						'type'  => (string) $item['label'],
					),
					$items
				),
				'total' => count( $items ),
			);
		} catch ( InvalidArgumentException $exception ) {
			return new WP_Error( 'mediacon_search_query', __( 'Query non valida.', 'mediacon-enterprise' ), array( 'status' => 400 ) );
		}
	}
}
