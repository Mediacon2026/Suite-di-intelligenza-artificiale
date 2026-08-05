<?php
/**
 * Runtime registry for the documented legacy compatibility surface.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

/**
 * Records only compatibility contracts actually exposed by Enterprise.
 */
final class LegacyContractRegistry {

	/** Available constants.
	 *
	 * @var array<string,mixed>
	 */
	private array $constants = array();

	/** Available functions.
	 *
	 * @var array<string,bool>
	 */
	private array $functions = array();

	/** Available classes, interfaces, and traits.
	 *
	 * @var array<string,bool>
	 */
	private array $classes = array();

	/** Published hooks.
	 *
	 * @var array<string,string>
	 */
	private array $hooks = array();

	/** Registered assets.
	 *
	 * @var array<string,string>
	 */
	private array $assets = array();

	/** Registered page identifiers.
	 *
	 * @var array<string,int>
	 */
	private array $pages = array();

	/** Loaded templates.
	 *
	 * @var array<string,bool>
	 */
	private array $templates = array();

	/** Missing required functions.
	 *
	 * @var array<int,string>
	 */
	private array $missing_functions = array();

	/** Missing required types.
	 *
	 * @var array<int,string>
	 */
	private array $missing_classes = array();

	/** Detected collisions.
	 *
	 * @var array<int,string>
	 */
	private array $collisions = array();

	/** Safe runtime errors.
	 *
	 * @var array<int,string>
	 */
	private array $errors = array();

	/**
	 * Define a compatibility constant without replacing an existing value.
	 *
	 * @param string $name  Constant name.
	 * @param mixed  $value Constant value.
	 * @return bool
	 */
	public function defineConstant( string $name, mixed $value ): bool {
		if ( defined( $name ) ) {
			$this->collision( sprintf( 'Constant %s already exists; its original value was preserved.', $name ) );
			$this->constants[ $name ] = constant( $name );
			return false;
		}

		define( $name, $value );
		$this->constants[ $name ] = $value;
		return true;
	}

	/**
	 * Record a function availability check.
	 *
	 * @param string $name Function name.
	 * @return void
	 */
	public function functionAvailable( string $name ): void {
		$this->functions[ $name ] = function_exists( $name );
	}

	/**
	 * Record a type availability check.
	 *
	 * @param string $name Class, interface, or trait name.
	 * @return void
	 */
	public function classAvailable( string $name ): void {
		$this->classes[ $name ] = class_exists( $name ) || interface_exists( $name ) || trait_exists( $name );
	}

	/**
	 * Record a published hook.
	 *
	 * @param string $name Hook name.
	 * @param string $type Hook type.
	 * @return void
	 */
	public function hook( string $name, string $type ): void {
		$this->hooks[ $name ] = $type;
	}

	/**
	 * Record a registered asset.
	 *
	 * @param string $handle Asset handle.
	 * @param string $type   Asset type.
	 * @return void
	 */
	public function asset( string $handle, string $type ): void {
		$this->assets[ sanitize_key( $handle ) ] = $type;
	}

	/**
	 * Record a page identifier.
	 *
	 * @param string $key     Registry key.
	 * @param int    $page_id Page identifier.
	 * @return void
	 */
	public function page( string $key, int $page_id ): void {
		$this->pages[ sanitize_key( $key ) ] = absint( $page_id );
	}

	/**
	 * Resolve a registered page identifier.
	 *
	 * @param string $key Registry key.
	 * @return int
	 */
	public function pageId( string $key ): int {
		return $this->pages[ sanitize_key( $key ) ] ?? 0;
	}

	/**
	 * Record a loaded template.
	 *
	 * @param string $name Template name.
	 * @return void
	 */
	public function template( string $name ): void {
		$this->templates[ $name ] = true;
	}

	/**
	 * Verify a required function without simulating it.
	 *
	 * @param string $name Required function.
	 * @return bool
	 */
	public function requireFunction( string $name ): bool {
		if ( function_exists( $name ) ) {
			return true;
		}

		$this->missing_functions[] = $name;
		$this->error( sprintf( 'Required legacy function %s is unavailable.', $name ) );
		return false;
	}

	/**
	 * Verify a required type without simulating it.
	 *
	 * @param string $name Required class, interface, or trait.
	 * @return bool
	 */
	public function requireClass( string $name ): bool {
		if ( class_exists( $name ) || interface_exists( $name ) || trait_exists( $name ) ) {
			return true;
		}

		$this->missing_classes[] = $name;
		$this->error( sprintf( 'Required legacy type %s is unavailable.', $name ) );
		return false;
	}

	/**
	 * Record a non-destructive collision.
	 *
	 * @param string $message Collision description.
	 * @return void
	 */
	public function collision( string $message ): void {
		$this->collisions[] = sanitize_text_field( $message );
	}

	/**
	 * Record a non-sensitive diagnostic error.
	 *
	 * @param string $message Safe diagnostic message.
	 * @return void
	 */
	public function error( string $message ): void {
		$this->errors[] = sanitize_text_field( $message );
	}

	/**
	 * Return the non-sensitive runtime report.
	 *
	 * @return array<string,mixed>
	 */
	public function report(): array {
		return array(
			'constants'         => $this->constants,
			'functions'         => $this->functions,
			'classes'           => $this->classes,
			'hooks'             => $this->hooks,
			'assets'            => $this->assets,
			'pages'             => $this->pages,
			'templates'         => array_keys( $this->templates ),
			'missing_functions' => array_values( array_unique( $this->missing_functions ) ),
			'missing_classes'   => array_values( array_unique( $this->missing_classes ) ),
			'collisions'        => array_values( array_unique( $this->collisions ) ),
			'errors'            => array_values( array_unique( $this->errors ) ),
		);
	}
}
