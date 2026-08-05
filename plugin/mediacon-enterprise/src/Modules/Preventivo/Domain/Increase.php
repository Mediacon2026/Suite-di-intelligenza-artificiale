<?php
/**
 * Increase value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Applies a configurable percentage increase. */
final readonly class Increase {

	/**
	 * Create an increase.
	 *
	 * @param float $percentage Non-negative increase percentage.
	 */
	public function __construct( private float $percentage ) {}

	/**
	 * Calculate the increase amount.
	 *
	 * @param Money $amount Base amount.
	 * @return Money
	 */
	public function amountFor( Money $amount ): Money {
		return $amount->multiply( max( 0, $this->percentage ) / 100 );
	}

	/**
	 * Return the percentage.
	 *
	 * @return float
	 */
	public function percentage(): float {
		return max( 0, $this->percentage );
	}
}
