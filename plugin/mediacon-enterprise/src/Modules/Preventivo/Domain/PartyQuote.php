<?php
/**
 * Per-party quote.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Contains the autonomous quote for one party. */
final readonly class PartyQuote {

	/**
	 * Create a per-party quote.
	 *
	 * @param Party            $party    Quoted party.
	 * @param array<QuoteLine> $lines    Detailed lines.
	 * @param Money            $total    Total due.
	 * @param ResidualTotal    $residual Residual amount.
	 */
	public function __construct( private Party $party, private array $lines, private Money $total, private ResidualTotal $residual ) {}

	/**
	 * Return the total due.
	 *
	 * @return Money
	 */
	public function total(): Money {
		return $this->total;
	}

	/**
	 * Return the residual total.
	 *
	 * @return ResidualTotal
	 */
	public function residual(): ResidualTotal {
		return $this->residual;
	}

	/**
	 * Serialize the quote for the public endpoint.
	 *
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return array(
			'id'       => $this->party->id(),
			'name'     => $this->party->name(),
			'role'     => $this->party->role(),
			'status'   => $this->party->status(),
			'centers'  => count( $this->party->centers() ),
			'lines'    => array_map( static fn ( QuoteLine $line ): array => $line->toArray(), $this->lines ),
			'paid'     => $this->party->paid()->amount->amount(),
			'total'    => $this->total->amount(),
			'residual' => $this->residual->amount()->amount(),
		);
	}
}
