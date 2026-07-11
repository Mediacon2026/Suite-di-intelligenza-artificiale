# AI-001 Nexus AI Core Architecture

## 1. Visione
Nexus AI Core non e' un chatbot generico, ma il motore intelligente della piattaforma Nexus ERP.

Il suo compito e' trasformare documenti, comunicazioni e dati non strutturati in informazioni operative verificabili, riutilizzabili da tutti i moduli: Mediazione, Formazione, OCC, Orientamento, Crisi d'Impresa, Advisor e Documentale.

La regola di prodotto e' chiara: AI propone, utente conferma, sistema registra.

## 2. Componenti principali
- Document Classifier: classifica tipologia, modulo e priorita' del documento.
- OCR Engine: estrae testo da immagini, scansioni e PDF non testuali.
- Parser Registry: cataloga parser disponibili, versioni, moduli e formati supportati.
- Module Parser: applica il parser corretto per modulo e tipo documento.
- Entity Extractor: riconosce soggetti, date, importi, sedi, riferimenti, scadenze e ruoli.
- Validation Engine: valida coerenza, campi obbligatori, formati e relazioni.
- Confidence Engine: assegna affidabilita' globale e per campo.
- Review Center: mostra dati estratti, anomalie e campi da confermare.
- Case Generator: crea fascicolo e pratica dopo conferma umana.
- Document Generator: genera atti, lettere, verbali, attestati e report dai dati confermati.
- Checklist Generator: aggiorna automaticamente checklist di modulo e fascicolo.
- Timeline Generator: registra eventi operativi e AI nel fascicolo.
- AI Assistant: risponde sul contesto del fascicolo, senza uscire dai dati disponibili.
- Audit AI Log: registra input, output, utente, data, modulo, documento sorgente e decisione finale.

## 3. Flusso generale

```text
Upload PDF/ZIP/EML/MSG/immagini
-> classificazione documenti
-> OCR se necessario
-> scelta parser corretto
-> estrazione dati
-> confidence per campo
-> validazione
-> revisione utente
-> creazione fascicolo
-> creazione pratica
-> generazione documenti
-> aggiornamento checklist
-> aggiornamento timeline
-> assistente AI del fascicolo
```

Il flusso deve essere asincrono dove necessario, tracciato in ogni passaggio e ripetibile su documenti nuovi o versioni successive.

## 4. Parser per modulo

### Mediazione
- istanza;
- procura;
- adesione;
- convocazione;
- verbale;
- accordo;
- dichiarazione imparzialita';
- assunzione incarico.

### Formazione
- iscrizione corso;
- programma;
- registro presenze;
- attestato;
- scheda docente.

### OCC
- istanza debitore;
- relazione particolareggiata;
- elenco creditori;
- documenti reddituali;
- stato famiglia;
- attivo/passivo.

### Orientamento
- richiesta informazioni;
- preventivo;
- documenti studente;
- iscrizione/immatricolazione;
- offerta economica.

### Crisi d'Impresa
- incarico advisor;
- bilanci;
- situazione debitoria;
- business plan;
- piano di risanamento.

## 5. Output standard del parser
Ogni parser deve restituire JSON strutturato.

```json
{
  "document_type": "istanza",
  "module": "mediazione",
  "case_type": "mediation",
  "extracted_entities": [],
  "extracted_fields": {},
  "missing_fields": [],
  "confidence_score": 0.0,
  "confidence_by_field": {},
  "suggested_actions": [],
  "validation_errors": [],
  "source_document_ids": []
}
```

Campi obbligatori:
- `document_type`;
- `module`;
- `case_type`;
- `extracted_entities`;
- `extracted_fields`;
- `missing_fields`;
- `confidence_score`;
- `confidence_by_field`;
- `suggested_actions`;
- `validation_errors`;
- `source_document_ids`.

## 6. Confidence Engine
Livelli:
- alta;
- media;
- bassa;
- da_verificare.

Regole:
- alta: dato coerente, fonte chiara, validazione superata;
- media: dato plausibile ma con piccoli dubbi o contesto incompleto;
- bassa: dato estratto ma non pienamente affidabile;
- da_verificare: dato mancante, ambiguo, contraddittorio o critico.

L'AI non deve mai creare definitivamente una pratica senza conferma umana.

## 7. Review Center
Il Review Center e' la schermata in cui l'operatore verifica le proposte dell'AI prima della creazione o aggiornamento del fascicolo.

La schermata deve mostrare:
- dati estratti;
- campi da verificare;
- documenti presenti;
- documenti mancanti;
- suggerimenti;
- conferma operatore.

Funzioni attese:
- modifica campi;
- accetta/rifiuta suggerimenti;
- marca un campo come confermato;
- collega documenti al fascicolo;
- avvia Case Generator solo dopo conferma.

## 8. Case Generator
Dopo conferma umana:
- crea fascicolo;
- crea procedura specifica;
- collega documenti;
- crea timeline;
- crea checklist;
- crea scadenze;
- genera documenti iniziali.

Il Case Generator deve usare i dati confermati, non direttamente l'output grezzo dell'AI.

## 9. AI Assistant
Ogni fascicolo deve avere un assistente dedicato che risponde solo sui dati del fascicolo.

Domande esempio:
- cosa manca?
- chi sono le parti?
- qual e' il prossimo adempimento?
- prepara convocazione;
- prepara verbale;
- riepiloga fascicolo;
- segnala criticita'.

