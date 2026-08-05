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
define( 'MEDIACON_ENTERPRISE_VERSION', '0.3.0' );

$GLOBALS['mediacon_test_options'] = array();
$GLOBALS['mediacon_test_is_page'] = false;
$GLOBALS['mediacon_test_page_id'] = 0;
$GLOBALS['mediacon_test_pages']   = array();

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
function update_option( string $key, mixed $value ): bool {
	$GLOBALS['mediacon_test_options'][ $key ] = $value;
	return true;
}

/** @param array<string,mixed> $args Values. @param array<string,mixed> $defaults Defaults. @return array<string,mixed> */
function wp_parse_args( array $args, array $defaults = array() ): array {
	return array_merge( $defaults, $args );
}

/** @param string $value Raw key. @return string */
function sanitize_key( string $value ): string {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $value ) ?? '' );
}

/** @param mixed $value Raw number. @return int */
function absint( mixed $value ): int {
	return abs( (int) $value );
}

/** @param mixed $value Slashed value. @return mixed */
function wp_unslash( mixed $value ): mixed {
	return $value;
}

/** @return bool */
function is_page(): bool {
	return (bool) $GLOBALS['mediacon_test_is_page'];
}

/** @return int */
function get_queried_object_id(): int {
	return (int) $GLOBALS['mediacon_test_page_id'];
}

/** @param string $path Page path. @return WP_Post|null */
function get_page_by_path( string $path ): ?WP_Post {
	return $GLOBALS['mediacon_test_pages'][ $path ] ?? null;
}

/** @param int $id Page ID. @return string */
function get_permalink( int $id ): string {
	return 'https://example.test/?page_id=' . (string) $id;
}

/** @param string $value Text. @return string */
function __( string $value ): string {
	return $value;
}

/** @param string $value Text. @return string */
function esc_html( string $value ): string {
	return $value;
}

/** @return void */
function do_action(): void {}
