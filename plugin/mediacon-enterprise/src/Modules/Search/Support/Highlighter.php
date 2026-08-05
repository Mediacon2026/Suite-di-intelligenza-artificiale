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
		foreach ( array_unique( array_filter( $terms ) ) as $term ) {
			$output = preg_replace( '/(' . preg_quote( esc_html( $term ), '/' ) . ')/iu', '<mark>$1</mark>', $output ) ?? $output;
		}

		return $output;
	}
}
