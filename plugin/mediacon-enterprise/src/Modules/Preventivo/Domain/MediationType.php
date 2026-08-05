<?php
/**
 * Mediation type value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/** Centralizes economic-regime selection. */
final readonly class MediationType {

	public const MANDATORY     = 'mandatory';
	public const COURT_ORDERED = 'court_ordered';
	public const VOLUNTARY     = 'voluntary';

	/**
	 * Create a mediation type.
	 *
	 * @param string $key Mediation type key.
	 * @throws InvalidArgumentException When the type is unsupported.
	 */
	public function __construct( public string $key ) {
		if ( ! array_key_exists( $key, self::labels() ) ) {
			throw new InvalidArgumentException( 'Unsupported mediation type.' );
		}
	}

	/**
	 * Return public type labels.
	 *
	 * @return array<string,string>
	 */
	public static function labels(): array {
		return array(
			self::MANDATORY     => __( 'Mediazione obbligatoria', 'mediacon-enterprise' ),
			self::COURT_ORDERED => __( 'Mediazione demandata dal giudice', 'mediacon-enterprise' ),
			self::VOLUNTARY     => __( 'Mediazione volontaria', 'mediacon-enterprise' ),
		);
	}

	/**
	 * Return the applicable economic regime.
	 *
	 * @return string
	 */
	public function economicRegime(): string {
		return self::COURT_ORDERED === $this->key ? self::MANDATORY : $this->key;
	}

	/**
	 * Return the stable type key.
	 *
	 * @return string
	 */
	public function value(): string {
		return $this->key;
	}

	/**
	 * Return the public type label.
	 *
	 * @return string
	 */
	public function label(): string {
		return self::labels()[ $this->key ];
	}
}