Regole:
- l'assistente non deve inventare dati mancanti;
- deve citare il documento o la sezione da cui deriva la risposta quando disponibile;
- deve proporre azioni operative, non eseguirle senza conferma;
- deve rispettare permessi, organismo, sede e visibilita' documentale.

## 10. Sicurezza e audit
Ogni azione AI deve essere tracciata:
- input;
- output;
- utente;
- data;
- modulo;
- documento sorgente;
- decisione umana finale.

Audit richiesto:
- run AI;
- documenti elaborati;
- parser usato;
- versione prompt/modello;
- confidence;
- campi modificati dall'utente;
- conferma o rifiuto finale;
- azioni generate sul fascicolo.

## 11. Database Design
Tabelle previste o da armonizzare con `docs/04_DATABASE/DATABASE_DESIGN_MASTER.md`:

### `ai_runs`
Esecuzione AI principale.

Campi:
- `id`;
- `organization_id`;
- `office_id`;
- `module`;
- `case_id`;
- `run_type`;
- `provider`;
- `model_name`;
- `status`;
- `started_at`;
- `completed_at`;
- `created_by`;
- `metadata_json`.

### `ai_run_documents`
Documenti inclusi in una run.

Campi:
- `id`;
- `ai_run_id`;
- `document_id`;
- `document_type`;
- `processing_status`;
- `ocr_required`;
- `parser_code`;
- `metadata_json`.

### `ai_extracted_fields`
Campi estratti.

Campi:
- `id`;
- `ai_run_id`;
- `document_id`;
- `field_name`;
- `field_value`;
- `field_type`;
- `source_page`;
- `source_position_json`;
- `confirmed_value`;
- `status`.

### `ai_confidence_scores`
Confidence globale e per campo.

Campi:
- `id`;
- `ai_run_id`;
- `field_id`;
- `confidence_level`;
- `confidence_score`;
- `reason`;
- `created_at`.

### `ai_suggestions`
Suggerimenti operativi.

Campi:
- `id`;
- `ai_run_id`;
- `case_id`;
- `suggestion_type`;
- `suggestion_text`;
- `suggestion_json`;
- `status`;
- `reviewed_by`;
- `reviewed_at`.

### `ai_user_reviews`
Decisioni umane sul Review Center.

Campi:
- `id`;
- `ai_run_id`;
- `user_id`;
- `review_status`;
- `reviewed_at`;
- `confirmed_fields_json`;
- `rejected_fields_json`;
- `notes`.

### `ai_audit_logs`
Log di audit AI.

Campi:
- `id`;
- `ai_run_id`;
- `organization_id`;
- `user_id`;
- `module`;
- `event_type`;
- `input_hash`;
- `output_hash`;
- `source_document_id`;
- `human_decision`;
- `created_at`;
- `metadata_json`.

### `parser_runs`
Esecuzioni parser, gia' previste nel database master.

Campi:
- `id`;
- `organization_id`;
- `document_id`;
- `parser_code`;
- `status`;
- `started_at`;
- `completed_at`;
- `confidence_score`;
- `error_message`.

### `parser_results`
Risultati aggregati parser, gia' previsti nel database master.

Campi:
- `id`;
- `parser_run_id`;
- `result_type`;
- `result_json`;
- `confidence_score`.

## 12. API Design
Endpoint futuri:

### `POST /ai/intake`
Avvia una run AI su documenti caricati o pacchetti documentali.

### `POST /ai/classify-documents`
Classifica documenti per modulo, tipo e priorita'.

### `POST /ai/extract-fields`
Esegue OCR, parser e extraction sui documenti selezionati.

### `POST /ai/review-confirm`
Registra la conferma utente dei dati estratti e delle azioni suggerite.

### `POST /ai/generate-case`
Crea fascicolo, pratica e oggetti collegati usando dati confermati.

### `GET /ai/runs/{id}`
Restituisce stato, metadati e audit della run AI.

### `GET /ai/runs/{id}/results`
Restituisce classificazioni, estrazioni, confidence, suggerimenti e validazioni.

## 13. Provider Strategy
Nexus AI Core deve supportare provider sostituibili e configurabili:
- parser locale;
- OCR locale;
- OpenAI;
- altri provider AI;
- modelli specializzati.

Principi:
- separazione tra AI Core e provider;
- configurazione per organismo e modulo;
- nessuna dipendenza rigida da un singolo modello;
- fallback locale quando possibile;
- tracciamento provider, modello e versione;
- policy di sicurezza per dati sensibili e documenti.

## 14. Regola fondamentale
AI propone, utente conferma, sistema registra.

Questa regola vale per:
- creazione fascicoli;
- creazione pratiche;
- estrazione dati;
- generazione documenti;
- suggerimenti operativi;
- checklist;
- timeline;
- decision support.

## 15. Relazione con i moduli Nexus
Nexus AI Core e' un motore trasversale. I moduli verticali non devono duplicare logica AI, ma registrare parser, regole, campi obbligatori, validazioni e documenti specifici nel Parser Registry e nel Review Center.

Ogni modulo deve definire:
- documenti supportati;
- campi attesi;
- validazioni;
- azioni suggeribili;
- output documentali;
- eventi timeline;
- checklist generate.
