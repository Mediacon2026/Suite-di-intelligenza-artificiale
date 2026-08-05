<?php
/**
 * Dependency injection container.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Stores service definitions and resolves shared services lazily.
 */
final class Container {

	/**
	 * Service definitions.
	 *
	 * @var array<string,Closure(self):mixed>
	 */
	private array $definitions = array();

	/**
	 * Resolved shared services.
	 *
	 * @var array<string,mixed>
	 */
	private array $instances = array();

	/**
	 * Services currently being resolved.
	 *
	 * @var array<string,bool>
	 */
	private array $resolving = array();

	/**
	 * Register a shared service.
	 *
	 * @param string  $id      Service identifier.
	 * @param Closure $factory Service factory receiving this container.
	 * @return void
	 * @throws InvalidArgumentException When the identifier is empty.
	 */
	public function singleton( string $id, Closure $factory ): void {
		if ( '' === $id ) {
			throw new InvalidArgumentException( 'A service identifier cannot be empty.' );
		}

		$this->definitions[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	/**
	 * Store an already constructed service.
	 *
	 * @param string $id      Service identifier.
	 * @param mixed  $service Service instance.
	 * @return void
	 * @throws InvalidArgumentException When the identifier is empty.
	 */
	public function instance( string $id, mixed $service ): void {
		if ( '' === $id ) {
			throw new InvalidArgumentException( 'A service identifier cannot be empty.' );
		}

		$this->instances[ $id ] = $service;
	}

	/**
	 * Determine whether a service is registered.
	 *
	 * @param string $id Service identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->definitions[ $id ] ) || array_key_exists( $id, $this->instances );
	}

	/**
	 * Resolve a shared service.
	 *
	 * @param string $id Service identifier.
	 * @return mixed
	 * @throws RuntimeException When the service is missing or has a circular dependency.
	 */
	public function get( string $id ): mixed {
		if ( array_key_exists( $id, $this->instances ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->definitions[ $id ] ) ) {
			throw new RuntimeException( sprintf( 'Service "%s" is not registered.', esc_html( $id ) ) );
		}

		if ( isset( $this->resolving[ $id ] ) ) {
			throw new RuntimeException( sprintf( 'Circular dependency detected while resolving "%s".', esc_html( $id ) ) );
		}

		$this->resolving[ $id ] = true;

		try {
			$this->instances[ $id ] = ( $this->definitions[ $id ] )( $this );
		} finally {
			unset( $this->resolving[ $id ] );
		}

		return $this->instances[ $id ];
	}
}
