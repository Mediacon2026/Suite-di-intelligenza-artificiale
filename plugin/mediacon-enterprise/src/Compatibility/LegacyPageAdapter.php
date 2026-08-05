<?php
/**
 * Legacy page registration adapter.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Compatibility;

/** Translates documented Design Core page definitions into frontend routes. */
final class LegacyPageAdapter {

	/**
	 * Registered page definitions, indexed by slug.
	 *
	 * @var array<string,array{slug:string,name:string,template:string}>
	 */
	private array $definitions = array();

	/**
	 * Create the adapter.
	 *
	 * @param LegacyContractRegistry $registry Contract registry.
	 */
	public function __construct( private readonly LegacyContractRegistry $registry ) {}

	/**
	 * Register a documented Design Core page definition.
	 *
	 * @param array<string,mixed> $definition Legacy definition.
	 * @return bool
	 */
	public function register( array $definition ): bool {
		$slug     = sanitize_title( (string) ( $definition['slug'] ?? '' ) );
		$name     = sanitize_text_field( (string) ( $definition['name'] ?? '' ) );
		$template = realpath( (string) ( $definition['template'] ?? '' ) );

		if ( '' === $slug || '' === $name || false === $template || 'php' !== strtolower( pathinfo( $template, PATHINFO_EXTENSION ) ) || ! is_readable( $template ) ) {
			$this->registry->error( 'A legacy page definition was rejected because its slug, name, or template was invalid.' );
			return false;
		}

		$this->definitions[ $slug ] = array(
			'slug'     => $slug,
			'name'     => $name,
			'template' => $template,
		);

		return true;
	}

	/**
	 * Select the registered legacy template for its matching WordPress page.
	 *
	 * @param string $template Theme template path.
	 * @return string
	 */
	public function filterTemplate( string $template ): string {
		foreach ( $this->definitions as $definition ) {
			if ( is_page( $definition['slug'] ) && is_readable( $definition['template'] ) ) {
				$this->registry->template( $definition['template'] );
				return $definition['template'];
			}
		}

		return $template;
	}
}
