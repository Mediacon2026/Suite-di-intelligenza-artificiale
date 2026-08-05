<?php
/**
 * Reversible legacy routing migration manager.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Enterprise;

use Mediacon\Enterprise\Core\SettingsManager;

defined( 'ABSPATH' ) || exit;

/** Migrates ownership metadata only; legacy code and content remain untouched. */
final class MigrationManager {

	/** Canonical page slug mapping. @var array<string,array{0:string,1:string}> */
	private const SLUGS = array(
		'come-funziona-la-mediazione'      => array( 'mediation', 'how-it-works' ),
		'costi-della-mediazione'           => array( 'mediation', 'costs' ),
		'mediazione-civile-e-commerciale'  => array( 'mediation', 'civil-commercial' ),
		'mediazione-demandata-dal-giudice' => array( 'mediation', 'court-referred' ),
		'mediazione-telematica'            => array( 'mediation', 'online' ),
		'istanza-di-mediazione'            => array( 'mediation', 'application' ),
		'adesione-alla-mediazione'         => array( 'mediation', 'participation' ),
		'faq-mediazione'                   => array( 'mediation', 'faq' ),
		'normativa'                        => array( 'mediation', 'legislation' ),
		'sentenze-e-giurisprudenza'        => array( 'mediation', 'case-law' ),
		'formazione-mediatori'             => array( 'formation', 'formation' ),
		'corso-base-mediatori'             => array( 'formation', 'base-course' ),
		'corso-approfondimento'            => array( 'formation', 'advanced' ),
		'corso-aggiornamento-biennale'     => array( 'formation', 'renewal' ),
		'calendario-corsi'                 => array( 'formation', 'calendar' ),
		'docenti-e-formatori'              => array( 'formation', 'teachers' ),
		'faq-formazione'                   => array( 'formation', 'faq' ),
		'iscrizioni-formazione'            => array( 'formation', 'registration' ),
		'prossimi-corsi'                   => array( 'formation', 'upcoming' ),
		'approfondimenti-formativi'        => array( 'formation', 'insights' ),
	);

	/**
	 * Create the migration manager.
	 *
	 * @param SiteInventory   $inventory Runtime inventory.
	 * @param PageGovernance  $governance Governance service.
	 * @param SettingsManager $settings Settings service.
	 */
	public function __construct(
		private readonly SiteInventory $inventory,
		private readonly PageGovernance $governance,
		private readonly SettingsManager $settings
	) {}

	/**
	 * Return the static/runtime analysis for one installed plugin.
	 *
	 * @param string $plugin Plugin basename.
	 */
	public function analyze( string $plugin ): array {
		return $this->inventory->plugins()[ $plugin ] ?? array();
	}

	/**
	 * Snapshot current routing, then opt associated pages into Enterprise.
	 *
	 * @param string $plugin Plugin basename.
	 */
	public function migrate( string $plugin ): array {
		$analysis = $this->analyze( $plugin );
		if ( array() === $analysis ) {
			return array(
				'success' => false,
				'message' => __( 'Plugin legacy non trovato.', 'mediacon-enterprise' ),
			);
		}
		$pages = array();
		foreach ( $analysis['pages'] as $page ) {
			$mapping = self::SLUGS[ $page['slug'] ] ?? null;
			if ( null === $mapping ) {
				continue;
			}
			$pages[] = (int) $page['id'];
		}
		$snapshots            = $this->settings->get( 'migration_snapshots', array() );
		$snapshots            = is_array( $snapshots ) ? $snapshots : array();
		$snapshots[ $plugin ] = array(
			'created_at' => gmdate( 'c' ),
			'modes'      => $this->governance->allModes(),
			'pages'      => $pages,
		);
		$this->settings->set( 'migration_snapshots', $snapshots );

		foreach ( $analysis['pages'] as $page ) {
			$mapping = self::SLUGS[ $page['slug'] ] ?? null;
			if ( null !== $mapping ) {
				$this->governance->setMode( $mapping[0], $mapping[1], PageGovernance::ENTERPRISE );
			}
		}
		return array(
			'success' => true,
			/* translators: %d: number of pages routed to Enterprise. */
			'message' => sprintf( __( 'Migrazione architetturale completata per %d pagine; plugin e contenuti legacy invariati.', 'mediacon-enterprise' ), count( $pages ) ),
		);
	}

	/**
	 * Restore the exact pre-migration governance snapshot.
	 *
	 * @param string $plugin Plugin basename.
	 */
	public function rollback( string $plugin ): array {
		$snapshots = $this->settings->get( 'migration_snapshots', array() );
		$snapshot  = is_array( $snapshots ) ? ( $snapshots[ $plugin ] ?? null ) : null;
		if ( ! is_array( $snapshot ) ) {
			return array(
				'success' => false,
				'message' => __( 'Nessuno snapshot disponibile per il rollback.', 'mediacon-enterprise' ),
			);
		}
		$this->governance->restore( is_array( $snapshot['modes'] ?? null ) ? $snapshot['modes'] : array() );
		unset( $snapshots[ $plugin ] );
		$this->settings->set( 'migration_snapshots', $snapshots );
		return array(
			'success' => true,
			'message' => __( 'Rollback completato. Le impostazioni di gestione precedenti sono state ripristinate.', 'mediacon-enterprise' ),
		);
	}

	/**
	 * Verify plugin contracts, page existence and selected ownership.
	 *
	 * @param string $plugin Plugin basename.
	 */
	public function verify( string $plugin ): array {
		$analysis = $this->analyze( $plugin );
		if ( array() === $analysis ) {
			return array(
				'success' => false,
				'message' => __( 'Plugin legacy non trovato.', 'mediacon-enterprise' ),
			);
		}
		$problems = count( $analysis['missing_functions'] ) + count( $analysis['missing_constants'] ) + count( $analysis['missing_classes'] );
		foreach ( $analysis['pages'] as $page ) {
			if ( null === get_post( (int) $page['id'] ) ) {
				++$problems;
			}
		}
		if ( 0 === $problems ) {
			$message = __( 'Verifica completata: nessuna anomalia rilevata.', 'mediacon-enterprise' );
		} else {
			/* translators: %d: number of compatibility anomalies. */
			$message = sprintf( __( 'Verifica completata con %d anomalie.', 'mediacon-enterprise' ), $problems );
		}
		return array(
			'success' => 0 === $problems,
			'message' => $message,
		);
	}
}
