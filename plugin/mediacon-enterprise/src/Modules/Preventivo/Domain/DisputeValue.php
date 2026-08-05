<?php
/**
 * Dispute value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/** Represents the positive economic value of the dispute. */
final readonly class DisputeValue {

	/**
	 * Create a dispute value.
	 *
	 * @param float $amount Dispute value in euros.
	 * @throws InvalidArgumentException When the amount is not positive.
	 */
	public function __construct( private float $amount ) {
		if ( ! is_finite( $amount ) || $amount <= 0 ) {
			throw new InvalidArgumentException( 'The dispute value must be greater than zero.' );
		}
	}

	/**
	 * Return the dispute value.
	 *
	 * @return float
	 */
	public function amount(): float {
		return $this->amount;
	}
}
