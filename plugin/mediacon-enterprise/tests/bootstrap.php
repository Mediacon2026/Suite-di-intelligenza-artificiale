<?php
/**
 * Test bootstrap and minimal WordPress function doubles.
 *
 * @package MediaconEnterprise
 */

define( 'ABSPATH', __DIR__ . '/wordpress/' );
define( 'OBJECT', 'OBJECT' );
define( 'MEDIACON_ENTERPRISE_PATH', dirname( __DIR__ ) . '/' );
define( 'MEDIACON_ENTERPRISE_URL', 'https://example.test/wp-content/plugins/mediacon-enterprise/' );
define( 'MEDIACON_ENTERPRISE_VERSION', '0.7.0' );
define( 'MEDIACON_ENTERPRISE_FILE', dirname( __DIR__ ) . '/mediacon-enterprise.php' );

$GLOBALS['mediacon_test_options'] = array();
$GLOBALS['mediacon_test_is_page'] = false;
$GLOBALS['mediacon_test_page_slug'] = '';
$GLOBALS['mediacon_test_page_id'] = 0;
$GLOBALS['mediacon_test_pages']   = array();
$GLOBALS['mediacon_test_is_home'] = false;
$GLOBALS['mediacon_test_is_search'] = false;
$GLOBALS['mediacon_test_transients'] = array();
$GLOBALS['mediacon_test_actions'] = array();
$GLOBALS['mediacon_test_filters'] = array();
$GLOBALS['mediacon_test_styles'] = array();
$GLOBALS['mediacon_test_scripts'] = array();
$GLOBALS['mediacon_test_enqueued_styles'] = array();
$GLOBALS['mediacon_test_enqueued_scripts'] = array();
$GLOBALS['mediacon_test_plugins'] = array();

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! class_exists( 'WP_Post' ) ) {
	/** Test post double. */
	class WP_Post {
		/** Post ID. @var int */
		public int $ID;

		/** @param int $id Post ID. */
		public function __construct( int $id = 0 ) {
			$this->ID = $id;
		}
	}
}

if ( ! class_exists( 'WP_Term' ) ) {
	/** Test term double. */
	class WP_Term {
		/** @param int $term_id Term ID. @param string $name Term name. */
		public function __construct( public int $term_id = 0, public string $name = '' ) {}
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	/** Test query double. */
	class WP_Query {
		/** Create the query double. @param array<string,mixed> $args Query arguments. */
		public function __construct( public array $args = array() ) {}
	}
}

/** @param string $key Option name. @param mixed $fallback Fallback. @return mixed */
function get_option( string $key, mixed $fallback = false ): mixed {
	return $GLOBALS['mediacon_test_options'][ $key ] ?? $fallback;
}

/** @param string $key Option name. @param mixed $value Value. @return bool */
function update_option( string $key, mixed $value, bool $autoload = true ): bool {
	$GLOBALS['mediacon_test_options'][ $key ] = $value;
	return true;
}

/** @param string $key Transient key. @return mixed */
function get_transient( string $key ): mixed {
	return $GLOBALS['mediacon_test_transients'][ $key ] ?? false;
}

/** @param string $key Transient key. @param mixed $value Value. @param int $ttl TTL. @return bool */
function set_transient( string $key, mixed $value, int $ttl ): bool {
	$GLOBALS['mediacon_test_transients'][ $key ] = $value;
	return true;
}

/** @param mixed $value Value. @return string|false */
function wp_json_encode( mixed $value ): string|false {
	return json_encode( $value );
}

/** @param array<string,mixed> $args Values. @param array<string,mixed> $defaults Defaults. @return array<string,mixed> */
function wp_parse_args( array $args, array $defaults = array() ): array {
	return array_merge( $defaults, $args );
}

/** @param string $value Raw key. @return string */
function sanitize_key( string $value ): string {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) ?? '' );
}

/** @param string $value Raw title. @return string */
function sanitize_title( string $value ): string {
	return sanitize_key( str_replace( ' ', '-', $value ) );
}

/** @param string $value Raw text. @return string */
function sanitize_text_field( string $value ): string {
	return trim( strip_tags( $value ) );
}

/** @param mixed $value Raw number. @return int */
function absint( mixed $value ): int {
	return abs( (int) $value );
}

/** @param mixed $value Slashed value. @return mixed */
function wp_unslash( mixed $value ): mixed {
	return $value;
}

/** @param int|string|array<int|string> $page Page identifier. @return bool */
function is_page( int|string|array $page = '' ): bool {
	if ( '' === $page || array() === $page ) {
		return (bool) $GLOBALS['mediacon_test_is_page'];
	}

	return (bool) $GLOBALS['mediacon_test_is_page'] && (string) $page === (string) $GLOBALS['mediacon_test_page_slug'];
}

/** @return bool */
function is_home(): bool {
	return (bool) $GLOBALS['mediacon_test_is_home'];
}

/** @return bool */
function is_search(): bool {
	return (bool) $GLOBALS['mediacon_test_is_search'];
}

