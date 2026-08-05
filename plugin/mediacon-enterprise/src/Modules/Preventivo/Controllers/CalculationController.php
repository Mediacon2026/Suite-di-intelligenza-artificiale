<?php
/**
 * Public calculation endpoint.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Controllers;

use InvalidArgumentException;
use Mediacon\Enterprise\Core\RateLimiter;
use Mediacon\Enterprise\Modules\Preventivo\Services\QuoteCalculator;
use Mediacon\Enterprise\Modules\Preventivo\Services\SimulationNumber;
use Mediacon\Enterprise\Modules\Preventivo\Support\QuoteRequestFactory;
use Throwable;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/** Validates REST requests and delegates all economics to the domain service. */
final readonly class CalculationController {

	/**
	 * Create the public calculation controller.
	 *
	 * @param QuoteRequestFactory $factory    Request factory.
	 * @param QuoteCalculator     $calculator Quote calculator.
	 * @param SimulationNumber    $numbers    Numbering service.
	 * @param RateLimiter         $limiter    Public endpoint limiter.
	 */
	public function __construct( private QuoteRequestFactory $factory, private QuoteCalculator $calculator, private SimulationNumber $numbers, private RateLimiter $limiter ) {}

	/**
	 * Validate the public REST nonce before dispatching the calculation.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return bool|WP_Error
	 */
	public function permissions( WP_REST_Request $request ): bool|WP_Error {
		return wp_verify_nonce( (string) $request->get_header( 'X-WP-Nonce' ), 'wp_rest' )
			? true
			: new WP_Error( 'mediacon_invalid_nonce', __( 'Sessione non valida. Ricarica la pagina.', 'mediacon-enterprise' ), array( 'status' => 403 ) );
	}

	/**
	 * Validate and calculate a public quote.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return array<string,mixed>|WP_Error
	 */
	public function calculate( WP_REST_Request $request ): array|WP_Error {
		$permission = $this->permissions( $request );
		if ( true !== $permission ) {
			return $permission;
		}
		$client = (string) ( $_SERVER['REMOTE_ADDR'] ?? 'guest' );
		if ( ! $this->limiter->allow( 'preventivo', $client, 30 ) ) {
			return new WP_Error( 'mediacon_quote_rate', __( 'Troppe richieste. Riprova tra poco.', 'mediacon-enterprise' ), array( 'status' => 429 ) );
		}
		try {
			$data                  = $this->factory->create( (array) $request->get_json_params() );
			$summary               = $this->calculator->calculate( $data['value'], $data['type'], $data['scenario'], $data['parties'], $data['increase'] )->toArray();
			$summary['simulation'] = $this->numbers->next();
			$summary['date']       = wp_date( get_option( 'date_format', 'd/m/Y' ) );
			return $summary;
		} catch ( InvalidArgumentException $exception ) {
			return new WP_Error( 'mediacon_invalid_quote', $exception->getMessage(), array( 'status' => 400 ) );
		} catch ( Throwable $exception ) {
			return new WP_Error( 'mediacon_quote_error', __( 'Impossibile calcolare il preventivo con i dati indicati.', 'mediacon-enterprise' ), array( 'status' => 500 ) );
		}
	}
}
