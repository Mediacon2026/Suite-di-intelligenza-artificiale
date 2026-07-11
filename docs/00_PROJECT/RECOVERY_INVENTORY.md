# RECOVERY-001 — Inventario forense del workspace

Data analisi: 2026-07-11  
Ambito: **FASE A — sola analisi e inventario**  
Versione rilevata nel codice: `0.1.0-alpha` (la versione richiesta `0.1.1-alpha-recovery` non è stata ancora applicata).

## Metodo e criteri

L'inventario deriva dalla scansione ricorsiva del workspace, lettura mirata del codice, ricerca di endpoint/tabelle/placeholder e verifiche locali. In questa fase non sono stati recuperati moduli, modificate regole di business, inizializzato Git o applicate migrazioni.

Stati usati esclusivamente: `COMPLETO`, `PARZIALE`, `PLACEHOLDER`, `MANCANTE`, `ROTTO`, `DA TESTARE`.

`COMPLETO` significa completo rispetto al perimetro osservabile e verificato in FASE A, non certificazione di produzione.

## Inventario reale

| Codice modulo | Componente | File | Stato reale | Dipendenze | Problemi | Azione necessaria |
|---|---|---|---|---|---|---|
| SPRINT-1 | Backend FastAPI e CORS | `backend/app/main.py` | DA TESTARE | FastAPI, SQLAlchemy | App importabile; `/health` verificato dai test interni; avvio server reale non eseguito | Smoke test con server e PostgreSQL locali |
| SPRINT-1 | Database connection | `backend/app/database.py`, `backend/.env.example` | DA TESTARE | PostgreSQL, psycopg, dotenv | URL configurabile; fallback contiene credenziali locali hard-coded; connessione reale non verificata | Rimuovere segreto dal fallback, verificare DB reale senza perdita dati |
| SPRINT-1 | Health API | `backend/app/main.py` | COMPLETO | FastAPI; PostgreSQL per `/health/full` | `/health` e `/health/full` presenti; il full health degrada correttamente se DB offline | Integrare in smoke test con DB online |
| SPRINT-1 | Frontend Vite/React | `frontend/src/main.jsx`, `frontend/src/styles.css`, `frontend/package.json` | COMPLETO | Node, React, Vite | Build Vite riuscita; `App.jsx` è quasi vuoto ma non è usato dall'entry point | Conservare; estrazione modulare graduale in FASE B |
| SPRINT-1 | Dashboard/sidebar/API URL | `frontend/src/main.jsx` | COMPLETO | Backend HTTP | Dashboard e sidebar presenti; API URL configurabile; nessun test UI | Aggiungere smoke test UI/API |
| SPRINT-2 | Master Anagrafica CRM | `backend/app/main.py`, `frontend/src/main.jsx`, `database/migrations/002_master_anagrafica_contacts.sql` | PARZIALE | contacts, organizations | CRUD, ricerca e dettaglio presenti; ruoli multipli modellati come flag; unicità/merge contatti non dimostrati | Test CRUD, duplicati, merge e ruoli multipli |
| SPRINT-3 | Mediazioni | `backend/app/main.py`, `frontend/src/main.jsx`, `database/migrations/003_mediazioni_tariffari.sql` | PARZIALE | contacts, offices, tariffs, cases | Lista, dettaglio, parti, avvocati, incontri e tariffari presenti; copertura end-to-end assente | Test API/DB per intero procedimento |
| SPRINT-3 | Tariffario e snapshot | `backend/app/main.py`, migrazioni `003`, `005`, `006` | PARZIALE | PostgreSQL | Calcoli e campi snapshot presenti; nessun test dedicato del tariffario rilevato | Test scaglioni, IVA, snapshot e immutabilità storica |
| SPRINT-3 | Regole economiche Mediacon | migrazioni `005`, `006`, `backend/app/main.py` | DA TESTARE | offices, economic_rules | Regole Casarano 50% e sedi operative 70/30 presenti nel codice/seed; correttezza numerica non testata in runner | Test Casarano, Pachino, Napoli e assenza compenso mediatore operativo |
| SPRINT-4 | Configuration Center | `backend/app/main.py`, `frontend/src/main.jsx`, `database/migrations/004_configuration_center.sql` | PARZIALE | config_* tables, audit_logs | Moduli, numerazioni, materie, workflow, ruoli, permessi, parametri e audit presenti; test specifici assenti | Test CRUD, permessi, numerazione concorrente e audit |
| CASE-001 | Fascicolo universale | `backend/app/main.py`, `backend/app/kernel/case_engine.py`, `database/migrations/008_case_engine.sql` | PARZIALE | cases e tabelle figlie | CRUD, documenti, contatti, task, scadenze, timeline e link mediazione presenti; kernel in-memory/placeholder | Test DB/API e consistenza collegamento mediazione-fascicolo |
| PARSER-001 | Parser Registry | `backend/app/parsers/parser_registry.py`, `base_parser.py`, `document_classifier.py` | COMPLETO | parser mediazione | Registry presente e usato dall'intake; test di flusso interno superato | Ampliare registry quando arrivano nuovi parser |
| PARSER-002 | Parser Mediazione | `backend/app/parsers/mediation_parser.py` | PARZIALE | regex, classifier | Sedi/contatti ufficiali e confidence per campo presenti; manca estrazione esplicita `ragioni`; regex non validate su corpus reale | Aggiungere `reasons`, corpus anonimizzato e test di tutti i campi |
| INTAKE-001 | Upload PDF/multiplo/ZIP | `backend/app/main.py`, `backend/app/ai/document_parser.py` | PARZIALE | pypdf, uploads, PostgreSQL | Endpoint PDF, ZIP e sessioni multiple presenti; sicurezza ZIP, OCR reale e file reali non verificati | Test file reali, ZIP traversal, limiti e rollback filesystem/DB |
| INTAKE-001 | Review e conferma | `backend/app/main.py`, `frontend/src/main.jsx` | PARZIALE | parser, intake tables | Review, mancanti e conferma presenti; alcuni mancanti vengono valorizzati `Da verificare` | Impedire persistenza di pseudo-dati come dati confermati; test UX/API |
| KERNEL-001 | Case Engine kernel | `backend/app/kernel/case_engine.py` | PLACEHOLDER | memoria processo | API di contratto presenti e testate, ma non persistenti | Collegare ai repository/servizi DB esistenti |
| KERNEL-001 | Rules Engine | `backend/app/kernel/rules_engine.py` | PLACEHOLDER | nessuna persistenza | Restituisce esplicitamente risultati placeholder | Implementare valutazione regole persistite |
| KERNEL-001 | Workflow Engine | `backend/app/kernel/workflow_engine.py` | PLACEHOLDER | memoria processo | Step iniziale `placeholder_step`; stato volatile | Collegare a config_workflows e timeline |
| KERNEL-001 | Event Engine | `backend/app/kernel/event_engine.py` | PARZIALE | memoria processo | Pubblicazione/handler testati; event store non persistente | Persistenza, idempotenza e retry |
| KERNEL-001 | Document Engine | `backend/app/kernel/document_engine.py` | PLACEHOLDER | hashing locale | Generazione marcata `generated_placeholder`; version/hash hanno contratti base | Collegare al generatore documenti reale in `main.py` |
| KERNEL-001 | Notification Engine | `backend/app/kernel/notification_engine.py` | PLACEHOLDER | nessun provider reale | Email e PEC dichiaratamente non inviate | Provider reali, outbox, retry e audit |
| KERNEL-001 | Audit Engine | `backend/app/kernel/audit_engine.py` | PLACEHOLDER | memoria/contratto | Restituisce `logged_placeholder`; esiste separatamente audit DB in `main.py` | Unificare con `audit_logs` persistente |
| KERNEL-001 | Kernel Service/API | `backend/app/kernel/kernel_service.py`, `backend/app/main.py` | PARZIALE | tutti gli engine | `/kernel/status` e `/kernel/events/test` presenti e testati; service include risultati placeholder | Integrare engine persistenti e test DB |
| STABILITY-001 | Test alpha interni | `backend/app/tests/run_alpha_tests.py`, `backend/app/tests/**` | COMPLETO | Python 3.14 venv | 55 test eseguiti, 55 passati; includono però contratti placeholder | Mantenere come smoke suite, non come certificazione funzionale |
| STABILITY-001 | pytest | `backend/app/tests/**`, `backend/requirements.txt` | ROTTO | pytest | Suite compatibile col runner interno, ma `pytest` non è installato né dichiarato | Aggiungere dipendenza sviluppo compatibile e rieseguire pytest |
| STABILITY-001 | Python compile | `backend/app/**/*.py` | COMPLETO | Python 3.14.6 | `py_compile` riuscito su tutti i sorgenti applicativi | Ripetere dopo le modifiche |
| STABILITY-001 | Frontend build | `frontend/**` | COMPLETO | Node/npm | `npm run build` riuscito (1771 moduli) | Ripetere dopo le modifiche; aggiungere test UI |
| ALPHA-002 | Next Action Engine | `backend/app/kernel/next_action_engine.py` | PARZIALE | contesto mediazione | Tutte le chiavi richieste sono dichiarate, ma il calcolo raggiunge solo fino a `WAIT_FIRST_MEETING`; complete/recalculate non persistono | Implementare stati post-incontro, DGStat, archivio e persistenza |
| ALPHA-002 | Zero Click transazionale | `backend/app/main.py` | PARZIALE | intake, cases, mediations, templates, DB | Endpoint usa commit unico e rollback su eccezione; crea molte entità richieste; workflow/checklist/automation completi non dimostrati | Test rollback reale e verifica atomica di ogni entità |
| ALPHA-002 | Documenti iniziali | `backend/app/main.py`, `templates/mediation/**` | PARZIALE | template ODT/DOCX, filesystem | Genera 4 tipi richiesti; presenti 9 template; intestazioni dinamiche da verificare visivamente per 3 sedi | Test contenuto/render Casarano, Pachino, Napoli e rollback file |
| ALPHA-003 | Automation Engine | `backend/app/kernel/automation/**`, API in `main.py` | PARZIALE | kernel, timeline, audit | Regole ed eventi richiesti presenti e testati come contratti; executor produce metadati/placeholder, non esegue sempre operazioni reali | Collegare azioni a generatori, email/outbox, Webex e DB; test idempotenza |
| MED-016 | Telematica/mista | `backend/app/integrations/video/**`, signature, preservation, documentazione MED-016 | PLACEHOLDER | Webex/firma/conservazione | Layer/provider presenti; API Webex, firma e conservazione reali esplicitamente non implementate; workflow legale solo progettuale | Implementare provider e tabelle/workflow con verifica normativa |
| DATABASE | Schema principale | `database/schema.sql` | PARZIALE | PostgreSQL | Contiene 19 tabelle legacy/base ma non tutte quelle usate dal backend moderno | Definire baseline canonica senza eliminare tabelle/dati |
| DATABASE | Schema locale minimo | `database/local_minimal_schema.sql` | PARZIALE | PostgreSQL | Solo 5 tabelle; insufficiente per backend completo | Documentare scopo o consolidare con migrazioni additive |
| DATABASE | Migrazioni | `database/migrations/002`–`010` | PARZIALE | PostgreSQL | Coprono CRM, mediazioni, config, economia, intake, cases, documenti; numerazione parte da 002; mancano tabelle create solo dal bootstrap di `main.py` | Mappa DB dettagliata e migrazioni additive per gap |
| DATABASE | Bootstrap automatico | `backend/app/main.py` | PARZIALE | PostgreSQL | Crea/alter molte tabelle all'avvio; duplica responsabilità delle migrazioni | Separare bootstrap dati da migrazioni dopo confronto DB reale |
| DATABASE | Tabelle solo bootstrap | `backend/app/main.py` | DA TESTARE | PostgreSQL | `intake_sessions`, `intake_session_documents`, `mediator_assignments`, `email_messages` non risultano create dai file di migrazione | Creare migrazioni non distruttive dopo ispezione DB reale |
| INTEGRATIONS | Firma digitale | `backend/app/integrations/signature/**` | PLACEHOLDER | provider mock | Interfaccia e mock presenti; nessuna firma reale | Integrare provider autorizzato e test sicurezza |
| INTEGRATIONS | Conservazione CAD | `backend/app/integrations/preservation/**` | PLACEHOLDER | provider mock | Predisposizione astratta presente; nessun conservatore reale | Integrare provider e workflow di conservazione |
| DOCS | Documentazione progetto | `docs/**`, `README.md` | PARZIALE | codice reale | Ampia documentazione presente; alcune affermazioni descrivono target o placeholder; versione ancora 0.1.0-alpha | Allineare solo in FASE B dopo recuperi verificati |
| SCRIPTS | Avvio locale/Docker | `scripts/**`, `docker-compose.yml`, `docker/**` | DA TESTARE | Docker, Node, Python, PostgreSQL | Script presenti; non eseguiti in FASE A | Smoke test controllato dei tre servizi |
| TEMPLATES | Template mediazione | `templates/mediation/**` | DA TESTARE | renderer DOCX/ODT | 9 file presenti; nessun file vuoto rilevato; contenuto non validato visivamente | Render e confronto per ciascun documento/sede |
| UPLOADS | Area upload | `uploads/.gitkeep` | COMPLETO | filesystem | Nessun documento utente presente nell'inventario; ignore configurato | Conservare esclusione Git e aggiungere policy sicurezza |
| GIT | Repository locale | `.git` | ROTTO | Git | Cartella `.git` presente ma vuota/non valida; Git segnala “not a git repository” | In FASE B, previa approvazione: rinomina backup, init, branch main; nessun push |
| GIT | Ignore sensibili | `.gitignore` | PARZIALE | Git | Esclude env, venv, node_modules, dist e uploads; non copre esplicitamente dump, chiavi e certificati | Estendere prima del primo commit |
| RECOVERY-001 | Versione recovery | README, backend, frontend, docs release | MANCANTE | governance release | Tutti i riferimenti principali sono ancora `0.1.0-alpha` | Applicare `0.1.1-alpha-recovery` soltanto in FASE B |
| RECOVERY-001 | Database recovery map | `docs/04_DATABASE/RECOVERY_DATABASE_MAP.md` | MANCANTE | analisi DB reale | Non ancora richiesta come output della sola FASE A; confronto preliminare incluso qui | Creare in FASE B dopo ispezione PostgreSQL |
| RECOVERY-001 | Git recovery report | `docs/00_PROJECT/GIT_RECOVERY_REPORT.md` | MANCANTE | recupero Git | Git non ancora recuperato per vincolo di fase | Creare durante recupero Git approvato |
| RECOVERY-001 | Final recovery report | `docs/00_PROJECT/RECOVERY_FINAL_REPORT.md` | MANCANTE | completamento FASE B | Non può esistere correttamente prima del recupero e dei test finali | Creare a conclusione verificata della FASE B |

