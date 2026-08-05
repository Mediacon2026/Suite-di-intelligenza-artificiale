<?php
/**
 * Interest centre value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

defined( 'ABSPATH' ) || exit;

/** Represents one independently charged centre of interest. */
final readonly class InterestCenter {

	/**
	 * Create an interest center.
	 *
	 * @param string $id    Stable center identifier.
	 * @param string $label Public center label.
	 */
	public function __construct( public string $id, public string $label ) {}
}
