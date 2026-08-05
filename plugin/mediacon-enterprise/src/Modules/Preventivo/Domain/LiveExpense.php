<?php
/**
 * Live expense value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Represents a documented expense assigned to one party only. */
final readonly class LiveExpense {

	/**
	 * Create a documented expense.
	 *
	 * @param string $label  Expense label.
	 * @param Money  $amount Expense amount.
	 */
	public function __construct( public string $label, public Money $amount ) {}
}
