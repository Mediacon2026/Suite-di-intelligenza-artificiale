<?php
/**
 * WordPress hook manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

use InvalidArgumentException;

/**
 * Collects and registers WordPress actions and filters.
 */
final class HookManager {

	/**
	 * Pending hooks.
	 *
	 * @var array<int,array{type:string,hook:string,callback:callable,priority:int,args:int}>
	 */
	private array $hooks = array();

	/**
	 * Whether hooks have been registered.
	 *
	 * @var bool
	 */
	private bool $registered = false;

	/**
	 * Queue an action.
	 *
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Hook callback.
	 * @param int      $priority      Hook priority.
	 * @param int      $accepted_args Accepted argument count.
	 * @return void
	 */
	public function action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->add( 'action', $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Queue a filter.
	 *
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Hook callback.
	 * @param int      $priority      Hook priority.
	 * @param int      $accepted_args Accepted argument count.
	 * @return void
	 */
	public function filter( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->add( 'filter', $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Register every queued hook with WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( $this->registered ) {
			return;
		}

		foreach ( $this->hooks as $hook ) {
			if ( 'action' === $hook['type'] ) {
				add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['args'] );
			} else {
				add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['args'] );
			}
		}

		$this->registered = true;
	}

	/**
	 * Add a hook definition.
	 *
	 * @param string   $type          Hook type.
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Hook callback.
	 * @param int      $priority      Hook priority.
	 * @param int      $accepted_args Accepted argument count.
	 * @return void
	 * @throws InvalidArgumentException When hooks are already registered or arguments are invalid.
	 */
	private function add( string $type, string $hook, callable $callback, int $priority, int $accepted_args ): void {
		if ( $this->registered ) {
			throw new InvalidArgumentException( 'Hooks cannot be queued after registration.' );
		}

		if ( '' === $hook || 0 > $accepted_args ) {
			throw new InvalidArgumentException( 'A valid hook name and argument count are required.' );
		}

		$this->hooks[] = array(
			'type'     => $type,
			'hook'     => $hook,
			'callback' => $callback,
			'priority' => $priority,
			'args'     => $accepted_args,
		);
	}
}
