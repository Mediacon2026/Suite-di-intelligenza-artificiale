<?php
/**
 * Monetary value object.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Preventivo\Domain;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/** Stores euro amounts as integer cents. */
final readonly class Money {

	/**
	 * Create a monetary value.
	 *
	 * @param int $cents Amount in cents.
	 */
	public function __construct( private int $cents ) {}

	/**
	 * Create a value from euros.
	 *
	 * @param float|int|string $amount Euro amount.
	 * @return self
	 * @throws InvalidArgumentException When the amount is not finite.
	 */
	public static function euros( float|int|string $amount ): self {
		$value = (float) $amount;
		if ( ! is_finite( $value ) ) {
			throw new InvalidArgumentException( 'The monetary amount must be finite.' );
		}

		return new self( max( 0, (int) round( $value * 100 ) ) );
	}

	/**
	 * Return a zero monetary value.
	 *
	 * @return self
	 */
	public static function zero(): self {
		return new self( 0 );
	}

	/**
	 * Add another monetary value.
	 *
	 * @param self $other Added amount.
	 * @return self
	 */
	public function add( self $other ): self {
		return new self( $this->cents + $other->cents );
	}

	/**
	 * Subtract without allowing a negative result.
	 *
	 * @param self $other Subtracted amount.
	 * @return self
	 */
	public function subtractFloorZero( self $other ): self {
		return new self( max( 0, $this->cents - $other->cents ) );
	}

	/**
	 * Multiply by a factor.
	 *
	 * @param float $factor Multiplication factor.
	 * @return self
	 */
	public function multiply( float $factor ): self {
		return new self( max( 0, (int) round( $this->cents * $factor ) ) );
	}

	/**
	 * Return the value in cents.
	 *
	 * @return int
	 */
	public function cents(): int {
		return $this->cents;
	}

	/**
	 * Return the value in euros.
	 *
	 * @return float
	 */
	public function amount(): float {
		return $this->cents / 100;
	}
}
