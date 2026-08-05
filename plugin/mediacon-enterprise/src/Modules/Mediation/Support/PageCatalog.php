<?php
/**
 * Supported mediation page catalog.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Support;

use Mediacon\Enterprise\Core\PageCatalog as CorePageCatalog;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves configured or existing WordPress pages without creating content.
 */
final class PageCatalog extends CorePageCatalog {

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
	 * Return the module settings key.
	 *
	 * @return string
	 */
	protected function settingsKey(): string {
		return 'mediation';
	}
}
