<?php
/**
 * Editable public formation copy.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Formation\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps institutional copy outside presentation templates.
 */
final class Content {

	/**
	 * Return content for a supported page.
	 *
	 * @param string $key Page key.
	 * @return array<string,mixed>
	 */
	public function page( string $key ): array {
		$pages = $this->pages();

		return $pages[ $key ] ?? $pages['formation'];
	}

	/**
	 * Return editable page content.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function pages(): array {
		return array(
			'formation'    => array(
				'eyebrow' => __( 'Ente di Formazione n. 422', 'mediacon-enterprise' ),
				'title'   => __( 'Formazione per Mediatori', 'mediacon-enterprise' ),
				'intro'   => __( 'Percorsi formativi dedicati allo sviluppo e all’aggiornamento delle competenze nella mediazione.', 'mediacon-enterprise' ),
			),
			'base-course'  => $this->course(
				__( 'Corso base mediatori', 'mediacon-enterprise' ),
				__( 'Un percorso strutturato per acquisire conoscenze, metodo e strumenti utili alla pratica della mediazione.', 'mediacon-enterprise' ),
				__( '80 ore', 'mediacon-enterprise' )
			),
			'advanced'     => $this->course(
				__( 'Corso di approfondimento', 'mediacon-enterprise' ),
				__( 'Un percorso focalizzato sull’analisi e sull’applicazione di competenze specialistiche.', 'mediacon-enterprise' ),
				__( '14 ore', 'mediacon-enterprise' )
			),
			'renewal'      => $this->course(
				__( 'Corso di aggiornamento biennale', 'mediacon-enterprise' ),
				__( 'Un programma dedicato al consolidamento e all’aggiornamento professionale dei Mediatori.', 'mediacon-enterprise' ),
				__( '18 ore', 'mediacon-enterprise' )
			),
			'calendar'     => $this->standard( __( 'Calendario corsi', 'mediacon-enterprise' ), __( 'Consulta le prossime attività formative pubblicate sul sito.', 'mediacon-enterprise' ) ),
			'teachers'     => $this->standard( __( 'Docenti e formatori', 'mediacon-enterprise' ), __( 'Conosci i professionisti coinvolti nei percorsi formativi Mediacon.', 'mediacon-enterprise' ) ),
			'faq'          => $this->standard( __( 'FAQ formazione', 'mediacon-enterprise' ), __( 'Informazioni utili per scegliere e frequentare un’attività formativa.', 'mediacon-enterprise' ) ),
			'registration' => $this->standard( __( 'Iscrizioni', 'mediacon-enterprise' ), __( 'Consulta le modalità indicate per il corso di interesse e invia la tua richiesta.', 'mediacon-enterprise' ) ),
			'upcoming'     => $this->standard( __( 'Prossimi corsi', 'mediacon-enterprise' ), __( 'Scopri le attività formative programmate e le relative modalità di partecipazione.', 'mediacon-enterprise' ) ),
			'insights'     => $this->standard( __( 'Approfondimenti formativi', 'mediacon-enterprise' ), __( 'Articoli e materiali pubblicati dalla redazione sui temi della formazione e della mediazione.', 'mediacon-enterprise' ) ),
		);
	}

	/**
	 * Build course page copy.
	 *
	 * @param string $title    Course title.
	 * @param string $intro    Course introduction.
	 * @param string $duration Default duration shown when no page metadata exists.
	 * @return array<string,mixed>
	 */
	private function course( string $title, string $intro, string $duration ): array {
		return array(
			'eyebrow'      => __( 'Percorso formativo', 'mediacon-enterprise' ),
			'title'        => $title,
			'intro'        => $intro,
			'duration'     => $duration,
			'audience'     => __( 'Professionisti interessati a sviluppare o aggiornare competenze nell’ambito della mediazione.', 'mediacon-enterprise' ),
			'requirements' => __( 'I requisiti specifici e la documentazione richiesta sono indicati nella scheda del corso pubblicata dall’Ente.', 'mediacon-enterprise' ),
		);
	}

	/**
	 * Build standard page copy.
	 *
	 * @param string $title Page title.
	 * @param string $intro Page introduction.
	 * @return array<string,mixed>
	 */
	private function standard( string $title, string $intro ): array {
		return array(
			'eyebrow' => __( 'Formazione Mediacon', 'mediacon-enterprise' ),
			'title'   => $title,
			'intro'   => $intro,
		);
	}

	/**
	 * Return common formation FAQ copy.
	 *
	 * @return array<int,array{question:string,answer:string}>
	 */
	public function faq(): array {
		return array(
			array(
				'question' => __( 'Dove trovo date e modalità del corso?', 'mediacon-enterprise' ),
				'answer'   => __( 'La scheda di ogni corso riporta le informazioni pubblicate dall’Ente, incluse modalità, sede e calendario disponibili.', 'mediacon-enterprise' ),
			),
			array(
				'question' => __( 'Come posso richiedere l’iscrizione?', 'mediacon-enterprise' ),
				'answer'   => __( 'Utilizza il collegamento indicato nella scheda del corso oppure consulta la pagina Iscrizioni.', 'mediacon-enterprise' ),
			),
			array(
				'question' => __( 'A chi posso chiedere chiarimenti?', 'mediacon-enterprise' ),
				'answer'   => __( 'Puoi contattare Mediacon attraverso i recapiti pubblicati sul sito prima di inviare la richiesta.', 'mediacon-enterprise' ),
			),
		);
	}

	/**
	 * Return supported course-type labels.
	 *
	 * @return array<string,string>
	 */
	public function courseTypes(): array {
		return array(
			'base'     => __( 'Corso base', 'mediacon-enterprise' ),
			'advanced' => __( 'Approfondimento', 'mediacon-enterprise' ),
			'renewal'  => __( 'Aggiornamento', 'mediacon-enterprise' ),
			'event'    => __( 'Evento formativo', 'mediacon-enterprise' ),
			'seminar'  => __( 'Seminario', 'mediacon-enterprise' ),
			'workshop' => __( 'Workshop', 'mediacon-enterprise' ),
		);
	}
}
