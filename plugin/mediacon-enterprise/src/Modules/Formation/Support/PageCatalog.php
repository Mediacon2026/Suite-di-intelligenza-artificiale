<?php
/**
 * Supported formation page catalog.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Support;

use Mediacon\Enterprise\Core\PageCatalog as CorePageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves existing WordPress formation pages without creating content.
 */
final class PageCatalog extends CorePageCatalog {

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
	 * Return the module settings key.
	 *
	 * @return string
	 */
	protected function settingsKey(): string {
		return 'formation';
	}
}
