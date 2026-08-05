<?php
/**
 * Namespaced WordPress cache manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Stores bounded module responses in WordPress transients.
 */
final class CacheManager {

	/**
	 * Read a cached value.
	 *
	 * @param string              $group Cache group.
	 * @param array<string,mixed> $parts     Cache-key components.
	 * @return mixed|null
	 */
	public function get( string $group, array $parts ): mixed {
		$value = get_transient( $this->key( $group, $parts ) );

		return false === $value ? null : $value;
	}

	/**
	 * Cache a value for a bounded duration.
	 *
	 * @param string              $group Cache group.
	 * @param array<string,mixed> $parts     Cache-key components.
	 * @param mixed               $value     Cached value.
	 * @param int                 $ttl       Duration in seconds.
	 * @return bool
	 */
	public function set( string $group, array $parts, mixed $value, int $ttl ): bool {
		return set_transient( $this->key( $group, $parts ), $value, max( 1, $ttl ) );
	}

	/**
	 * Invalidate a namespace without enumerating transient rows.
	 *
	 * @param string $group Cache group.
	 * @return void
	 */
	public function invalidate( string $group ): void {
		$option = $this->versionOption( $group );
		update_option( $option, $this->version( $group ) + 1, false );
	}

	/**
	 * Build a stable namespaced key.
	 *
	 * @param string              $group Cache group.
	 * @param array<string,mixed> $parts     Key components.
	 * @return string
	 */
	private function key( string $group, array $parts ): string {
		$payload = wp_json_encode( $parts );

		return 'mce_' . sanitize_key( $group ) . '_' . $this->version( $group ) . '_' . md5( false === $payload ? '' : $payload );
	}

	/**
	 * Read the namespace generation.
	 *
	 * @param string $group Cache group.
	 * @return int
	 */
	private function version( string $group ): int {
		return max( 1, absint( get_option( $this->versionOption( $group ), 1 ) ) );
	}

	/**
	 * Build the generation option name.
	 *
	 * @param string $group Cache group.
	 * @return string
	 */
	private function versionOption( string $group ): string {
		return 'mediacon_enterprise_cache_' . sanitize_key( $group );
	}
}
