<?php
/**
 * Supported formation page catalog.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Support;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves existing WordPress formation pages without creating content.
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
			'formation'    => array(
				'title' => __( 'Formazione mediatori', 'mediacon-enterprise' ),
				'slug'  => 'formazione-mediatori',
				'type'  => 'landing',
			),
			'base-course'  => array(
				'title' => __( 'Corso base mediatori', 'mediacon-enterprise' ),
				'slug'  => 'corso-base-mediatori',
				'type'  => 'course',
			),
			'advanced'     => array(
				'title' => __( 'Corso di approfondimento', 'mediacon-enterprise' ),
				'slug'  => 'corso-approfondimento',
				'type'  => 'course',
			),
			'renewal'      => array(
				'title' => __( 'Corso di aggiornamento biennale', 'mediacon-enterprise' ),
				'slug'  => 'corso-aggiornamento-biennale',
				'type'  => 'course',
			),
			'calendar'     => array(
				'title' => __( 'Calendario corsi', 'mediacon-enterprise' ),
				'slug'  => 'calendario-corsi',
				'type'  => 'calendar',
			),
			'teachers'     => array(
				'title' => __( 'Docenti e formatori', 'mediacon-enterprise' ),
				'slug'  => 'docenti-e-formatori',
				'type'  => 'teachers',
			),
			'faq'          => array(
				'title' => __( 'FAQ formazione', 'mediacon-enterprise' ),
				'slug'  => 'faq-formazione',
				'type'  => 'standard',
			),
			'registration' => array(
				'title' => __( 'Iscrizioni', 'mediacon-enterprise' ),
				'slug'  => 'iscrizioni-formazione',
				'type'  => 'registration',
			),
			'upcoming'     => array(
				'title' => __( 'Prossimi corsi', 'mediacon-enterprise' ),
				'slug'  => 'prossimi-corsi',
				'type'  => 'archive',
			),
			'insights'     => array(
				'title' => __( 'Approfondimenti formativi', 'mediacon-enterprise' ),
				'slug'  => 'approfondimenti-formativi',
				'type'  => 'insights',
			),
		);
	}

	/**
	 * Return page rows enriched with WordPress state.
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
	 * Resolve an enabled definition for the current page.
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
	 * Return persisted formation settings.
	 *
	 * @return array<string,mixed>
	 */
	public function moduleSettings(): array {
		$value = $this->settings->get( 'formation', array() );

		return array(
			'pages'     => isset( $value['pages'] ) && is_array( $value['pages'] ) ? $value['pages'] : array(),
			'templates' => isset( $value['templates'] ) && is_array( $value['templates'] ) ? $value['templates'] : array(),
			'general'   => isset( $value['general'] ) && is_array( $value['general'] ) ? $value['general'] : array(),
		);
	}
}
