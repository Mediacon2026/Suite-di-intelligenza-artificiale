<?php
/**
 * Process-isolated Enterprise and bridge smoke scenarios.
 *
 * @package MediaconEnterprise
 */

$scenario = $argv[1] ?? '';
if ( ! in_array( $scenario, array( 'both', 'enterprise-only', 'bridge-only', 'collisions', 'disabled' ), true ) ) {
	fwrite( STDERR, "Unknown compatibility smoke scenario.\n" );
	exit( 2 );
}

define( 'ABSPATH', __DIR__ . '/wordpress/' );
$GLOBALS['compat_smoke_actions'] = array();
$GLOBALS['compat_smoke_options'] = array();
if ( 'disabled' === $scenario ) {
	$GLOBALS['compat_smoke_options']['mediacon_enterprise_settings'] = array(
		'enabled_modules'       => array( 'mediation' ),
		'compatibility_enabled' => false,
	);
}

/** @param string $file Plugin file. @return string */
function plugin_dir_path( string $file ): string {
	return dirname( $file ) . DIRECTORY_SEPARATOR; }
/** @return string */
function plugin_dir_url(): string {
	return 'https://example.test/wp-content/plugins/mediacon-enterprise/'; }
/** @param string $hook Hook. @param callable $callback Callback. @param int $priority Priority. @param int $accepted_args Accepted arguments. @return void */
function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['compat_smoke_actions'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' ); }
/** @return void */
function add_filter(): void {}
/** @param string $hook Hook. @param mixed $value Value. @return mixed */
function apply_filters( string $hook, mixed $value ): mixed {
	return $value; }
/** @return void */
function register_activation_hook(): void {}
/** @return void */
function register_deactivation_hook(): void {}
/** @param string $key Option. @param mixed $fallback Fallback. @return mixed */
function get_option( string $key, mixed $fallback = false ): mixed {
	return $GLOBALS['compat_smoke_options'][ $key ] ?? $fallback; }
/** @param array<string,mixed> $args Values. @param array<string,mixed> $defaults Defaults. @return array<string,mixed> */
function wp_parse_args( array $args, array $defaults = array() ): array {
	return array_merge( $defaults, $args ); }
/** @param string $value Value. @return string */
function sanitize_key( string $value ): string {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) ?? '' ); }
/** @param string $value Value. @return string */
function sanitize_text_field( string $value ): string {
	return trim( strip_tags( $value ) ); }
/** @param mixed $value Value. @return int */
function absint( mixed $value ): int {
	return abs( (int) $value ); }
/** @param string $hook Hook. @param mixed ...$args Arguments. @return void */
function do_action( string $hook, mixed ...$args ): void {
	foreach ( $GLOBALS['compat_smoke_actions'][ $hook ] ?? array() as $item ) {
		$item['callback']( ...array_slice( $args, 0, $item['accepted_args'] ) ); } }
/** @return bool */
function current_user_can(): bool {
	return true; }
/** @param string $text Text. @return string */
function esc_html__( string $text ): string {
	return $text; }

if ( 'collisions' === $scenario ) {
	define( 'MEDIACON_DESIGN_CORE_VERSION', 'legacy-value' );
	/** @return string */
	function mediacon_design_core_logo_url(): string {
		return 'legacy-logo'; }
}

if ( in_array( $scenario, array( 'both', 'enterprise-only', 'collisions', 'disabled' ), true ) ) {
	require dirname( __DIR__ ) . '/mediacon-enterprise.php';
}
if ( in_array( $scenario, array( 'both', 'bridge-only', 'disabled' ), true ) ) {
	require dirname( __DIR__ ) . '/compatibility/mediacon-design-core/mediacon-design-core.php';
}

$callbacks = $GLOBALS['compat_smoke_actions']['plugins_loaded'] ?? array();
usort( $callbacks, static fn ( array $left, array $right ): int => $left['priority'] <=> $right['priority'] );
foreach ( $callbacks as $item ) {
	$item['callback']();
}

if ( 'both' === $scenario && empty( $GLOBALS['mediacon_design_core_bridge_ready'] ) ) {
	fwrite( STDERR, "Bridge did not connect to Enterprise.\n" );
	exit( 1 );
}
if ( 'bridge-only' === $scenario && ! empty( $GLOBALS['mediacon_design_core_bridge_ready'] ) ) {
	fwrite( STDERR, "Bridge reported ready without Enterprise.\n" );
	exit( 1 );
}
if ( 'disabled' === $scenario && ( ! empty( $GLOBALS['mediacon_design_core_bridge_ready'] ) || function_exists( 'mediacon_design_core_path' ) ) ) {
	fwrite( STDERR, "Disabled compatibility module exposed runtime contracts.\n" );
	exit( 1 );
}
if ( in_array( $scenario, array( 'enterprise-only', 'both', 'collisions' ), true ) && ! function_exists( 'mediacon_design_core_path' ) ) {
	fwrite( STDERR, "Compatibility functions were not exposed.\n" );
	exit( 1 );
}
if ( 'collisions' === $scenario && ( 'legacy-value' !== MEDIACON_DESIGN_CORE_VERSION || 'legacy-logo' !== mediacon_design_core_logo_url() ) ) {
	fwrite( STDERR, "Existing contracts were overwritten.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Compatibility scenario {$scenario} completed without fatal errors.\n" );
