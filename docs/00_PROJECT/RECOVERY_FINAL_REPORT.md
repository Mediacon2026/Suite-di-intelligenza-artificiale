# RECOVERY-001 — Final Recovery Report

Data: 2026-07-11  
Versione: **Nexus ERP 0.1.1 Alpha Recovery** (`0.1.1-alpha-recovery`)

## Cosa è stato trovato

- Baseline FastAPI/React/PostgreSQL ampia e compilabile, concentrata soprattutto in `main.py` e `main.jsx`.
- CRM, Configuration Center, mediazioni, fascicoli, intake, parser, kernel, Next Action e Automation presenti con livelli diversi di completezza.
- Repository Git non valido e privo di cronologia recuperabile.
- PostgreSQL reale online con 30 tabelle prima del recupero.
- Quattro tabelle usate dal backend create solo dal bootstrap runtime e non formalizzate in migrazione.
- Kernel e integrazioni con numerosi contratti placeholder/in-memory.

## Cosa è stato recuperato o consolidato

- Git locale valido sul branch `main`, preservando `.git_backup_corrotto`, con commit iniziale Recovery creato e senza push.
- `.gitignore` professionale esteso ai principali segreti e artefatti sensibili.
- Migrazioni additive e runner tracciato; database reale allineato senza eliminazioni.
- Recapiti ufficiali di Casarano, Pachino e Napoli allineati tra parser e database senza sovrascrivere valori esistenti.
- Parser Mediazione esteso all'estrazione delle ragioni con confidence per campo.
- Next Action Engine esteso realmente dal primo incontro fino a verbale, DGStat e archiviazione.
- Validazione delle azioni completate: chiavi sconosciute vengono rifiutate.
- Fallback database ripulito da credenziali hard-coded.
- pytest installato nel virtual environment e dichiarato nelle dipendenze sviluppo.
- Versione e documentazione release aggiornate a `0.1.1-alpha-recovery`.

## Cosa è stato ricostruito

- Tabelle `intake_sessions`, `intake_session_documents`, `mediator_assignments`, `email_messages` tramite migrazione non distruttiva.
- Tracking migrazioni tramite `schema_migrations`.
- Suite Recovery per parser, ciclo Next Action, regole economiche, schema DB, sedi e rollback PostgreSQL.

## Cosa resta incompleto

- Rules, Workflow, Document, Notification e Audit Engine del Kernel contengono ancora parti placeholder o in-memory.
- Automation Engine esegue contratti e registra timeline/audit via API, ma alcune azioni restano preparatorie/placeholder.
- Invio reale email/PEC non implementato.
- Webex reale non implementato.
- Firma digitale e conservazione CAD usano provider mock.
- Workflow completi degli artt. 8-bis e 8-ter restano progettuali.
- OCR avanzato e validazione del parser su un corpus documentale reale anonimizzato non eseguiti.
- Collaudo visuale browser completo non eseguito.
- Duplicati storici in `economic_rules` non rimossi per evitare perdita dati.

## Test finali

| Verifica | Risultato |
|---|---|
| Python `py_compile` | SUPERATO |
| Runner interno | 65/65 SUPERATI |
| pytest | 65/65 SUPERATI |
| Rollback PostgreSQL reale | SUPERATO |
| Schema/tabelle recovery | SUPERATO |
| Recapiti sedi | SUPERATO |
| Regole economiche Casarano/Pachino/Napoli | SUPERATO |
| Frontend `npm run build` | SUPERATO, 1771 moduli trasformati |

Avvisi non bloccanti: FastAPI segnala la deprecazione di `@app.on_event("startup")` in favore del lifespan API.

## Rischi rimanenti

- L'ampiezza di `backend/app/main.py` e `frontend/src/main.jsx` aumenta il rischio di regressioni.
- Il bootstrap SQL in `main.py` duplica ancora parte delle responsabilità delle migrazioni.
- Engine in-memory non sono adatti a scenari multi-processo o audit persistente.
- Le integrazioni esterne richiedono credenziali, provider, sicurezza, retry, idempotenza e collaudi dedicati.
- La nuova baseline Git non contiene la cronologia precedente perché non era presente nel workspace.
- Il commit iniziale è stato creato con l'identità Git locale fornita dall'utente.

## File principali modificati o creati

- `.gitignore`
- `backend/app/database.py`
- `backend/app/main.py`
- `backend/app/parsers/mediation_parser.py`
- `backend/app/kernel/next_action_engine.py`
- `backend/app/tests/test_api_contracts.py`
- `backend/app/tests/test_recovery_001.py`
- `backend/app/tests/test_database_recovery.py`
- `backend/requirements-dev.txt`
- `database/migrations/011_recovery_runtime_tables.sql`
- `database/migrations/012_recovery_office_contacts.sql`
- `scripts/apply_migrations.py`
- `frontend/package.json`, `frontend/package-lock.json`
- `README.md`
- documenti RECOVERY-001, changelog, release notes, versioning e test report.

## Prossimo task consigliato

**STABILITY-002 — Kernel Persistence & Automation Hardening**: estrarre gradualmente servizi da `main.py`, collegare Event/Workflow/Rules/Audit/Document Engine alle tabelle persistenti, rendere l'Automation Engine idempotente e aggiungere test end-to-end con schema PostgreSQL isolato. I provider esterni devono essere affrontati in task separati, senza simulare completezza.
