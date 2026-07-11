# MED-016 Telemediation Engine + Webex Integration

## Scopo
MED-016 definisce la gestione della mediazione telematica e degli incontri da remoto o misti nel modulo Mediazione di Nexus ERP. Webex e' il provider iniziale di videoconferenza, integrato tramite un provider layer astratto per consentire futuri provider alternativi.

Questo documento e' una specifica funzionale e architetturale. Non costituisce parere legale e deve essere verificato con il responsabile normativo dell'organismo prima della messa in produzione.

## Riferimento art. 8-bis D.Lgs. 28/2010
L'art. 8-bis disciplina la mediazione in modalita' telematica. Ai fini progettuali Nexus ERP deve trattare questa modalita' come un processo digitale completo, in cui verbale, sottoscrizioni, deposito, invio e conservazione seguono un workflow documentale informatico.

Regole operative di piattaforma:
- il verbale deve essere gestito come documento informatico;
- le firme devono essere raccolte digitalmente;
- il mediatore firma dopo aver verificato firme, validita' e integrita';
- il documento viene depositato in segreteria;
- il documento viene inviato alle parti e agli avvocati;
- il documento finale viene predisposto per conservazione CAD.

## Riferimento art. 8-ter D.Lgs. 28/2010
L'art. 8-ter disciplina gli incontri con partecipazione audiovisiva da remoto, anche in forma mista. Ai fini progettuali Nexus ERP deve consentire che ogni partecipante sia classificato per modalita' di partecipazione: presenza, remoto o misto.

Regole operative di piattaforma:
- ogni partecipante deve avere una modalita' di partecipazione registrata;
- il consenso alla firma digitale deve essere tracciato;
- se c'e' consenso, il documento segue il workflow di firma digitale;
- se manca consenso, il documento segue il workflow di firma analogica avanti al mediatore;
- le presenze devono essere confermate e collegate al fascicolo.

## Mediazione telematica e incontro remoto/misto

### Mediazione telematica
La mediazione telematica e' un procedimento progettato integralmente su documento informatico, firma digitale, deposito digitale, invio digitale e conservazione. Nel sistema viene identificata come processo governato da `signature_workflows`, `signature_workflow_steps`, document engine e conservazione CAD.

### Incontro da remoto o misto
L'incontro da remoto o misto riguarda la modalita' di partecipazione dei soggetti all'incontro. Il procedimento puo' rimanere ordinario o misto, ma deve registrare:
- provider di videoconferenza;
- link incontro;
- partecipanti;
- presenza effettiva;
- consenso alla firma digitale;
- modalita' firma scelta o necessaria.

## Uso Webex
Webex e' il provider iniziale per la videoconferenza.

Funzioni previste:
- generazione meeting Webex;
- aggiornamento dati meeting;
- cancellazione meeting;
- recupero join URL;
- sincronizzazione partecipanti e presenze.

Nella fase MED-016 le chiamate reali alle API Webex non sono implementate. Il backend contiene solo il provider layer e placeholder tecnici.

## Workflow firme

### Art. 8-bis
1. Generazione verbale come documento informatico.
2. Invio separato ai firmatari.
3. Raccolta firme digitali.
4. Verifica validita' e integrita' firme.
5. Firma del mediatore.
6. Deposito in segreteria.
7. Invio a parti e avvocati.
8. Predisposizione conservazione CAD.

### Art. 8-ter
1. Registrazione partecipanti e modalita' presenza/remoto.
2. Raccolta consenso alla firma digitale.
3. Se tutti i firmatari necessari acconsentono, avvio workflow digitale.
4. Se manca consenso, raccolta firme analogiche avanti al mediatore.
5. Registrazione evidenze, note e documenti finali.

## Verifica firme
Il sistema deve predisporre uno stato di verifica per ogni documento firmato:
- firma presente;
- firma valida;
- firma integra;
- firmatario corrispondente;
- versione documento corretta;
- data firma;
- log evento o evidenza provider.

Il mediatore deve poter vedere lo stato complessivo prima della propria firma conclusiva.

## Deposito in segreteria
Dopo la verifica firme il verbale o documento finale viene depositato in segreteria.

Dati da tracciare:
- documento finale;
- versione;
- data deposito;
- utente o ruolo depositante;
- esito verifica;
- note operative.

## Invio alle parti e avvocati
Il sistema deve predisporre invii separati a:
- parte istante;
- parte invitata;
- avvocato istante;
- avvocato invitato;
- mediatore;
- segreteria;
- responsabile organismo.

Gli invii possono avvenire tramite email o PEC quando l'integrazione sara' disponibile. Ogni invio deve lasciare log, destinatario, allegati e stato.

## Conservazione CAD
Il documento informatico finale deve essere predisposto per conservazione a norma CAD tramite strutture dati di preservation.

La fase attuale non implementa conservazione reale. Il sistema deve registrare:
- documento;
- hash;
- algoritmo;
- provider futuro;
- stato;
- ricevuta;
- metadati;
- legal hold;
- retention period.

## Dati principali
Le tabelle progettuali sono documentate in `docs/04_DATABASE/DATABASE_DESIGN_MASTER.md`:
- `telemediation_meetings`;
- `telemediation_participants`;
- `remote_consent_records`;
- `signature_workflows`;
- `signature_workflow_steps`;
- `cad_preservation_records`.

## Stati consigliati

### Incontro telematico
- draft;
- scheduled;
- link_generated;
- completed;
- cancelled;
- sync_pending;
- sync_error.

### Partecipazione
- invited;
- joined;
- left;
- presence_confirmed;
- absent.

### Workflow firma
- draft;
- sent;
- partially_signed;
- completed;
- refused;
- expired;
- cancelled.

## Requisiti di interfaccia
Nel modulo Mediazioni, sezione Incontri, l'utente deve poter predisporre:
- modalita' incontro: presenza, telematica, mista;
- provider: Webex;
- generazione link Webex;
- link incontro;
- partecipanti;
- consenso firma digitale;
- modalita' firma: digitale o analogica.

## Controlli
- Se modalita' telematica, richiedere provider e link incontro.
- Se modalita' mista, richiedere modalita' partecipazione per ogni partecipante.
- Se firma digitale, richiedere consenso o base operativa tracciata.
- Se art. 8-bis, bloccare deposito finale finche' firme e integrita' non sono verificate.
- Se conservazione richiesta, calcolare hash e predisporre record CAD.
