<?php
/**
 * Supported mediation page catalog.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Support;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves configured or existing WordPress pages without creating content.
 */
final class PageCatalog {

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
	public function definitions(): array {
		return array(
			'how-it-works'     => array(
				'title' => __( 'Come funziona la mediazione', 'mediacon-enterprise' ),
				'slug'  => 'come-funziona-la-mediazione',
				'type'  => 'process',
			),
			'costs'            => array(
				'title' => __( 'Costi della mediazione', 'mediacon-enterprise' ),
				'slug'  => 'costi-della-mediazione',
				'type'  => 'standard',
			),
			'civil-commercial' => array(
				'title' => __( 'Mediazione civile e commerciale', 'mediacon-enterprise' ),
				'slug'  => 'mediazione-civile-e-commerciale',
				'type'  => 'standard',
			),
			'court-referred'   => array(
				'title' => __( 'Mediazione demandata dal giudice', 'mediacon-enterprise' ),
				'slug'  => 'mediazione-demandata-dal-giudice',
				'type'  => 'standard',
			),
			'online'           => array(
				'title' => __( 'Mediazione telematica', 'mediacon-enterprise' ),
				'slug'  => 'mediazione-telematica',
				'type'  => 'standard',
			),
			'application'      => array(
				'title' => __( 'Istanza di mediazione', 'mediacon-enterprise' ),
				'slug'  => 'istanza-di-mediazione',
				'type'  => 'standard',
			),
			'participation'    => array(
				'title' => __( 'Adesione alla mediazione', 'mediacon-enterprise' ),
				'slug'  => 'adesione-alla-mediazione',
				'type'  => 'standard',
			),
			'faq'              => array(
				'title' => __( 'FAQ', 'mediacon-enterprise' ),
				'slug'  => 'faq-mediazione',
				'type'  => 'standard',
			),
			'legislation'      => array(
				'title' => __( 'Normativa', 'mediacon-enterprise' ),
				'slug'  => 'normativa',
				'type'  => 'editorial',
			),
			'case-law'         => array(
				'title' => __( 'Sentenze e giurisprudenza', 'mediacon-enterprise' ),
				'slug'  => 'sentenze-e-giurisprudenza',
				'type'  => 'editorial',
			),
		);
	}

	/**
	 * Return page rows enriched with resolved WordPress state.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function rows(): array {
		$settings = $this->moduleSettings();
		$rows     = array();

		foreach ( $this->definitions() as $key => $definition ) {
			$page_id = isset( $settings['pages'][ $key ] ) ? absint( $settings['pages'][ $key ] ) : 0;
			if ( 0 === $page_id ) {
				$page    = get_page_by_path( $definition['slug'], OBJECT, 'page' );
				$page_id = $page instanceof \WP_Post ? (int) $page->ID : 0;
			}

			$rows[ $key ] = array_merge(
				$definition,
				array(
					'key'     => $key,
					'page_id' => $page_id,
					'url'     => $page_id > 0 ? (string) get_permalink( $page_id ) : '',
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
			if ( $row['enabled'] && $current_id === $row['page_id'] ) {
				return $row;
			}
		}

		return null;
	}

	/**
	 * Return persisted module settings.
	 *
	 * @return array{pages:array<string,int>,templates:array<string,bool>}
	 */
	public function moduleSettings(): array {
		$value = $this->settings->get( 'mediation', array() );

		return array(
			'pages'     => isset( $value['pages'] ) && is_array( $value['pages'] ) ? $value['pages'] : array(),
			'templates' => isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array(),
		);
	}
}
