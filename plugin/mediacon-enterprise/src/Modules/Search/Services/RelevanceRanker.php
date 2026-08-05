<?php
/**
 * Transparent search relevance ranker.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Search\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Applies documented deterministic ranking rules.
 */
final readonly class RelevanceRanker {

	/**
	 * Rank and filter documents.
	 *
	 * @param array<array<string,mixed>> $documents  Candidate documents.
	 * @param array<string>              $terms      Expanded query terms.
	 * @param string                     $exact      Original query.
	 * @param array<string,int>          $priorities Configured type priorities.
	 * @return array<array<string,mixed>>
	 */
	public function rank( array $documents, array $terms, string $exact, array $priorities ): array {
		$ranked = array();
		foreach ( $documents as $document ) {
			if ( 'publish' !== ( $document['status'] ?? 'publish' ) ) {
				continue;
			}
			$score = $this->score( $document, $terms, $exact ) + (int) ( $priorities[ $document['type'] ?? '' ] ?? 0 );
			if ( 0 >= $score ) {
				continue;
			}
			$document['score'] = $score;
			$ranked[]          = $document;
		}
		usort(
			$ranked,
			static function ( array $left, array $right ): int {
				$score_order = $right['score'] <=> $left['score'];
				return 0 !== $score_order ? $score_order : strcmp( (string) ( $right['date'] ?? '' ), (string) ( $left['date'] ?? '' ) );
			}
		);

		return $ranked;
	}

	/**
	 * Calculate a document score.
	 *
	 * @param array<string,mixed> $document Search document.
	 * @param array<string>       $terms    Query terms.
	 * @param string              $exact    Exact query.
	 * @return int
	 */
	public function score( array $document, array $terms, string $exact ): int {
		$title    = $this->normalize( (string) ( $document['title'] ?? '' ) );
		$taxonomy = $this->normalize( (string) ( $document['taxonomy'] ?? '' ) );
		$excerpt  = $this->normalize( (string) ( $document['excerpt'] ?? '' ) );
		$content  = $this->normalize( (string) ( $document['content'] ?? '' ) );
		$needle   = $this->normalize( $exact );
		$score    = '' !== $needle && $title === $needle ? 100 : 0;

		foreach ( $terms as $term ) {
			$term   = $this->normalize( $term );
			$score += $this->contains( $title, $term ) ? 20 : 0;
			$score += $this->contains( $taxonomy, $term ) ? 12 : 0;
			$score += $this->contains( $excerpt, $term ) ? 8 : 0;
			$score += $this->contains( $content, $term ) ? 3 : 0;
		}

		return $score;
	}

	/**
	 * Perform a case-insensitive contains check.
	 *
	 * @param string $haystack Text.
	 * @param string $needle   Term.
	 * @return bool
	 */
	private function contains( string $haystack, string $needle ): bool {
		return '' !== $needle && str_contains( $haystack, $needle );
	}

	/**
	 * Normalize comparison text.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private function normalize( string $text ): string {
		$text = wp_strip_all_tags( $text );

		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text ) : strtolower( $text );
	}
}
