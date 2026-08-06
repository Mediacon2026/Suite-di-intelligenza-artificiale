<?php
/**
 * Read-only live-site configuration sources.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the resources that must exist before activation on mediacon.org.
 *
 * Integrators can alter this in code with the mediacon_one_page_map filter. No
 * option, post, permalink, or other database record is written by the theme.
 *
 * @return array<string,array<string,string>>
 */
function mediacon_one_page_map(): array {
	$map = array(
		'home'             => array(
			'type'     => 'front_page',
			'path'     => 'home',
			'renderer' => 'wordpress',
		),
		'mediation'        => array(
			'type'     => 'page',
			'path'     => 'la-mediazione-2',
			'renderer' => 'wordpress',
		),
		'how_it_works'     => array(
			'type'     => 'page',
			'path'     => 'cose-e-come-funziona-la-mediazione-2',
			'renderer' => 'wordpress',
		),
		'application'      => array(
			'type'     => 'page',
			'path'     => 'istanza-di-mediazione',
			'renderer' => 'wordpress',
		),
		'adhesion'         => array(
			'type'     => 'page',
			'path'     => 'modello-di-adesione-alla-mediazione',
			'renderer' => 'wordpress',
		),
		'costs'            => array(
			'type'     => 'page',
			'path'     => 'costi-della-mediazione',
			'renderer' => 'enterprise_preventivo',
		),
		'online_mediation' => array(
			'type'     => 'page',
			'path'     => 'mediazione-online',
			'renderer' => 'wordpress',
		),
		'training'         => array(
			'type'     => 'page',
			'path'     => 'formazione',
			'renderer' => 'wordpress',
		),
		'contacts'         => array(
			'type'     => 'page',
			'path'     => 'contatti',
			'renderer' => 'wordpress',
		),
		'mediators'        => array(
			'type'     => 'page',
			'path'     => 'il-team-dei-nostri-mediatori',
			'renderer' => 'wordpress',
		),
		'offices'          => array(
			'type'     => 'page',
			'path'     => 'le-nostre-sedi',
			'renderer' => 'wordpress',
		),
		'blog'             => array(
			'type'     => 'category',
			'path'     => 'blog',
			'renderer' => 'wordpress_archive',
		),
		'jurisprudence'    => array(
			'type'     => 'category',
			'path'     => 'sentenze-e-giurisprudenza-sulla-mediazione',
			'renderer' => 'wordpress_archive',
		),
		'upcoming_courses' => array(
			'type'     => 'category',
			'path'     => 'prossimi-corsi',
			'renderer' => 'wordpress_archive',
		),
	);

	/** Filter the live-site resource map from deployment code. */
	$filtered = apply_filters( 'mediacon_one_page_map', $map );
	return is_array( $filtered ) ? $filtered : $map;
}

/**
 * Return the single, code-configurable office source.
 *
 * @return array<string,array<string,string>>
 */
function mediacon_one_offices(): array {
	$offices = array(
		'casarano' => array(
			'name'    => 'Casarano',
			'region'  => 'LE',
			'address' => 'Via Bruno Buozzi 10, 73042 Casarano (LE)',
			'status'  => 'operational',
		),
		'pachino'  => array(
			'name'    => 'Pachino',
			'region'  => 'SR',
			'address' => 'Via Fratelli Bandiera 82, 96018 Pachino (SR)',
			'status'  => 'operational',
		),
		'napoli'   => array(
			'name'    => 'Napoli',
			'region'  => 'NA',
			'address' => '',
			'status'  => 'activation_pending',
		),
	);

	/** Filter the office source from deployment code without database writes. */
	$filtered = apply_filters( 'mediacon_one_offices', $offices );
	return is_array( $filtered ) ? $filtered : $offices;
}

/**
 * Resolve a configured category to its saved WordPress term.
 *
 * @param array<string,string> $definition Resource definition.
 */
function mediacon_one_resolve_resource( array $definition ): ?WP_Term {
	if ( 'category' !== ( $definition['type'] ?? '' ) ) {
		return null;
	}

	$term = get_term_by( 'slug', sanitize_title( (string) ( $definition['path'] ?? '' ) ), 'category' );
	return $term instanceof WP_Term ? $term : null;
}

/**
 * Resolve a configured page, including the assigned static front page.
 *
 * @param array<string,string> $definition Resource definition.
 */
function mediacon_one_resolve_page( array $definition ): ?WP_Post {
	if ( 'front_page' === ( $definition['type'] ?? '' ) ) {
		$front_id = (int) get_option( 'page_on_front', 0 );
		if ( 0 < $front_id ) {
			$page = get_post( $front_id );
			return $page instanceof WP_Post ? $page : null;
		}
	}

	if ( ! in_array( $definition['type'] ?? '', array( 'page', 'front_page' ), true ) ) {
		return null;
	}

	$page = get_page_by_path( sanitize_title( (string) ( $definition['path'] ?? '' ) ) );
	return $page instanceof WP_Post ? $page : null;
}

/** Return the configured key for a saved page, or an empty string. */
function mediacon_one_current_page_key(): string {
	if ( ! is_page() && ! is_front_page() ) {
		return '';
	}

	$current_id = get_queried_object_id();
	foreach ( mediacon_one_page_map() as $key => $resource ) {
		$page = mediacon_one_resolve_page( $resource );
		if ( $page instanceof WP_Post && $current_id === (int) $page->ID ) {
			return (string) $key;
		}
	}

	return '';
}

/** Return the configured key for the current category archive. */
function mediacon_one_current_archive_key(): string {
	if ( ! is_category() ) {
		return '';
	}

	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	foreach ( mediacon_one_page_map() as $key => $definition ) {
		if ( 'category' === ( $definition['type'] ?? '' ) && (string) ( $definition['path'] ?? '' ) === (string) $term->slug ) {
			return (string) $key;
		}
	}

	return '';
}

/**
 * Enforce each configured rendering source after optional plugin routing.
 *
 * @param string $template Template selected by WordPress and plugins.
 */
function mediacon_one_enforce_rendering_source( string $template ): string {
	$page_key = mediacon_one_current_page_key();
	if ( '' !== $page_key ) {
		$definition = mediacon_one_page_map()[ $page_key ] ?? array();
		$renderer   = (string) ( $definition['renderer'] ?? 'wordpress' ); // phpcs:ignore WordPress.WP.CapitalPDangit.MisspelledInText -- Stable configuration value.
		if ( 'enterprise_preventivo' === $renderer && mediacon_one_preventivo_owns_page() ) {
			return $template;
		}
		if ( 'front_page' !== ( $definition['type'] ?? '' ) && in_array( $renderer, array( 'wordpress', 'enterprise_preventivo' ), true ) ) {
			$fallback = MEDIACON_ONE_PATH . 'page.php';
			return is_readable( $fallback ) ? $fallback : $template;
		}
	}

	$archive_key = mediacon_one_current_archive_key();
	if ( '' !== $archive_key && 'wordpress_archive' === ( mediacon_one_page_map()[ $archive_key ]['renderer'] ?? '' ) ) {
		$fallback = MEDIACON_ONE_PATH . 'archive.php';
		return is_readable( $fallback ) ? $fallback : $template;
	}

	return $template;
}
add_filter( 'template_include', 'mediacon_one_enforce_rendering_source', 120 );
