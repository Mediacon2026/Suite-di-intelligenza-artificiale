<?php
/**
 * Reduction value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Applies a percentage reduction to a monetary amount. */
final readonly class Reduction {

	/**
	 * Create a reduction.
	 *
	 * @param float $percentage Percentage between zero and one hundred.
	 */
	public function __construct( private float $percentage ) {}

	/**
	 * Apply the reduction.
	 *
	 * @param Money $amount Base amount.
	 * @return Money
	 */
	public function apply( Money $amount ): Money {
		return $amount->multiply( 1 - min( 100, max( 0, $this->percentage ) ) / 100 );
	}

	/**
	 * Return the percentage.
	 *
	 * @return float
	 */
	public function percentage(): float {
		return min( 100, max( 0, $this->percentage ) );
	}
}
