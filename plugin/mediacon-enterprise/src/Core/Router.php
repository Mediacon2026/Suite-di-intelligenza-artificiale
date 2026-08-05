<?php
/**
 * WordPress route manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

use InvalidArgumentException;

/**
 * Registers REST and authenticated admin-post routes.
 */
final class Router {

	/**
	 * REST route definitions.
	 *
	 * @var array<int,array{route:string,args:array<string,mixed>}>
	 */
	private array $rest_routes = array();

	/**
	 * Admin-post route definitions.
	 *
	 * @var array<string,callable>
	 */
	private array $admin_routes = array();

	/**
	 * Add a REST route.
	 *
	 * @param string              $route Route path beginning with a slash.
	 * @param array<string,mixed> $args  REST route arguments.
	 * @return void
	 * @throws InvalidArgumentException When required route arguments are missing.
	 */
	public function rest( string $route, array $args ): void {
		if ( ! str_starts_with( $route, '/' ) || ! isset( $args['callback'], $args['permission_callback'] ) ) {
			throw new InvalidArgumentException( 'REST routes require a path, callback, and permission callback.' );
		}

		$this->rest_routes[] = array(
			'route' => $route,
			'args'  => $args,
		);
	}

	/**
	 * Add an authenticated admin-post route.
	 *
	 * @param string   $action   Sanitized action name.
	 * @param callable $callback Route callback.
	 * @return void
	 * @throws InvalidArgumentException When the action name is empty.
	 */
	public function admin( string $action, callable $callback ): void {
		$action = sanitize_key( $action );

		if ( '' === $action ) {
			throw new InvalidArgumentException( 'Admin routes require an action name.' );
		}

		$this->admin_routes[ $action ] = $callback;
	}

	/**
	 * Register routes with WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( $this->admin_routes as $action => $callback ) {
			add_action( 'admin_post_' . $action, $callback );
		}

		add_action( 'rest_api_init', array( $this, 'registerRestRoutes' ) );
	}

	/**
	 * Register collected REST routes.
	 *
	 * @return void
	 */
	public function registerRestRoutes(): void {
		foreach ( $this->rest_routes as $route ) {
			register_rest_route( 'mediacon-enterprise/v1', $route['route'], $route['args'] );
		}
	}
}
