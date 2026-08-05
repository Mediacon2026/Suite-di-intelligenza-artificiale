<?php
/**
 * Paid amount value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Represents an amount already paid by one party. */
final readonly class PaidAmount {

	/**
	 * Create a paid amount.
	 *
	 * @param Money $amount Paid amount.
	 */
	public function __construct( public Money $amount ) {}
}
