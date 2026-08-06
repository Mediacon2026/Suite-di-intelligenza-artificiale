<?php
/**
 * Safe presentation of saved WordPress content.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demote content-owned H1 headings while the theme owns the document H1.
 *
 * @param string $content Filtered WordPress content.
 */
function mediacon_one_demote_content_h1( string $content ): string {
	$content = preg_replace( '/<h1(\s[^>]*)?>/i', '<h2$1>', $content );
	$content = preg_replace( '/<\/h1\s*>/i', '</h2>', (string) $content );
	return (string) $content;
}

/**
 * Remove configured legacy calculator shortcodes from the Costi fallback.
 *
 * @param string $content Saved WordPress content.
 */
function mediacon_one_suppress_legacy_calculator( string $content ): string {
	if ( 'costs' !== mediacon_one_current_page_key() || mediacon_one_preventivo_owns_page() ) {
		return $content;
	}

	$tags = array( 'mediacon_calculator', 'mediacon_calcolatore', 'calcolatore_mediazione', 'mediacon_preventivo' );
	/** Filter legacy shortcode tags suppressed only on the Costi fallback. */
	$tags = apply_filters( 'mediacon_one_legacy_calculator_shortcodes', $tags );
	foreach ( is_array( $tags ) ? $tags : array() as $tag ) {
		$tag = preg_quote( sanitize_key( (string) $tag ), '/' );
		if ( '' === $tag ) {
			continue;
		}
		$content = preg_replace( '/\[' . $tag . '(?:\s[^\]]*)?\](?:.*?\[\/' . $tag . '\])?/is', '', $content );
	}

	return (string) $content;
}

/** Render the filtered saved content without changing the stored post. */
function mediacon_one_the_content(): void {
	add_filter( 'the_content', 'mediacon_one_suppress_legacy_calculator', 8 );
	add_filter( 'the_content', 'mediacon_one_demote_content_h1', PHP_INT_MAX );
	the_content();
	remove_filter( 'the_content', 'mediacon_one_demote_content_h1', PHP_INT_MAX );
	remove_filter( 'the_content', 'mediacon_one_suppress_legacy_calculator', 8 );
}

/**
 * Return the status presented on course cards.
 *
 * @param int $post_id Course post ID.
 */
function mediacon_one_course_status( int $post_id ): array {
	$date = '';
	foreach ( array( '_mediacon_course_end_date', 'course_end_date', 'data_fine_corso' ) as $meta_key ) {
		$value = (string) get_post_meta( $post_id, $meta_key, true );
		if ( '' !== trim( $value ) ) {
			$date = $value;
			break;
		}
	}

	$timestamp = $date ? strtotime( $date ) : false;
	if ( false === $timestamp && preg_match( '/\b(20\d{2})\b/', get_the_title( $post_id ), $matches ) ) {
		$timestamp = strtotime( $matches[1] . '-12-31 23:59:59' );
	}

	$is_future = false !== $timestamp && $timestamp >= current_datetime()->getTimestamp();
	return array(
		'key'   => $is_future ? 'future' : 'concluded',
		'label' => $is_future ? __( 'Corso futuro', 'mediacon-one' ) : __( 'Corso concluso', 'mediacon-one' ),
	);
}