## File vuoti, duplicazioni e concentrazione del rischio

- Nessun file applicativo vuoto è stato rilevato; `.git` è invece una directory vuota/non valida.
- `frontend/src/App.jsx` contiene solo un re-export/import minimale e non è l'entry point effettivo.
- Esistono implementazioni sovrapposte di parsing/classificazione in `backend/app/`, `backend/app/services/`, `backend/app/ai/` e `backend/app/parsers/`. Non sono state rimosse perché occorre prima stabilire i chiamanti reali.
- `backend/app/main.py` (circa 197 KB) incorpora API, modelli, bootstrap SQL, seed, servizi e transazioni; `frontend/src/main.jsx` (circa 111 KB) incorpora gran parte della UI. Sono funzionanti abbastanza da compilare/buildare, ma rendono rischioso ogni recupero indiscriminato.
- Il bootstrap SQL dentro `main.py` e le migrazioni hanno responsabilità duplicate e non perfettamente allineate.

## Endpoint rilevati e gap principali

Sono presenti gli endpoint richiesti `/health`, `/health/full`, `GET /kernel/status` e `POST /kernel/events/test`, oltre a gruppi API per automazione, parser, organizzazioni/sedi, contatti, mediazioni, intake, fascicoli, tariffari, regole economiche, incontri, Configuration Center e audit.

