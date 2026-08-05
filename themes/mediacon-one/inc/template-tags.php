<?php
/**
 * Presentation-only template helpers.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the first existing page URL without creating or updating content.
 *
 * @param array<int,string> $slugs Candidate saved page slugs.
 */
function mediacon_one_page_url( array $slugs ): string {
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( sanitize_title( (string) $slug ) );
		if ( $page instanceof WP_Post ) {
			return (string) get_permalink( $page );
		}
	}
	return home_url( '/' );
}

/** Return a concise semantic context for the current saved page. */
function mediacon_one_page_context(): array {
	$slug = is_page() ? (string) get_post_field( 'post_name', get_queried_object_id() ) : '';
	$map  = array(
		'preventivo'    => array( 'tools', __( 'Strumenti', 'mediacon-one' ) ),
		'ricerca'       => array( 'tools', __( 'Ricerca', 'mediacon-one' ) ),
		'formazione'    => array( 'formation', __( 'Formazione', 'mediacon-one' ) ),
		'corsi'         => array( 'formation', __( 'Formazione', 'mediacon-one' ) ),
		'docenti'       => array( 'formation', __( 'Formazione', 'mediacon-one' ) ),
		'mediazione'    => array( 'mediation', __( 'Mediazione', 'mediacon-one' ) ),
		'come-funziona' => array( 'mediation', __( 'Mediazione', 'mediacon-one' ) ),
		'costi'         => array( 'mediation', __( 'Mediazione', 'mediacon-one' ) ),
	);
	return $map[ $slug ] ?? array( 'institutional', __( 'Mediacon', 'mediacon-one' ) );
}

/** Print a post date with machine-readable markup. */
function mediacon_one_posted_on(): void {
	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

/** Render pagination with accessible labels. */
function mediacon_one_pagination(): void {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( 'Precedente', 'mediacon-one' ),
			'next_text' => __( 'Successiva', 'mediacon-one' ),
		)
	);
}
