<?php
/**
 * Frontend asset loading.
 *
 * @package MediaconOne
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a cache-safe local asset version.
 *
 * @param string $relative_path Theme-relative asset path.
 */
function mediacon_one_asset_version( string $relative_path ): string {
	$file = MEDIACON_ONE_PATH . ltrim( $relative_path, '/\\' );
	return is_readable( $file ) ? (string) filemtime( $file ) : MEDIACON_ONE_VERSION;
}

/** Enqueue small, dependency-free theme assets. */
function mediacon_one_enqueue_assets(): void {
	$styles       = array(
		'mediacon-one-tokens'     => 'assets/css/tokens.css',
		'mediacon-one-base'       => 'assets/css/base.css',
		'mediacon-one-layout'     => 'assets/css/layout.css',
		'mediacon-one-components' => 'assets/css/components.css',
	);
	$dependencies = array();
	foreach ( $styles as $handle => $path ) {
		wp_enqueue_style( $handle, MEDIACON_ONE_URL . $path, $dependencies, mediacon_one_asset_version( $path ) );
		$dependencies = array( $handle );
	}

	wp_enqueue_script( 'mediacon-one-navigation', MEDIACON_ONE_URL . 'assets/js/navigation.js', array(), mediacon_one_asset_version( 'assets/js/navigation.js' ), true );
	if ( is_front_page() || is_page() ) {
		wp_enqueue_script( 'mediacon-one-accordion', MEDIACON_ONE_URL . 'assets/js/accordion.js', array(), mediacon_one_asset_version( 'assets/js/accordion.js' ), true );
	}
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'mediacon_one_enqueue_assets' );

/**
 * Defer the theme's independent scripts.
 *
 * @param string $tag    Script element.
 * @param string $handle Registered script handle.
 */
function mediacon_one_defer_scripts( string $tag, string $handle ): string {
	if ( in_array( $handle, array( 'mediacon-one-navigation', 'mediacon-one-accordion' ), true ) && ! str_contains( $tag, ' defer' ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'mediacon_one_defer_scripts', 10, 2 );
