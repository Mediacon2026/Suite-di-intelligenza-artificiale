<?php
/**
 * Mediation party entity.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/** Models one independently charged party. */
final readonly class Party {

	public const ROLE_CLAIMANT = 'claimant';
	public const ROLE_INVITED  = 'invited';
	public const PRESENT       = 'present';
	public const ABSENT        = 'absent';
	public const NON_ADHERENT  = 'nonadherent';

	/**
	 * Create a party.
	 *
	 * @param string                $id       Stable browser identifier.
	 * @param string                $name     Display name.
	 * @param string                $role     Party role.
	 * @param string                $status   Attendance status.
	 * @param array<InterestCenter> $centers  Interest centers.
	 * @param array<LiveExpense>    $expenses Explicit live expenses.
	 * @param PaidAmount            $paid     Amount already paid.
	 * @throws InvalidArgumentException When values are invalid.
	 */
	public function __construct(
		private string $id,
		private string $name,
		private string $role,
		private string $status,
		private array $centers,
		private array $expenses,
		private PaidAmount $paid
	) {
		if ( '' === trim( $id ) || '' === trim( $name ) ) {
			throw new InvalidArgumentException( 'Each party requires an identifier and name.' );
		}
		if ( ! in_array( $role, array( self::ROLE_CLAIMANT, self::ROLE_INVITED ), true ) ) {
			throw new InvalidArgumentException( 'Invalid party role.' );
		}
		if ( ! in_array( $status, array( self::PRESENT, self::ABSENT, self::NON_ADHERENT ), true ) ) {
			throw new InvalidArgumentException( 'Invalid party status.' );
		}
		if ( array() === $centers ) {
			throw new InvalidArgumentException( 'Each party requires at least one interest center.' );
		}
	}

	/**
	 * Return the party identifier.
	 *
	 * @return string
	 */
	public function id(): string {
		return $this->id;
	}

	/**
	 * Return the party name.
	 *
	 * @return string
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * Return the party role.
	 *
	 * @return string
	 */
	public function role(): string {
		return $this->role;
	}

	/**
	 * Return the attendance status.
	 *
	 * @return string
	 */
	public function status(): string {
		return $this->status;
	}

	/**
	 * Return the interest centers.
	 *
	 * @return array<InterestCenter>
	 */
	public function centers(): array {
		return $this->centers;
	}

	/**
	 * Return explicit live expenses.
	 *
	 * @return array<LiveExpense>
	 */
	public function expenses(): array {
		return $this->expenses;
	}

	/**
	 * Return the amount already paid.
	 *
	 * @return PaidAmount
	 */
	public function paid(): PaidAmount {
		return $this->paid;
	}

	/**
	 * Determine whether the party owes tariff charges.
	 *
	 * @return bool
	 */
	public function isChargeable(): bool {
		return self::PRESENT === $this->status;
	}
}
