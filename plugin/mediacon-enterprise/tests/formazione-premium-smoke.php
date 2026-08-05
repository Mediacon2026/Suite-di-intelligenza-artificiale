<?php
/**
 * Regression smoke for the real Mediacon Formazione Premium archive.
 *
 * @package MediaconEnterprise
 */

if ( 2 > $argc || ! is_readable( $argv[1] ) ) {
	fwrite( STDERR, "Usage: php formazione-premium-smoke.php <legacy-archive.zip>\n" );
	exit( 2 );
}

$archive = realpath( $argv[1] );
$sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mediacon-formation-smoke-' . (string) getmypid();

if ( false === $archive || ! mkdir( $sandbox, 0700, true ) ) {
	fwrite( STDERR, "Unable to create the isolated smoke directory.\n" );
	exit( 2 );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $archive ) || ! $zip->extractTo( $sandbox ) ) {
	fwrite( STDERR, "Unable to extract the legacy archive.\n" );
	exit( 2 );
}
$zip->close();

$legacy_files = glob( $sandbox . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.php' );
$legacy_file  = is_array( $legacy_files ) ? ( $legacy_files[0] ?? '' ) : '';
if ( '' === $legacy_file || ! is_readable( $legacy_file ) ) {
	fwrite( STDERR, "The legacy plugin entry point was not found.\n" );
	exit( 2 );
}

define( 'ABSPATH', $sandbox . DIRECTORY_SEPARATOR . 'wordpress' . DIRECTORY_SEPARATOR );
define( 'OBJECT', 'OBJECT' );

$GLOBALS['mediacon_smoke_actions']          = array();
$GLOBALS['mediacon_smoke_filters']          = array();
$GLOBALS['mediacon_smoke_styles']           = array();
$GLOBALS['mediacon_smoke_scripts']          = array();
$GLOBALS['mediacon_smoke_enqueued_styles']  = array();
$GLOBALS['mediacon_smoke_enqueued_scripts'] = array();
$GLOBALS['mediacon_smoke_is_page']          = false;

/** @param string $file Plugin file. @return string */
function plugin_dir_path( string $file ): string {
	return dirname( $file ) . DIRECTORY_SEPARATOR;
}

/** @param string $file Plugin file. @return string */
function plugin_dir_url( string $file = '' ): string {
	return 'https://example.test/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
}

/** @return void */
function register_activation_hook(): void {}

/** @return void */
function register_deactivation_hook(): void {}

/** @param string $hook Hook. @param callable $callback Callback. @param int $priority Priority. @param int $accepted_args Accepted arguments. @return void */
function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['mediacon_smoke_actions'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

/** @param string $hook Hook. @param callable $callback Callback. @param int $priority Priority. @param int $accepted_args Accepted arguments. @return void */
function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['mediacon_smoke_filters'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

/** @param string $hook Hook. @param mixed ...$args Arguments. @return void */
function do_action( string $hook, mixed ...$args ): void {
	$callbacks = $GLOBALS['mediacon_smoke_actions'][ $hook ] ?? array();
	usort( $callbacks, static fn ( array $left, array $right ): int => $left['priority'] <=> $right['priority'] );
	foreach ( $callbacks as $registration ) {
		$registration['callback']( ...array_slice( $args, 0, $registration['accepted_args'] ) );
	}
}

/** @param string $hook Hook. @param mixed $value Value. @param mixed ...$args Arguments. @return mixed */
function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
	$callbacks = $GLOBALS['mediacon_smoke_filters'][ $hook ] ?? array();
	usort( $callbacks, static fn ( array $left, array $right ): int => $left['priority'] <=> $right['priority'] );
	foreach ( $callbacks as $registration ) {
		$value = $registration['callback']( $value, ...array_slice( $args, 0, max( 0, $registration['accepted_args'] - 1 ) ) );
	}
	return $value;
}

/** @param string $key Option. @param mixed $fallback Fallback. @return mixed */
function get_option( string $key, mixed $fallback = false ): mixed {
	return $fallback;
}

/** @param array<string,mixed> $args Values. @param array<string,mixed> $defaults Defaults. @return array<string,mixed> */
function wp_parse_args( array $args, array $defaults = array() ): array {
	return array_merge( $defaults, $args );
}

/** @param string $value Key. @return string */
function sanitize_key( string $value ): string {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) ?? '' );
}

/** @param string $value Title. @return string */
function sanitize_title( string $value ): string {
	return sanitize_key( str_replace( ' ', '-', $value ) );
}

/** @param string $value Text. @return string */
function sanitize_text_field( string $value ): string {
	return trim( strip_tags( $value ) );
}

/** @param mixed $value Number. @return int */
function absint( mixed $value ): int {
	return abs( (int) $value );
}

/** @param string $value Text. @return string */
function __( string $value ): string {
	return $value;
}

/** @param string $value Text. @return string */
function esc_html__( string $value ): string {
	return $value;
}

