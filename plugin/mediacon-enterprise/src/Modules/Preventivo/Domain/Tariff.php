<?php
/**
 * Mediation tariff value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Represents the tariff assigned to one interest center. */
final readonly class Tariff {

	/**
	 * Create a tariff.
	 *
	 * @param Money $amount Tariff amount.
	 */
	public function __construct( private Money $amount ) {}

	/**
	 * Return the tariff amount.
	 *
	 * @return Money
	 */
	public function amount(): Money {
		return $this->amount;
	}
}
