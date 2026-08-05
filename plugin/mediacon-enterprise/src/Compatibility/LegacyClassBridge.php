<?php
/**
 * Explicit legacy class alias bridge.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

/** Creates aliases only when both names have been explicitly supplied. */
final readonly class LegacyClassBridge {

	/**
	 * Create the class bridge.
	 *
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private LegacyContractRegistry $registry ) {}

	/**
	 * Create an explicit, non-destructive type alias.
	 *
	 * @param string $legacy     Legacy type name.
	 * @param string $enterprise Enterprise type name.
	 * @return bool
	 */
	public function alias( string $legacy, string $enterprise ): bool {
		if ( class_exists( $legacy ) || interface_exists( $legacy ) || trait_exists( $legacy ) ) {
			$this->registry->collision( sprintf( 'Type %s already exists; no compatibility alias was created.', $legacy ) );
			return false;
		}
		if ( ! class_exists( $enterprise ) && ! interface_exists( $enterprise ) && ! trait_exists( $enterprise ) ) {
			$this->registry->requireClass( $enterprise );
			return false;
		}
		if ( trait_exists( $enterprise ) ) {
			$this->registry->error( sprintf( 'Trait %s cannot be aliased safely without a documented wrapper.', $enterprise ) );
			return false;
		}

		$created = class_alias( $enterprise, $legacy );
		if ( $created ) {
			$this->registry->classAvailable( $legacy );
		}
		return $created;
	}
}
