<?php
/**
 * Quote line value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Describes one economic component of a party quote. */
final readonly class QuoteLine {

	/**
	 * Create a quote line.
	 *
	 * @param string $label     Public label.
	 * @param Money  $amount    Absolute amount.
	 * @param string $operation Addition or deduction.
	 */
	public function __construct( private string $label, private Money $amount, private string $operation = 'add' ) {}

	/**
	 * Serialize the line.
	 *
	 * @return array{label:string,amount:float,operation:string}
	 */
	public function toArray(): array {
		return array(
			'label'     => $this->label,
			'amount'    => $this->amount->amount(),
			'operation' => $this->operation,
		);
	}
}
