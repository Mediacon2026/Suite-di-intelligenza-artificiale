# Changelog

Tutte le modifiche rilevanti del progetto Mediacon Nexus ERP devono essere registrate in questo file secondo un formato leggibile, cronologico e verificabile.

## Convenzione
- `Added`: nuove funzionalita' o nuovi documenti.
- `Changed`: modifiche a funzionalita' esistenti.
- `Fixed`: correzioni di bug o incoerenze.
- `Security`: interventi con impatto su sicurezza, accessi o dati.
- `Deprecated`: elementi ancora presenti ma destinati alla rimozione.
- `Removed`: elementi rimossi.

## Unreleased

## Nexus ERP 0.1.1 Alpha Recovery - 2026-07-11

### Added
- Inventario forense RECOVERY-001 e report di recupero.
- Migrazioni additive `011` e `012` con runner tracciato `scripts/apply_migrations.py`.
- Dipendenze sviluppo con pytest e test parser, Next Action, regole economiche, schema PostgreSQL e rollback reale.

### Changed
- Parser Mediazione esteso alle ragioni della pretesa con confidence per campo.
- Next Action esteso fino a esito, verbale, DGStat e archiviazione.
- Recapiti di Pachino e Napoli completati solo quando mancanti.
- Repository Git ricostruito sul branch `main`; nessun push eseguito.

### Security
- Rimosse credenziali dal fallback database nel codice.
- Esteso `.gitignore` a dump, chiavi, certificati, credenziali e backup Git corrotto.

### Added
- Creata struttura documentale professionale in `docs/` per governance, architettura, requisiti, moduli, database, API, test e manuali.
- Introdotto `docs/MODULE_INDEX.md` come registro centrale dei codici modulo.
- Definita baseline `Nexus ERP Alpha 0.1.0`.
- Aggiunti contratti API Kernel, struttura errori Kernel e health check esteso.
- Aggiunti test unitari Kernel, test API e test integrazione Alpha per il flusso Intake -> Parser -> Case -> Workflow -> Document -> Audit.
- Aggiunto ALPHA-002 Mediazione Zero Click con wizard Intake operativo, creazione completa mediazione, documenti iniziali, primo incontro e Next Action Engine.
- Aggiunti endpoint `POST /intake/{id}/confirm-and-create-mediation`, `GET /mediations/{id}/next-action`, `POST /mediations/{id}/next-action/complete` e `GET /mediations/{id}/creation-summary`.
- Aggiunto ALPHA-003 Nexus Automation Engine v1 con regole su nomina mediatore, firma documento, incontro creato/completato, audit, timeline e next action.
- Aggiunti endpoint `POST /automation/run/{case_id}`, `GET /automation/status/{case_id}` e `GET /automation/logs/{case_id}`.

### Changed
- README aggiornato con nome progetto, obiettivo, struttura repository, metodo di sviluppo per release e ruolo del Module Index.
- Rafforzato riconoscimento sedi Mediacon per Casarano, Pachino e Napoli senza inventare sedi ambigue.
- La numerazione mediazioni usa la regola configurata nel Configuration Center quando disponibile.

## Storico release

## Nexus ERP Alpha 0.1.0 - 2026-07-10

### Added
- Configuration Center.
- CRM base.
- Mediazioni base.
- Fascicoli.
- Parser Mediazione.
- Intake Engine.
- Nexus Kernel 1.0.
- Test di stabilita' Alpha per Kernel, API e flusso integrato.
- Next Action Engine Alpha.
- Flusso Mediazione Zero Click da Intake a prossima azione.
- Automation Engine Alpha per reazione automatica agli eventi del fascicolo.

### Changed
- Formalizzata la versione `0.1.0-alpha` come baseline tecnica.

Le release ufficiali saranno aggiunte in ordine cronologico inverso a partire dalla prima versione governata.
