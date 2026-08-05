<?php
/**
 * Shared public page catalog behavior.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Core;

use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves configured WordPress pages without creating or changing content.
 */
abstract class PageCatalog {

	/**
	 * Create the catalog.
	 *
	 * @param SettingsManager $settings Core settings manager.
	 */
	public function __construct( private readonly SettingsManager $settings ) {}

	/**
	 * Return supported page definitions.
	 *
	 * @return array<string,array{title:string,slug:string,type:string}>
	 */
	abstract public function definitions(): array;

	/**
	 * Return the module settings key.
	 *
	 * @return string
	 */
	abstract protected function settingsKey(): string;

	/**
	 * Return page rows enriched with resolved WordPress state.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function rows(): array {
		$settings = $this->moduleSettings();
		$rows     = array();

		foreach ( $this->definitions() as $key => $definition ) {
			$page_id = absint( $settings['pages'][ $key ] ?? 0 );
			if ( 0 === $page_id ) {
				$page    = get_page_by_path( $definition['slug'], OBJECT, 'page' );
				$page_id = $page instanceof WP_Post ? (int) $page->ID : 0;
			}

			$rows[ $key ] = array_merge(
				$definition,
				array(
					'key'     => $key,
					'page_id' => $page_id,
					'url'     => 0 < $page_id ? (string) get_permalink( $page_id ) : '',
					'enabled' => ! empty( $settings['templates'][ $key ] ),
				)
			);
		}

		return $rows;
	}

	/**
	 * Resolve the enabled definition for the current page.
	 *
	 * @return array<string,mixed>|null
	 */
	public function current(): ?array {
		if ( ! is_page() ) {
			return null;
		}

		$current_id = get_queried_object_id();
		foreach ( $this->rows() as $row ) {
			if ( ! empty( $row['enabled'] ) && $current_id === $row['page_id'] ) {
				return $row;
			}
		}

		return null;
	}

	/**
	 * Return persisted module settings with stable page and template maps.
	 *
	 * @return array<string,mixed>
	 */
	public function moduleSettings(): array {
		$value = $this->settings->get( $this->settingsKey(), array() );
		$value = is_array( $value ) ? $value : array();

		$value['pages']     = isset( $value['pages'] ) && is_array( $value['pages'] ) ? $value['pages'] : array();
		$value['templates'] = isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array();

		return $value;
	}
}
