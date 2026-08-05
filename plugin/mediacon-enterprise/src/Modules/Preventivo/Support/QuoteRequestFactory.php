<?php
/**
 * Public quote request factory.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Support;

use InvalidArgumentException;
use Mediacon\Enterprise\Modules\Preventivo\Domain\DisputeValue;
use Mediacon\Enterprise\Modules\Preventivo\Domain\InterestCenter;
use Mediacon\Enterprise\Modules\Preventivo\Domain\LiveExpense;
use Mediacon\Enterprise\Modules\Preventivo\Domain\MediationType;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Money;
use Mediacon\Enterprise\Modules\Preventivo\Domain\PaidAmount;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Party;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Scenario;

defined( 'ABSPATH' ) || exit;

/** Validates untrusted request data and creates domain objects. */
final class QuoteRequestFactory {

	/**
	 * Validate input and build calculation values.
	 *
	 * @param array<string,mixed> $input Untrusted request values.
	 * @return array{value:DisputeValue,type:MediationType,scenario:Scenario,parties:array<Party>,increase:float}
	 * @throws InvalidArgumentException When required values are invalid.
	 */
	public function create( array $input ): array {
		$rows = isset( $input['parties'] ) && is_array( $input['parties'] ) ? array_slice( $input['parties'], 0, 20 ) : array();
		if ( array() === $rows ) {
			throw new InvalidArgumentException( 'Add at least one party.' );
		}
		$parties = array();
		foreach ( $rows as $index => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$centers = array();
			foreach ( array_slice( is_array( $row['centers'] ?? null ) ? $row['centers'] : array(), 0, 20 ) as $center_index => $label ) {
				$center_label = sanitize_text_field( (string) $label );
				$centers[]    = new InterestCenter( (string) $center_index, '' !== $center_label ? $center_label : __( 'Centro di interesse', 'mediacon-enterprise' ) );
			}
			$expenses = array();
			foreach ( array_slice( is_array( $row['expenses'] ?? null ) ? $row['expenses'] : array(), 0, 20 ) as $expense ) {
				if ( is_array( $expense ) && 0 < (float) ( $expense['amount'] ?? 0 ) ) {
					$expenses[] = new LiveExpense( sanitize_text_field( (string) ( $expense['label'] ?? __( 'Spesa viva', 'mediacon-enterprise' ) ) ), Money::euros( $expense['amount'] ) );
				}
			}
			$parties[] = new Party(
				sanitize_key( (string) ( $row['id'] ?? 'party-' . $index ) ),
				sanitize_text_field( (string) ( $row['name'] ?? '' ) ),
				sanitize_key( (string) ( $row['role'] ?? '' ) ),
				sanitize_key( (string) ( $row['status'] ?? Party::PRESENT ) ),
				$centers,
				$expenses,
				new PaidAmount( Money::euros( $row['paid'] ?? 0 ) )
			);
		}
		if ( array() === $parties ) {
			throw new InvalidArgumentException( 'Add at least one valid party.' );
		}
		if ( ! array_filter( $parties, static fn ( Party $party ): bool => Party::ROLE_CLAIMANT === $party->role() ) ) {
			throw new InvalidArgumentException( 'Add at least one claimant.' );
		}
		return array(
			'value'    => new DisputeValue( (float) ( $input['value'] ?? 0 ) ),
			'type'     => new MediationType( sanitize_key( (string) ( $input['type'] ?? '' ) ) ),
			'scenario' => new Scenario( sanitize_key( (string) ( $input['scenario'] ?? '' ) ) ),
			'parties'  => $parties,
			'increase' => min( 300, max( 0, (float) ( $input['increase'] ?? 0 ) ) ),
		);
	}
}