Gap verificati:

- nessun endpoint/provider operativo per invio reale email/PEC;
- nessuna integrazione Webex reale;
- firma e conservazione sono mock;
- Next Action dichiara il flusso completo ma non calcola gli stati successivi a `WAIT_FIRST_MEETING`;
- molti endpoint DB non sono stati eseguiti contro PostgreSQL nella FASE A.

## Database: confronto preliminare

- `schema.sql`: 19 tabelle base/legacy.
- `local_minimal_schema.sql`: 5 tabelle minime.
- migrazioni `002`–`010`: aggiungono CRM, mediazioni/tariffe, Configuration Center, regole economiche, intake, case engine e documenti generati.
- bootstrap `main.py`: crea 33 tabelle, incluse quattro non individuate nelle migrazioni (`intake_sessions`, `intake_session_documents`, `mediator_assignments`, `email_messages`).
- Lo stato del database PostgreSQL effettivo non è stato interrogato: non è quindi lecito affermare quali tabelle/dati siano già presenti nell'istanza locale.

## Test realmente eseguiti in FASE A

| Verifica | Esito | Nota |
|---|---|---|
| Python `py_compile` | COMPLETO | Tutti i file Python applicativi compilano con Python 3.14.6 |
| Runner interno | COMPLETO | 55 test eseguiti, tutti passati |
| pytest | ROTTO | Modulo `pytest` non installato nel venv e non dichiarato nelle dipendenze |
| Frontend `npm run build` | COMPLETO | Build Vite riuscita, 1771 moduli trasformati |
| PostgreSQL end-to-end | DA TESTARE | Non eseguito; richiede ispezione controllata dell'istanza e dei dati |
| Rollback reale Zero Click | DA TESTARE | Il codice chiama `rollback`, ma manca prova su DB reale e filesystem |

