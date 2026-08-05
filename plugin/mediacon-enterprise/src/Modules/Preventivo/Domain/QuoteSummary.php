<?php
/**
 * Quote summary.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Aggregates distinct party quotes without redistributing their totals. */
final readonly class QuoteSummary {

	/**
	 * Create a quote summary.
	 *
	 * @param array<PartyQuote> $parties  Party quotes.
	 * @param MediationType     $type     Economic regime.
	 * @param Scenario          $scenario Procedural scenario.
	 */
	public function __construct( private array $parties, private MediationType $type, private Scenario $scenario ) {}

	/**
	 * Return the autonomous party quotes.
	 *
	 * @return array<PartyQuote>
	 */
	public function parties(): array {
		return $this->parties;
	}

	/**
	 * Sum the party totals without redistribution.
	 *
	 * @return Money
	 */
	public function generalTotal(): Money {
		$total = Money::zero();
		foreach ( $this->parties as $party ) {
			$total = $total->add( $party->total() );
		}
		return $total;
	}

	/**
	 * Sum the party residual amounts.
	 *
	 * @return Money
	 */
	public function generalResidual(): Money {
		$total = Money::zero();
		foreach ( $this->parties as $party ) {
			$total = $total->add( $party->residual()->amount() );
		}
		return $total;
	}

	/**
	 * Serialize the complete summary.
	 *
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return array(
			'type'             => $this->type->value(),
			'regime'           => $this->type->economicRegime(),
			'scenario'         => $this->scenario->label(),
			'parties'          => array_map( static fn ( PartyQuote $quote ): array => $quote->toArray(), $this->parties ),
			'general_total'    => $this->generalTotal()->amount(),
			'general_residual' => $this->generalResidual()->amount(),
		);
	}
}
