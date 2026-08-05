<?php
/**
 * Non-destructive editorial quality auditor.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Editorial\Services;

use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Reports content quality signals without changing WordPress data.
 */
final class QualityAuditor {

	/**
	 * Audit recently published posts.
	 *
	 * @param int $limit Maximum posts to inspect.
	 * @return array<int,array{post:WP_Post,warnings:array<int,string>}>
	 */
	public function recent( int $limit = 20 ): array {
		$posts   = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => min( 50, max( 1, $limit ) ),
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		$results = array();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$warnings = $this->audit( $post );
			if ( array() !== $warnings ) {
				$results[] = array(
					'post'     => $post,
					'warnings' => $warnings,
				);
			}
		}

		return $results;
	}

	/**
	 * Audit one post without modifying it.
	 *
	 * @param WP_Post $post Post to inspect.
	 * @return array<int,string>
	 */
	public function audit( WP_Post $post ): array {
		$warnings = array();
		$content  = (string) $post->post_content;

		if ( ! has_post_thumbnail( $post ) ) {
			$warnings[] = __( 'Immagine in evidenza mancante', 'mediacon-enterprise' );
		} elseif ( '' === trim( (string) get_post_meta( get_post_thumbnail_id( $post ), '_wp_attachment_image_alt', true ) ) ) {
			$warnings[] = __( 'Immagine in evidenza senza testo alternativo', 'mediacon-enterprise' );
		}
		if ( '' === trim( (string) $post->post_excerpt ) ) {
			$warnings[] = __( 'Estratto manuale mancante', 'mediacon-enterprise' );
		}
		if ( array() === wp_get_post_categories( $post->ID ) ) {
			$warnings[] = __( 'Categoria mancante', 'mediacon-enterprise' );
		}
		if ( strlen( wp_strip_all_tags( get_the_title( $post ) ) ) > 70 ) {
			$warnings[] = __( 'Titolo superiore a 70 caratteri', 'mediacon-enterprise' );
		}
		if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
			$warnings[] = __( 'Contenuto vuoto', 'mediacon-enterprise' );
		}
		if ( preg_match( '/<img\b(?![^>]*\balt=(?:"[^"]+"|\'[^\']+\'))[^>]*>/i', $content ) ) {
			$warnings[] = __( 'Immagine nel contenuto senza testo alternativo', 'mediacon-enterprise' );
		}
		if ( preg_match( '/\[[a-z][a-z0-9_-]*(?:\s[^\]]*)?\]/i', $content ) ) {
			$warnings[] = __( 'Possibile shortcode visibile', 'mediacon-enterprise' );
		}
		if ( preg_match_all( '/<h1\b/i', $content ) > 1 ) {
			$warnings[] = __( 'Più di un H1 nel contenuto', 'mediacon-enterprise' );
		}

		return $warnings;
	}
}
