<?php
/**
 * Safe search-term highlighter.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Highlights escaped display text without changing stored content.
 */
final class Highlighter {

	/**
	 * Highlight query terms in escaped text.
	 *
	 * @param string        $text  Plain display text.
	 * @param array<string> $terms Query terms.
	 * @return string
	 */
	public function highlight( string $text, array $terms ): string {
		$output = esc_html( $text );
		$terms  = array_values( array_unique( array_filter( array_map( static fn ( mixed $term ): string => esc_html( (string) $term ), $terms ) ) ) );
		usort( $terms, static fn ( string $left, string $right ): int => strlen( $right ) <=> strlen( $left ) );
		if ( array() === $terms ) {
			return $output;
		}
		$pattern = '/(' . implode( '|', array_map( static fn ( string $term ): string => preg_quote( $term, '/' ), $terms ) ) . ')/iu';

		return preg_replace( $pattern, '<mark>$1</mark>', $output ) ?? $output;
	}
}
