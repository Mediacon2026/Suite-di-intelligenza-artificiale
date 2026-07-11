# RECOVERY-001 — Database Recovery Map

Data verifica: 2026-07-11  
Istanza verificata: `mediacon_hub_erp` su PostgreSQL 18.4  
Metodo: introspezione SQLAlchemy in sola lettura, confronto con schema/migrazioni/bootstrap, quindi applicazione delle sole migrazioni additive RECOVERY-001.

## Fonti confrontate

| Fonte | Stato | Nota |
|---|---|---|
| `database/schema.sql` | PARZIALE | Baseline legacy/base; non contiene l'intero ERP moderno |
| `database/local_minimal_schema.sql` | PARZIALE | Avvio minimo con 5 tabelle |
| `database/migrations/002`–`010` | PARZIALE | Copertura progressiva di CRM, mediazioni, configurazione, economia, intake, case e documenti |
| `backend/app/main.py` bootstrap | PARZIALE | Conteneva tabelle runtime non formalizzate in migrazione |
| PostgreSQL reale prima del recupero | PARZIALE | 30 tabelle applicative; mancavano quattro tabelle runtime moderne |
| PostgreSQL reale dopo il recupero | COMPLETO | Tabelle richieste localmente presenti; migrazioni recovery tracciate |

## Migrazioni Recovery applicate

| Migrazione | Effetto | Sicurezza dati |
|---|---|---|
| `011_recovery_runtime_tables.sql` | Crea `intake_sessions`, `intake_session_documents`, `mediator_assignments`, `email_messages`, indici e tracking migrazioni | Solo `CREATE ... IF NOT EXISTS`; nessuna eliminazione |
| `012_recovery_office_contacts.sql` | Completa email e PEC di Pachino/Napoli | Usa `COALESCE`: non sovrascrive valori esistenti |

Il runner `scripts/apply_migrations.py` registra i file applicati nella tabella `schema_migrations`, esegue commit per singola migrazione e rollback su errore.

## Tabelle presenti e responsabilità

| Area | Tabelle verificate |
|---|---|
| Organizzazione e CRM | `organizations`, `offices`, `contacts` |
| Mediazioni | `mediations`, `mediation_parties`, `mediation_lawyers`, `mediation_sessions`, `mediation_documents` |
| Tariffe ed economia | `mediation_tariffs`, `mediation_tariff_rows`, `economic_rules` |
| Configuration Center | `config_modules`, `config_numbering_rules`, `config_mediation_matters`, `config_workflows`, `config_workflow_steps`, `config_roles`, `config_permissions`, `config_role_permissions`, `config_economic_parameters` |
| Intake | `document_intakes`, `intake_sessions`, `intake_session_documents` |
| Fascicoli | `cases`, `case_documents`, `case_contacts`, `case_tasks`, `case_deadlines`, `case_timeline` |
| Documenti e nomine | `document_templates`, `generated_documents`, `mediator_assignments`, `email_messages` |
| Audit e governance DB | `audit_logs`, `schema_migrations` |

## Verifiche dati e regole

- Casarano: indirizzo, email e PEC coerenti con Parser Registry.
- Pachino: indirizzo, email e PEC coerenti dopo migrazione additiva.
- Napoli: indirizzo, email e PEC coerenti dopo migrazione additiva.
- Regole economiche presenti: 50% mediatore sede principale, 70% sede operativa, 30% Mediacon.
- I test verificano che Pachino e Napoli non producano compenso mediatore automatico.
- Sono presenti record duplicati storici per alcune chiavi `economic_rules`. Non sono stati cancellati o deduplicati per rispettare il vincolo di conservazione dati. Il calcolo attuale seleziona una regola coerente, ma serve una futura bonifica governata con audit.

## Test database

| Test | Esito |
|---|---|
| Connessione PostgreSQL reale | SUPERATO |
| Presenza tabelle recovery | SUPERATO |
| Rollback transazionale reale | SUPERATO: il record annullato risulta assente |
| Recapiti tre sedi | SUPERATO |
| Regole economiche tre sedi | SUPERATO con test deterministici |

## Rischi residui

- `schema.sql`, schema minimo, migrazioni storiche e bootstrap non sono ancora una singola baseline canonica.
- Le migrazioni `002`–`010`, già materialmente presenti nel DB prima del tracking, non sono state retro-marcate automaticamente per evitare dichiarazioni non dimostrabili.
- Le duplicazioni storiche in `economic_rules` richiedono una procedura di deduplica approvata, con backup e audit.
- Provider esterni e workflow legali telematici richiederanno ulteriori tabelle/configurazioni solo quando implementati realmente.

