<?php
/**
 * Standalone plugin activation/bootstrap smoke test.
 *
 * @package MediaconEnterprise
 */

define( 'ABSPATH', __DIR__ . '/wordpress/' );

$GLOBALS['mediacon_smoke_actions'] = array();

/** @param string $file Plugin file. @return string */
function plugin_dir_path( string $file ): string {
	return dirname( $file ) . DIRECTORY_SEPARATOR;
}

/** @return string */
function plugin_dir_url(): string {
	return 'https://example.test/wp-content/plugins/mediacon-enterprise/';
}

/** @param string $hook Hook name. @param callable $callback Callback. @return void */
function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['mediacon_smoke_actions'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

/** @return void */
function add_filter(): void {}

/** @param string $hook Hook. @param mixed $value Value. @return mixed */
function apply_filters( string $hook, mixed $value ): mixed {
	return $value;
}

/** @return void */
function register_activation_hook(): void {}

/** @return void */
function register_deactivation_hook(): void {}

/** @param string $key Option key. @param mixed $fallback Fallback value. @return mixed */
function get_option( string $key, mixed $fallback = false ): mixed {
	return $fallback;
}

/** @param array<string,mixed> $args Values. @param array<string,mixed> $defaults Defaults. @return array<string,mixed> */
function wp_parse_args( array $args, array $defaults = array() ): array {
	return array_merge( $defaults, $args );
}

/** @param string $value Raw key. @return string */
function sanitize_key( string $value ): string {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) ?? '' );
}

/** @return void */
function do_action(): void {}

require dirname( __DIR__ ) . '/mediacon-enterprise.php';

foreach ( $GLOBALS['mediacon_smoke_actions']['plugins_loaded'] ?? array() as $registration ) {
	$registration['callback']();
}

$plugin = Mediacon\Enterprise\Core\Plugin::instance();
if ( ! $plugin->container()->has( Mediacon\Enterprise\Core\ModuleManager::class ) ) {
	fwrite( STDERR, "Module manager was not registered.\n" );
	exit( 1 );
}

fwrite( STDOUT, "Plugin bootstrap completed without fatal errors.\n" );
