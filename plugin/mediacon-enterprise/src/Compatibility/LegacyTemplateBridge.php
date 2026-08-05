<?php
/**
 * Safe legacy template facade.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

use Mediacon\Enterprise\Helpers\Template;
use RuntimeException;

/** Delegates rendering to the bounded Enterprise template loader. */
final readonly class LegacyTemplateBridge {

	/**
	 * Create the template bridge.
	 *
	 * @param Template               $template Core template loader.
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private Template $template, private LegacyContractRegistry $registry ) {}

	/**
	 * Render a template relative to the Enterprise template folder.
	 *
	 * @param string              $name Template name.
	 * @param array<string,mixed> $data Template data.
	 * @return bool
	 */
	public function render( string $name, array $data = array() ): bool {
		try {
			$this->template->render( $name, $data );
			$this->registry->template( $name );
			return true;
		} catch ( RuntimeException $exception ) {
			$this->registry->error( $exception->getMessage() );
			return false;
		}
	}

	/**
	 * Render a common Enterprise component.
	 *
	 * @param string              $name Component name.
	 * @param array<string,mixed> $data Component data.
	 * @return bool
	 */
	public function component( string $name, array $data = array() ): bool {
		$name = sanitize_key( $name );
		return '' !== $name && $this->render( 'components/' . $name . '.php', $data );
	}
}
