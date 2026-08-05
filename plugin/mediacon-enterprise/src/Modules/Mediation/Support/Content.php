<?php
/**
 * Editable public mediation copy.
 *
 * @package MediaconEnterprise
 */

namespace Mediacon\Enterprise\Modules\Mediation\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps public copy separate from templates so it can be reviewed and changed safely.
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

		return $pages[ $key ] ?? $pages['how-it-works'];
	}

	/**
	 * Return all editable page content.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function pages(): array {
		return array(
			'how-it-works'     => array(
				'eyebrow'  => __( 'Percorso di mediazione', 'mediacon-enterprise' ),
				'title'    => __( 'Come funziona la mediazione', 'mediacon-enterprise' ),
				'intro'    => __( 'Un percorso organizzato in fasi, nel quale le parti possono confrontarsi con il supporto di un Mediatore imparziale.', 'mediacon-enterprise' ),
				'benefits' => array(
					__( 'Uno spazio strutturato per il confronto', 'mediacon-enterprise' ),
					__( 'Incontri organizzati dall’Organismo', 'mediacon-enterprise' ),
					__( 'Possibilità di esplorare soluzioni condivise', 'mediacon-enterprise' ),
				),
				'mediator' => __( 'Il Mediatore facilita il dialogo e aiuta le parti a valutare possibili soluzioni. Non decide la controversia e non sostituisce i consulenti delle parti.', 'mediacon-enterprise' ),
				'outcomes' => array(
					__( 'Assenza della parte invitata', 'mediacon-enterprise' ),
					__( 'Mancato accordo al primo incontro', 'mediacon-enterprise' ),
					__( 'Accordo al primo incontro', 'mediacon-enterprise' ),
					__( 'Mancato accordo dopo più incontri', 'mediacon-enterprise' ),
					__( 'Accordo dopo più incontri', 'mediacon-enterprise' ),
				),
			),
			'costs'            => $this->standard(
				__( 'Costi della mediazione', 'mediacon-enterprise' ),
				__( 'Informazioni chiare per orientarsi tra le voci economiche del procedimento.', 'mediacon-enterprise' ),
				array(
					__( 'Consulta le tariffe e i criteri pubblicati dall’Organismo.', 'mediacon-enterprise' ),
					__( 'Richiedi un preventivo riferito alle caratteristiche del caso.', 'mediacon-enterprise' ),
					__( 'Verifica sempre gli importi applicabili prima del deposito.', 'mediacon-enterprise' ),
				)
			),
			'civil-commercial' => $this->standard(
				__( 'Mediazione civile e commerciale', 'mediacon-enterprise' ),
				__( 'Un percorso di confronto per controversie in ambito civile e commerciale.', 'mediacon-enterprise' ),
				array(
					__( 'Esamina l’oggetto della controversia e la documentazione disponibile.', 'mediacon-enterprise' ),
					__( 'Individua le parti e i recapiti necessari alla convocazione.', 'mediacon-enterprise' ),
					__( 'Contatta l’Organismo per chiarire gli aspetti procedurali.', 'mediacon-enterprise' ),
				)
			),
			'court-referred'   => $this->standard(
				__( 'Mediazione demandata dal giudice', 'mediacon-enterprise' ),
				__( 'Informazioni operative per affrontare un percorso di mediazione disposto nel corso di un giudizio.', 'mediacon-enterprise' ),
				array(
					__( 'Condividi il provvedimento con il tuo professionista.', 'mediacon-enterprise' ),
					__( 'Verifica termini e indicazioni contenuti nel provvedimento.', 'mediacon-enterprise' ),
					__( 'Deposita la documentazione richiesta attraverso i canali dell’Organismo.', 'mediacon-enterprise' ),
				)
			),
			'online'           => $this->standard(
				__( 'Mediazione telematica', 'mediacon-enterprise' ),
				__( 'Partecipa agli incontri a distanza attraverso gli strumenti comunicati dall’Organismo.', 'mediacon-enterprise' ),
				array(
					__( 'Verifica in anticipo connessione, audio e videocamera.', 'mediacon-enterprise' ),
					__( 'Utilizza un ambiente riservato e un dispositivo affidabile.', 'mediacon-enterprise' ),
					__( 'Segui le istruzioni ricevute per accesso e identificazione.', 'mediacon-enterprise' ),
				)
			),
			'application'      => $this->standard(
				__( 'Istanza di mediazione', 'mediacon-enterprise' ),
				__( 'Prepara il deposito con i dati e i documenti utili alla corretta gestione della richiesta.', 'mediacon-enterprise' ),
				array(
					__( 'Indica con precisione le parti coinvolte.', 'mediacon-enterprise' ),
					__( 'Descrivi in modo sintetico l’oggetto della controversia.', 'mediacon-enterprise' ),
					__( 'Allega i documenti richiesti e conserva la ricevuta del deposito.', 'mediacon-enterprise' ),
				)
			),
			'participation'    => $this->standard(
				__( 'Adesione alla mediazione', 'mediacon-enterprise' ),
				__( 'Organizza la partecipazione dopo aver ricevuto una convocazione.', 'mediacon-enterprise' ),
				array(
					__( 'Leggi integralmente la comunicazione ricevuta.', 'mediacon-enterprise' ),
					__( 'Comunica tempestivamente la tua adesione all’Organismo.', 'mediacon-enterprise' ),
					__( 'Prepara documenti e informazioni utili al confronto.', 'mediacon-enterprise' ),
				)
			),
			'faq'              => $this->standard(
				__( 'Domande frequenti sulla mediazione', 'mediacon-enterprise' ),
				__( 'Risposte sintetiche alle domande più comuni sul percorso e sull’organizzazione degli incontri.', 'mediacon-enterprise' ),
				array()
			),
			'legislation'      => array(
				'eyebrow' => __( 'Approfondimenti', 'mediacon-enterprise' ),
				'title'   => __( 'Normativa', 'mediacon-enterprise' ),
				'intro'   => __( 'Raccolta editoriale dei contenuti normativi pubblicati sul sito.', 'mediacon-enterprise' ),
			),
			'case-law'         => array(
				'eyebrow' => __( 'Approfondimenti', 'mediacon-enterprise' ),
				'title'   => __( 'Sentenze e giurisprudenza', 'mediacon-enterprise' ),
				'intro'   => __( 'Raccolta editoriale delle decisioni e degli approfondimenti pubblicati sul sito.', 'mediacon-enterprise' ),
			),
		);
	}

	/**
	 * Build standard page content.
	 *
	 * @param string            $title  Page title.
	 * @param string            $intro  Page introduction.
	 * @param array<int,string> $cards  Information cards.
	 * @return array<string,mixed>
	 */
	private function standard( string $title, string $intro, array $cards ): array {
		return array(
			'eyebrow' => __( 'Mediazione', 'mediacon-enterprise' ),
			'title'   => $title,
			'intro'   => $intro,
			'cards'   => $cards,
		);
	}

	/**
	 * Return common FAQ copy.
	 *
	 * @return array<int,array{question:string,answer:string}>
	 */
	public function faq(): array {
		return array(
			array(
				'question' => __( 'Chi organizza gli incontri?', 'mediacon-enterprise' ),
				'answer'   => __( 'L’Organismo cura gli aspetti organizzativi e comunica alle parti le informazioni necessarie per partecipare.', 'mediacon-enterprise' ),
			),
			array(
				'question' => __( 'Il Mediatore decide chi ha ragione?', 'mediacon-enterprise' ),
				'answer'   => __( 'No. Il Mediatore facilita il confronto tra le parti e non decide la controversia.', 'mediacon-enterprise' ),
			),
			array(
				'question' => __( 'Dove trovo costi e modalità di deposito?', 'mediacon-enterprise' ),
				'answer'   => __( 'Consulta le pagine dedicate del sito oppure contatta direttamente l’Organismo prima di procedere.', 'mediacon-enterprise' ),
			),
		);
	}
}
