<?php
/**
 * Per-party quote calculator.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Services;

use Mediacon\Enterprise\Modules\Preventivo\Domain\DisputeValue;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Increase;
use Mediacon\Enterprise\Modules\Preventivo\Domain\MediationType;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Money;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Party;
use Mediacon\Enterprise\Modules\Preventivo\Domain\PartyQuote;
use Mediacon\Enterprise\Modules\Preventivo\Domain\QuoteLine;
use Mediacon\Enterprise\Modules\Preventivo\Domain\QuoteSummary;
use Mediacon\Enterprise\Modules\Preventivo\Domain\ResidualTotal;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Scenario;

defined( 'ABSPATH' ) || exit;

/** Applies configured rules independently to every party. */
final readonly class QuoteCalculator {

	/**
	 * Create the calculator.
	 *
	 * @param PricingConfiguration $configuration Pricing settings.
	 */
	public function __construct( private PricingConfiguration $configuration ) {}

	/**
	 * Calculate an autonomous quote for each party.
	 *
	 * @param DisputeValue  $value           Dispute value.
	 * @param MediationType $type            Mediation type.
	 * @param Scenario      $scenario        Procedural scenario.
	 * @param array<Party>  $parties         Parties.
	 * @param float         $manual_increase Additional configured increase.
	 * @return QuoteSummary
	 */
	public function calculate( DisputeValue $value, MediationType $type, Scenario $scenario, array $parties, float $manual_increase = 0 ): QuoteSummary {
		$tariff    = $this->configuration->tariffFor( $value )->amount();
		$reduction = $this->configuration->reductionFor( $type );
		$increase  = $this->configuration->increaseFor( $scenario );
		$quotes    = array();

		foreach ( $parties as $party ) {
			$lines = array();
			$total = Money::zero();
			if ( $party->isChargeable() ) {
				$base      = $tariff->multiply( count( $party->centers() ) );
				$lines[]   = new QuoteLine( __( 'Tariffa per centri di interesse', 'mediacon-enterprise' ), $base );
				$reduced   = $reduction->apply( $base );
				$deduction = $base->subtractFloorZero( $reduced );
				if ( 0 < $deduction->cents() ) {
					/* translators: %s is the applied reduction percentage. */
					$lines[] = new QuoteLine( sprintf( __( 'Riduzione regime %.2f%%', 'mediacon-enterprise' ), $reduction->percentage() ), $deduction, 'subtract' );
				}
				$total = $reduced;
				$total = $this->addIncrease( $lines, $total, $increase, __( 'Aumento per scenario', 'mediacon-enterprise' ) );
				$total = $this->addIncrease( $lines, $total, new Increase( $manual_increase ), __( 'Aumento ulteriore', 'mediacon-enterprise' ) );
			}
			foreach ( $party->expenses() as $expense ) {
				$lines[] = new QuoteLine( $expense->label, $expense->amount );
				$total   = $total->add( $expense->amount );
			}
			$quotes[] = new PartyQuote( $party, $lines, $total, new ResidualTotal( $total->subtractFloorZero( $party->paid()->amount ) ) );
		}
		return new QuoteSummary( $quotes, $type, $scenario );
	}

	/**
	 * Add an increase and its explanatory line.
	 *
	 * @param array<QuoteLine> $lines    Quote lines, passed by reference.
	 * @param Money            $base     Current base.
	 * @param Increase         $increase Increase rule.
	 * @param string           $label    Public line label.
	 * @return Money
	 */
	private function addIncrease( array &$lines, Money $base, Increase $increase, string $label ): Money {
		$amount = $increase->amountFor( $base );
		if ( 0 < $amount->cents() ) {
			$lines[] = new QuoteLine( sprintf( '%s (%.2f%%)', $label, $increase->percentage() ), $amount );
		}
		return $base->add( $amount );
	}
}
