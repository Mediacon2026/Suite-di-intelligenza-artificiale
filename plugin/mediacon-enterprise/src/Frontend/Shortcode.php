<?php
/**
 * Frontend status shortcode.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Frontend;

use Mediacon\Enterprise\Core\ModuleManager;

/**
 * Renders a small frontend module summary.
 */
final class Shortcode {

	/**
	 * Create the shortcode handler.
	 *
	 * @param ModuleManager $modules Module manager.
	 */
	public function __construct( private readonly ModuleManager $modules ) {}

	/**
	 * Register the shortcode.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'mediacon_enterprise', array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string,mixed> $attributes Shortcode attributes.
	 * @return string
	 */
	public function render( array $attributes = array() ): string {
		$attributes = shortcode_atts(
			array( 'title' => esc_html__( 'Mediacon Enterprise', 'mediacon-enterprise' ) ),
			$attributes,
			'mediacon_enterprise'
		);

		$module_names = array_map(
			static fn ( $module ): string => ucwords( str_replace( '-', ' ', $module->id() ) ),
			$this->modules->all()
		);

		return sprintf(
			'<section class="mediacon-enterprise" aria-label="%1$s"><h2>%1$s</h2><p>%2$s</p></section>',
			esc_attr( (string) $attributes['title'] ),
			esc_html( implode( ', ', $module_names ) )
		);
	}
}
