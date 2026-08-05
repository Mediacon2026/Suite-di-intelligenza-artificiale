<?php
/**
 * Mediation scenario value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/** Centralizes supported procedural scenarios and their public labels. */
final readonly class Scenario {

	public const ABSENCE               = 'absence';
	public const FIRST_NO_AGREEMENT    = 'first_no_agreement';
	public const FIRST_AGREEMENT       = 'first_agreement';
	public const CONTINUATION          = 'continuation';
	public const MULTIPLE_NO_AGREEMENT = 'multiple_no_agreement';
	public const MULTIPLE_AGREEMENT    = 'multiple_agreement';
	public const MEDIATOR_PROPOSAL     = 'mediator_proposal';

	/**
	 * Create a procedural scenario.
	 *
	 * @param string $key Scenario key.
	 * @throws InvalidArgumentException When the scenario is unsupported.
	 */
	public function __construct( public string $key ) {
		if ( ! array_key_exists( $key, self::labels() ) ) {
			throw new InvalidArgumentException( 'Unsupported mediation scenario.' );
		}
	}

	/**
	 * Return the supported public labels.
	 *
	 * @return array<string,string>
	 */
	public static function labels(): array {
		return array(
			self::ABSENCE               => __( 'Assenza della parte invitata', 'mediacon-enterprise' ),
			self::FIRST_NO_AGREEMENT    => __( 'Mancato accordo al primo incontro', 'mediacon-enterprise' ),
			self::FIRST_AGREEMENT       => __( 'Accordo al primo incontro', 'mediacon-enterprise' ),
			self::CONTINUATION          => __( 'Prosecuzione oltre il primo incontro', 'mediacon-enterprise' ),
			self::MULTIPLE_NO_AGREEMENT => __( 'Mancato accordo dopo più incontri', 'mediacon-enterprise' ),
			self::MULTIPLE_AGREEMENT    => __( 'Accordo dopo più incontri', 'mediacon-enterprise' ),
			self::MEDIATOR_PROPOSAL     => __( 'Proposta del Mediatore', 'mediacon-enterprise' ),
		);
	}

	/**
	 * Return the public scenario label.
	 *
	 * @return string
	 */
	public function label(): string {
		return self::labels()[ $this->key ];
	}
}
