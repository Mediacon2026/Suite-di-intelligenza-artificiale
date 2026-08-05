<?php
/**
 * Simulation numbering service.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Services;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/** Produces sequential, non-case-management simulation references. */
final readonly class SimulationNumber {

	/**
	 * Create the numbering service.
	 *
	 * @param SettingsManager $settings Core settings.
	 */
	public function __construct( private SettingsManager $settings ) {}

	/**
	 * Reserve and return the next simulation reference.
	 *
	 * @return string
	 */
	public function next(): string {
		$config               = $this->settings->get( 'preventivo', array() );
		$config               = is_array( $config ) ? $config : array();
		$simulation           = is_array( $config['simulation'] ?? null ) ? $config['simulation'] : array();
		$number               = max( 1, absint( $simulation['next_number'] ?? 1 ) );
		$prefix               = strtoupper( sanitize_key( (string) ( $simulation['prefix'] ?? 'mc-prev' ) ) );
		$config['simulation'] = array(
			'prefix'      => $prefix,
			'next_number' => $number + 1,
		);
		$this->settings->set( 'preventivo', $config );
		return sprintf( '%s-%s-%05d', $prefix, wp_date( 'Y' ), $number );
	}
}
