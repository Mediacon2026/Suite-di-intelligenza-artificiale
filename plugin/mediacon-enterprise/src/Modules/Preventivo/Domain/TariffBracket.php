<?php
/**
 * Tariff bracket value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Associates a configurable upper dispute value with a per-centre tariff. */
final readonly class TariffBracket {

	/**
	 * Create a tariff bracket.
	 *
	 * @param float $maximum Maximum value; zero means no upper limit.
	 * @param Money $tariff  Per-centre tariff.
	 */
	public function __construct( private float $maximum, private Money $tariff ) {}

	/**
	 * Determine whether the value belongs to the bracket.
	 *
	 * @param DisputeValue $value Dispute value.
	 * @return bool
	 */
	public function contains( DisputeValue $value ): bool {
		return 0.0 === $this->maximum || $value->amount() <= $this->maximum;
	}

	/**
	 * Return the bracket tariff.
	 *
	 * @return Money
	 */
	public function tariff(): Money {
		return $this->tariff;
	}

	/**
	 * Return the upper limit.
	 *
	 * @return float
	 */
	public function maximum(): float {
		return $this->maximum;
	}
}
