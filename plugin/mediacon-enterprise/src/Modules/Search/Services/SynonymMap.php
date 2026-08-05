<?php
/**
 * Configurable synonym map.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Expands known terms without opaque processing.
 */
final readonly class SynonymMap {

	/**
	 * Create the synonym map.
	 *
	 * @param array<array<string>> $groups Configured synonym groups.
	 */
	public function __construct( private array $groups ) {}

	/**
	 * Expand words found in a query.
	 *
	 * @param string $query Search query.
	 * @return array<string>
	 */
	public function expand( string $query ): array {
		$terms = $this->words( $query );
		foreach ( $this->groups as $group ) {
			$normalized = array_map( array( $this, 'normalize' ), $group );
			if ( array_intersect( $terms, $normalized ) ) {
				$terms = array_merge( $terms, $normalized );
			}
		}

		return array_values( array_unique( array_filter( $terms ) ) );
	}

	/**
	 * Return words from plain text.
	 *
	 * @param string $text Plain text.
	 * @return array<string>
	 */
	private function words( string $text ): array {
		$parts = preg_split( '/[^\p{L}\p{N}]+/u', $this->normalize( $text ) );

		return false === $parts ? array() : array_values( array_filter( $parts ) );
	}

	/**
	 * Normalize text for comparisons.
	 *
	 * @param string $text Plain text.
	 * @return string
	 */
	private function normalize( string $text ): string {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( $text ) ) : strtolower( trim( $text ) );
	}
}