I 55 test interni coprono contratti di parser, sedi Pachino/Napoli, Next Action iniziale, automazione, API health/kernel/parser, engine kernel e un flusso intake-to-case in memoria. Non coprono adeguatamente tariffari, regole economiche, documenti renderizzati, transazioni PostgreSQL, rollback filesystem, Configuration Center, CRM completo o integrazioni reali.

## Conclusione FASE A

Il workspace è recuperabile e contiene una baseline ampia, ma non è corretto definirlo interamente recuperato. Le priorità tecniche per la FASE B sono: mettere in sicurezza Git e configurazione, fotografare il database reale, colmare le migrazioni additive, trasformare i placeholder kernel/automation/integration in operazioni persistenti, completare Next Action e parser, quindi aggiungere test DB/API/documenti prima di aggiornare la versione e i report finali.

**Stop di fase:** nessuna attività di FASE B deve iniziare senza approvazione esplicita dell'utente.

## Aggiornamento dopo FASE B approvata

FASE B eseguita il 2026-07-11. Gli stati aggiornati verificati sono:

- Git: da `ROTTO` a `COMPLETO` per la nuova baseline locale; cronologia precedente non recuperabile.
- pytest: da `ROTTO` a `COMPLETO`; 65 test passati.
- tabelle runtime database: da `DA TESTARE` a `COMPLETO`; migrazioni `011` e `012` applicate.
- Parser Mediazione: resta `PARZIALE` come parser regex, ma il campo `reasons` è stato recuperato e testato.
- Next Action Engine: `COMPLETO` per il calcolo delle azioni richieste; persistenza dedicata resta fuori dal componente.
- Regole economiche: `COMPLETO` per i casi richiesti Casarano/Pachino/Napoli verificati da test.
- Zero Click: resta `PARZIALE`; rollback DB reale verificato, ma non l'intero rollback combinato DB/filesystem con documenti reali.
- Automation, Webex, notifiche, firma, conservazione e alcuni engine Kernel restano `PARZIALE`/`PLACEHOLDER` come dichiarato nel report finale.

Dettagli: `RECOVERY_FINAL_REPORT.md`, `GIT_RECOVERY_REPORT.md` e `../04_DATABASE/RECOVERY_DATABASE_MAP.md`.
