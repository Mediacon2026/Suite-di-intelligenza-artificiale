<?php
/**
 * Preventivo pricing configuration.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Services;

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Preventivo\Domain\DisputeValue;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Increase;
use Mediacon\Enterprise\Modules\Preventivo\Domain\MediationType;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Money;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Reduction;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Scenario;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Tariff;
use Mediacon\Enterprise\Modules\Preventivo\Domain\TariffBracket;
use RuntimeException;

defined( 'ABSPATH' ) || exit;

/** Converts sanitized Core settings into pricing value objects. */
final class PricingConfiguration {

	/**
	 * Create the configuration adapter.
	 *
	 * @param SettingsManager $settings Core settings.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Return all Preventivo settings.
	 *
	 * @return array<string,mixed>
	 */
	public function all(): array {
		$value = $this->settings->get( 'preventivo', array() );
		return is_array( $value ) ? $value : array();
	}

	/**
	 * Build sorted tariff brackets.
	 *
	 * @return array<TariffBracket>
	 */
	public function brackets(): array {
		$rows     = $this->all()['brackets'] ?? array();
		$rows     = is_array( $rows ) ? $rows : array();
		$brackets = array();
		foreach ( $rows as $row ) {
			if ( is_array( $row ) ) {
				$brackets[] = new TariffBracket( (float) ( $row['max'] ?? 0 ), Money::euros( $row['fee'] ?? 0 ) );
			}
		}
		usort( $brackets, static fn ( TariffBracket $a, TariffBracket $b ): int => ( 0.0 === $a->maximum() ? PHP_INT_MAX : $a->maximum() ) <=> ( 0.0 === $b->maximum() ? PHP_INT_MAX : $b->maximum() ) );
		return $brackets;
	}

	/**
	 * Select the tariff for a dispute value.
	 *
	 * @param DisputeValue $value Dispute value.
	 * @return Tariff
	 * @throws RuntimeException When no bracket is configured.
	 */
	public function tariffFor( DisputeValue $value ): Tariff {
		foreach ( $this->brackets() as $bracket ) {
			if ( $bracket->contains( $value ) ) {
				return new Tariff( $bracket->tariff() );
			}
		}
		throw new RuntimeException( 'No tariff bracket is configured for this value.' );
	}

	/**
	 * Select the applicable economic-regime reduction.
	 *
	 * @param MediationType $type Mediation type.
	 * @return Reduction
	 */
	public function reductionFor( MediationType $type ): Reduction {
		$rates = $this->all()['reductions'] ?? array();
		$rates = is_array( $rates ) ? $rates : array();
		return new Reduction( (float) ( $rates[ $type->economicRegime() ] ?? 0 ) );
	}

	/**
	 * Select the configured scenario increase.
	 *
	 * @param Scenario $scenario Procedural scenario.
	 * @return Increase
	 */
	public function increaseFor( Scenario $scenario ): Increase {
		$rates = $this->all()['increases'] ?? array();
		$rates = is_array( $rates ) ? $rates : array();
		return new Increase( (float) ( $rates[ $scenario->key ] ?? 0 ) );
	}
}
