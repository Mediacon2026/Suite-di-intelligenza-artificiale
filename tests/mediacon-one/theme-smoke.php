<?php
/**
 * Standalone theme activation and Enterprise boundary smoke.
 *
 * @package MediaconOne
 */

$mode  = $argv[1] ?? 'inactive';
$theme = realpath( $argv[2] ?? dirname( __DIR__, 2 ) . '/themes/mediacon-one' );
if ( false === $theme || ! in_array( $mode, array( 'active', 'inactive' ), true ) ) {
	fwrite( STDERR, "Usage: php theme-smoke.php <active|inactive> [theme-directory]\n" );
	exit( 2 );
}

define( 'ABSPATH', $theme . '/wordpress/' );
if ( 'active' === $mode ) {
	define( 'MEDIACON_ENTERPRISE_VERSION', 'test' );
	eval( 'namespace Mediacon\\Enterprise\\Core; final class Plugin {}' );
}

$GLOBALS['mco_actions']  = array();
$GLOBALS['mco_supports'] = array();
$GLOBALS['mco_menus']    = array();

/** @return string */
function get_template_directory(): string {
	return $GLOBALS['mco_theme'];
}
/** @return string */
function get_template_directory_uri(): string {
	return 'https://example.test/wp-content/themes/mediacon-one';
}
/** @param string $path Path. @return string */
function trailingslashit( string $path ): string {
	return rtrim( $path, '/\\' ) . '/';
}
/** @param string $hook Hook. @param callable $callback Callback. @param int $priority Priority. @return void */
function add_action( string $hook, callable $callback, int $priority = 10 ): void {
	$GLOBALS['mco_actions'][ $hook ][ $priority ][] = $callback;
}
/** @return void */
function add_filter(): void {}
/** @param string $domain Domain. @param string $path Path. @return bool */
function load_theme_textdomain( string $domain, string $path ): bool {
	return true;
}
/** @param string $feature Feature. @param mixed ...$args Arguments. @return void */
function add_theme_support( string $feature, mixed ...$args ): void {
	$GLOBALS['mco_supports'][] = $feature;
}
/** @return void */
function add_editor_style(): void {}
/** @param array<string,string> $menus Menus. @return void */
function register_nav_menus( array $menus ): void {
	$GLOBALS['mco_menus'] = $menus;
}
/** @return void */
function add_image_size(): void {}
/** @param string $text Text. @return string */
function __( string $text ): string {
	return $text;
}
/** @param string $hook Hook. @param mixed $value Value. @return mixed */
function apply_filters( string $hook, mixed $value ): mixed {
	return $value;
}
/** @return void */
function do_action(): void {}

$GLOBALS['mco_theme'] = $theme;
require $theme . '/functions.php';

foreach ( $GLOBALS['mco_actions']['after_setup_theme'] ?? array() as $callbacks ) {
	foreach ( $callbacks as $callback ) {
		$callback();
	}
}

$expected = array( 'title-tag', 'post-thumbnails', 'responsive-embeds', 'align-wide', 'custom-logo', 'html5' );
if ( array_diff( $expected, $GLOBALS['mco_supports'] ) || array_diff( array( 'primary', 'mega', 'footer', 'social' ), array_keys( $GLOBALS['mco_menus'] ) ) ) {
	fwrite( STDERR, "Theme supports or menu locations are incomplete.\n" );
	exit( 1 );
}

$detected = mediacon_one_enterprise_active();
if ( ( 'active' === $mode ) !== $detected ) {
	fwrite( STDERR, "Unexpected Enterprise detection state.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Theme activation smoke passed with Enterprise {$mode}.\n" );
