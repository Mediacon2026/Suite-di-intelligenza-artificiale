<?php
/**
 * Residual total value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Stores the amount still due after payments. */
final readonly class ResidualTotal {

	/**
	 * Create the residual total.
	 *
	 * @param Money $amount Residual amount.
	 */
	public function __construct( private Money $amount ) {}

	/**
	 * Return the residual amount.
	 *
	 * @return Money
	 */
	public function amount(): Money {
		return $this->amount;
	}
}