/** @param string $value Text. @return string */
function esc_html( string $value ): string {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

/** @param string $value URL. @return string */
function esc_url( string $value ): string {
	return $value;
}

/** @param string $single Singular. @param string $plural Plural. @param int $number Number. @return string */
function _n( string $single, string $plural, int $number ): string {
	return 1 === $number ? $single : $plural;
}

/** @param string $value HTML. @param array<string,array<string,bool>> $allowed Allowed tags. @return string */
function wp_kses( string $value, array $allowed ): string {
	$tags = implode( '', array_map( static fn ( string $tag ): string => '<' . $tag . '>', array_keys( $allowed ) ) );
	return strip_tags( $value, $tags );
}

/** @return bool */
function current_user_can(): bool {
	return true;
}

/** @param int|string|array<int|string> $page Page. @return bool */
function is_page( int|string|array $page = '' ): bool {
	return (bool) $GLOBALS['mediacon_smoke_is_page'] && ( '' === $page || 'formazione' === $page );
}

/** @return bool */
function is_category(): bool {
	return false;
}

/** @return bool */
function is_home(): bool {
	return false;
}

/** @return bool */
function is_search(): bool {
	return false;
}

/** @return bool */
function is_singular(): bool {
	return false;
}

/** @return bool */
function has_category(): bool {
	return false;
}

/** @return int */
function get_queried_object_id(): int {
	return 0;
}

/** @return null */
function get_page_by_path(): null {
	return null;
}

/** @return object */
function get_category_by_slug(): object {
	return (object) array( 'term_id' => 1 );
}

/** @return string */
function get_category_link(): string {
	return 'https://example.test/category/prossimi-corsi/';
}

/** @return void */
function get_header(): void {}

/** @return void */
function wp_footer(): void {}

/** @param string $path Path. @return string */
function home_url( string $path = '/' ): string {
	return 'https://example.test' . $path;
}

/** @return string */
function get_bloginfo(): string {
	return 'Mediacon Test';
}

/** @param string $handle Handle. @param string $src Source. @param array<int,string> $deps Dependencies. @param string|bool|null $version Version. @return bool */
function wp_register_style( string $handle, string $src, array $deps = array(), string|bool|null $version = false ): bool {
	$GLOBALS['mediacon_smoke_styles'][ $handle ] = compact( 'src', 'deps', 'version' );
	return true;
}

/** @param string $handle Handle. @param string $src Source. @param array<int,string> $deps Dependencies. @param string|bool|null $version Version. @param bool $footer Footer. @return bool */
function wp_register_script( string $handle, string $src, array $deps = array(), string|bool|null $version = false, bool $footer = false ): bool {
	$GLOBALS['mediacon_smoke_scripts'][ $handle ] = compact( 'src', 'deps', 'version', 'footer' );
	return true;
}

/** @param string $handle Handle. @param string $src Source. @param array<int,string> $deps Dependencies. @param string|bool|null $version Version. @return void */
function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), string|bool|null $version = false ): void {
	$GLOBALS['mediacon_smoke_enqueued_styles'][] = $handle;
	if ( '' !== $src ) {
		wp_register_style( $handle, $src, $deps, $version );
	}
}

/** @param string $handle Handle. @param string $src Source. @param array<int,string> $deps Dependencies. @param string|bool|null $version Version. @param bool $footer Footer. @return void */
function wp_enqueue_script( string $handle, string $src = '', array $deps = array(), string|bool|null $version = false, bool $footer = false ): void {
	$GLOBALS['mediacon_smoke_enqueued_scripts'][] = $handle;
	if ( '' !== $src ) {
		wp_register_script( $handle, $src, $deps, $version, $footer );
	}
}

/** @param string $handle Handle. @param string $status Status. @return bool */
function wp_style_is( string $handle, string $status ): bool {
	return 'registered' === $status ? isset( $GLOBALS['mediacon_smoke_styles'][ $handle ] ) : in_array( $handle, $GLOBALS['mediacon_smoke_enqueued_styles'], true );
}

/** @param string $handle Handle. @param string $status Status. @return bool */
function wp_script_is( string $handle, string $status ): bool {
	return 'registered' === $status ? isset( $GLOBALS['mediacon_smoke_scripts'][ $handle ] ) : in_array( $handle, $GLOBALS['mediacon_smoke_enqueued_scripts'], true );
}

/** @return void */
function add_shortcode(): void {}

require dirname( __DIR__ ) . '/mediacon-enterprise.php';
require dirname( __DIR__ ) . '/compatibility/mediacon-design-core/mediacon-design-core.php';
require $legacy_file;

do_action( 'plugins_loaded' );

if ( ! function_exists( 'mdc_register_page' ) || ! defined( 'MDC_URL' ) || empty( $GLOBALS['mediacon_design_core_bridge_ready'] ) ) {
	fwrite( STDERR, "The documented Design Core contracts were not activated.\n" );
	exit( 1 );
}

ob_start();
do_action( 'admin_notices' );
$notices = (string) ob_get_clean();
if ( '' !== trim( $notices ) ) {
	fwrite( STDERR, "Unexpected activation notice: {$notices}\n" );
	exit( 1 );
}

$assets = Mediacon\Enterprise\Core\Plugin::instance()->container()->get( Mediacon\Enterprise\Compatibility\LegacyAssetBridge::class );
$assets->registerDefaults();
if ( MDC_URL . 'assets/css/core.css' !== $GLOBALS['mediacon_smoke_styles']['mediacon-design-core']['src'] || MDC_URL . 'assets/js/core.js' !== $GLOBALS['mediacon_smoke_scripts']['mediacon-design-core']['src'] ) {
	fwrite( STDERR, "The compatibility asset handles resolve to unexpected sources.\n" );
	exit( 1 );
}

$GLOBALS['mediacon_smoke_is_page'] = true;
$template                          = apply_filters( 'template_include', 'theme.php' );
if ( realpath( dirname( $legacy_file ) . '/templates/formazione.php' ) !== $template ) {
	fwrite( STDERR, "The Enterprise page adapter did not resolve the legacy template.\n" );
	exit( 1 );
}

ob_start();
require $template;
$rendered = (string) ob_get_clean();
if ( ! str_contains( $rendered, 'class="mdc-page-hero"' ) || ! str_contains( $rendered, 'class="mdc-footer"' ) ) {
	fwrite( STDERR, "The legacy template did not render the Enterprise design components.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Formazione Premium activated and rendered without notices or fatal errors.\n" );