/** @return bool */
function is_category(): bool {
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
	return (int) $GLOBALS['mediacon_test_page_id'];
}

/** @param string $path Page path. @return WP_Post|null */
function get_page_by_path( string $path ): ?WP_Post {
	return $GLOBALS['mediacon_test_pages'][ $path ] ?? null;
}

/** @return WP_Term|null */
function get_term(): ?WP_Term {
	return null;
}

/** @return WP_Term|null */
function get_category_by_slug(): ?WP_Term {
	return null;
}

/** @return string */
function get_term_link(): string {
	return 'https://example.test/category/';
}

/** @return bool */
function is_wp_error(): bool {
	return false;
}

/** @param string $path Path. @return string */
function home_url( string $path = '/' ): string {
	return 'https://example.test' . $path;
}

/** @param int $id Page ID. @return string */
function get_permalink( int $id ): string {
	return 'https://example.test/?page_id=' . (string) $id;
}

/** @return int|string */
function get_query_var(): int|string {
	return 1;
}

/** @param string $value Text. @return string */
function wp_strip_all_tags( string $value ): string {
	return strip_tags( $value );
}

/** @param string $value Text. @param int $length Length. @param string $more Suffix. @return string */
function wp_html_excerpt( string $value, int $length, string $more = '' ): string {
	return strlen( $value ) > $length ? substr( $value, 0, $length ) . $more : $value;
}

/** @param string $value Text. @return string */
function __( string $value ): string {
	return $value;
}

/** @param string $value Text. @return string */
function esc_html( string $value ): string {
	return $value;
}

/** @param string $value Text. @return string */
function esc_html__( string $value ): string {
	return $value;
}

/** @param string $value URL. @return string */
function esc_url( string $value ): string {
	return $value;
}

/** @param string $value HTML. @param array<string,array<string,bool>> $allowed Allowed tags. @return string */
function wp_kses( string $value, array $allowed ): string {
	$tags = '';
	foreach ( array_keys( $allowed ) as $tag ) {
		$tags .= '<' . $tag . '>';
	}
	return strip_tags( $value, $tags );
}

/** @param string $field Site field. @return string */
function get_bloginfo( string $field = '' ): string {
	return 'Mediacon Test';
}

/** @param string $hook Hook name. @param callable $callback Callback. @param int $priority Priority. @param int $accepted_args Accepted arguments. @return void */
function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['mediacon_test_actions'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

/** @param string $hook Hook name. @param callable $callback Callback. @param int $priority Priority. @param int $accepted_args Accepted arguments. @return void */
function add_filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
	$GLOBALS['mediacon_test_filters'][ $hook ][] = compact( 'callback', 'priority', 'accepted_args' );
}

/** @param string $hook Hook name. @param mixed ...$args Hook arguments. @return void */
function do_action( string $hook, mixed ...$args ): void {
	foreach ( $GLOBALS['mediacon_test_actions'][ $hook ] ?? array() as $registration ) {
		$registration['callback']( ...array_slice( $args, 0, $registration['accepted_args'] ) );
	}
}

/** @param string $hook Hook name. @param mixed $value Filtered value. @param mixed ...$args Extra arguments. @return mixed */
function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
	foreach ( $GLOBALS['mediacon_test_filters'][ $hook ] ?? array() as $registration ) {
		$value = $registration['callback']( $value, ...array_slice( $args, 0, max( 0, $registration['accepted_args'] - 1 ) ) );
	}
	return $value;
}

/** @param string $handle Handle. @param string $src URL. @param array<int,string> $deps Dependencies. @param string|bool|null $version Version. @return bool */
function wp_register_style( string $handle, string $src, array $deps = array(), string|bool|null $version = false ): bool {
	$GLOBALS['mediacon_test_styles'][ $handle ] = compact( 'src', 'deps', 'version' );
	return true;
}

/** @param string $handle Handle. @param string $src URL. @param array<int,string> $deps Dependencies. @param string|bool|null $version Version. @param bool $footer Footer. @return bool */
function wp_register_script( string $handle, string $src, array $deps = array(), string|bool|null $version = false, bool $footer = false ): bool {
	$GLOBALS['mediacon_test_scripts'][ $handle ] = compact( 'src', 'deps', 'version', 'footer' );
	return true;
}

/** @param string $handle Handle. @return void */
function wp_enqueue_style( string $handle ): void {
	$GLOBALS['mediacon_test_enqueued_styles'][] = $handle;
}

/** @param string $handle Handle. @return void */
function wp_enqueue_script( string $handle ): void {
	$GLOBALS['mediacon_test_enqueued_scripts'][] = $handle;
}

/** @return array<string,array<string,mixed>> */
function get_plugins(): array {
	return $GLOBALS['mediacon_test_plugins'];
}

/** @param string $file Plugin basename. @return bool */
function is_plugin_active( string $file ): bool {
	return in_array( $file, get_option( 'active_plugins', array() ), true );
}
