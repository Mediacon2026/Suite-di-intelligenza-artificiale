# Nexus ERP - Installazione Mediacon

**Versione progetto:** Nexus ERP 0.1.1 Alpha Recovery (`0.1.1-alpha-recovery`)

Nexus ERP e' una piattaforma software modulare per la gestione industriale di organismi, sedi e servizi professionali collegati a mediazione, formazione, OCC, orientamento, crisi d'impresa, CRM, fascicoli, documenti, pagamenti, workflow, analytics e funzioni AI.

Mediacon e' la prima installazione pilota del prodotto Nexus ERP. Il repository mantiene quindi una doppia lettura:

- Nexus ERP: prodotto multi-organismo, configurabile e riutilizzabile in contesti diversi;
- Mediacon: ambiente pilota iniziale, con configurazioni e priorita' operative specifiche.

## Prodotto e installazione

Nexus ERP deve restare indipendente dalla singola installazione. Le funzioni comuni devono essere progettate nel Nexus Kernel e nei motori trasversali, mentre le esigenze Mediacon devono essere gestite tramite configurazioni, parametri, workflow, template e moduli attivabili.

Il prodotto e' multi-organismo e multi-sede: puo' supportare piu' entita' operative, ognuna con utenti, ruoli, permessi, sedi, regole economiche, workflow, template, documenti e report dedicati.

## Obiettivo del progetto

L'obiettivo e' costruire un ERP professionale, tracciabile e scalabile, organizzato per moduli funzionali e release governate. Il progetto deve mantenere una separazione chiara tra codice applicativo, database, documentazione, template, script e test, cosi' da sostenere sviluppo progressivo, controllo qualita' e manutenzione nel tempo.

## Struttura repository

```text
.
+-- ai/                  # componenti e asset collegati alle funzioni AI
+-- api/                 # materiali e specifiche API di supporto
+-- backend/             # applicazione backend e logica server
+-- database/            # schema, migrazioni e script database
+-- docker/              # configurazioni container
+-- docs/                # documentazione di progetto, architettura, requisiti e moduli
+-- frontend/            # applicazione frontend web
+-- scripts/             # script operativi locali
+-- templates/           # template documentali o applicativi
+-- tests/               # test automatici e materiali di verifica
+-- uploads/             # area locale per file caricati durante lo sviluppo
```

## Governance documentale

La documentazione principale e' organizzata in `docs/`:

```text
docs/
+-- 00_PROJECT/
+-- 01_ARCHITECTURE/
+-- 02_REQUIREMENTS/
+-- 03_MODULES/
+-- 04_DATABASE/
+-- 05_API/
+-- 06_TEST/
+-- 07_MANUALS/
+-- MODULE_INDEX.md
```

## Metodo di sviluppo per release

Lo sviluppo procede per release incrementali. Ogni release deve avere:

- obiettivo funzionale chiaro;
- moduli coinvolti identificati tramite `docs/MODULE_INDEX.md`;
- requisiti aggiornati in `docs/02_REQUIREMENTS/`;
- impatti architetturali e database documentati;
- API aggiornate in `docs/05_API/API_REFERENCE.md`;
- test registrati in `docs/06_TEST/TEST_BOOK.md`;
- changelog e release notes aggiornati in `docs/00_PROJECT/`.

## Alpha Recovery 0.1.1

Nexus ERP 0.1.1 Alpha Recovery consolida la prima baseline integrata del prodotto senza dichiarare completi i provider esterni o gli engine ancora placeholder. Include:

- Configuration Center;
- CRM base;
- Mediazioni base;
- Fascicoli;
- Parser Mediazione;
- Intake Engine;
- Nexus Kernel 1.0.

La release Recovery aggiunge migrazioni additive tracciate, parser con ragioni della pretesa, ciclo Next Action post-incontro, test PostgreSQL e recupero Git. Webex, email/PEC, firma e conservazione restano integrazioni non operative.

## Ruolo di MODULE_INDEX.md

`docs/MODULE_INDEX.md` e' il registro centrale dei codici modulo. Serve a collegare roadmap, requisiti, sviluppo, test, changelog e release notes con una nomenclatura stabile. Ogni nuova funzionalita' o modifica rilevante deve indicare i codici modulo impattati.

Sprint 1 - avvio locale su Windows senza Docker.

Stack locale:

- PostgreSQL locale
- FastAPI su http://localhost:8000
- React/Vite su http://localhost:5173

## Database locale

Il progetto usa il database PostgreSQL esistente:

```text
database: mediacon_hub_erp
utente: postgres
password: Mediacon2026!
host: localhost
porta: 5432
```

Non cancellare dati esistenti. Se il database non contiene ancora le tabelle minime, puoi eseguire manualmente lo script sicuro:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\local_minimal_schema.sql
```

Lo script usa `CREATE TABLE IF NOT EXISTS` e crea solo:

- `organizations`
- `offices`
- `users`
- `roles`
- `contacts`

## Backend

1. Entra nella cartella del progetto.

```powershell
cd C:\Users\PC-Gabriele\Downloads\Mediacon-Hub-ERP-Complete
```

2. Crea l'ambiente virtuale Python.

```powershell
py -3 -m venv backend\.venv
```

3. Attiva l'ambiente virtuale.

```powershell
backend\.venv\Scripts\activate
```

4. Installa le dipendenze.

```powershell
pip install -r backend\requirements.txt
```

5. Crea il file `.env` reale partendo dall'esempio.

```powershell
copy backend\.env.example backend\.env
```

Il contenuto atteso è:

```text
DATABASE_URL=postgresql+psycopg://postgres:Mediacon2026%21@localhost:5432/mediacon_hub_erp
APP_ENV=local
APP_HOST=127.0.0.1
APP_PORT=8000
```

Nota: nella URL la `!` della password è scritta come `%21`.

6. Avvia FastAPI.

```powershell
cd backend
uvicorn app.main:app --host 127.0.0.1 --port 8000 --reload
```

Verifica:

- http://localhost:8000/health
- http://localhost:8000/modules
- http://localhost:8000/docs

Endpoint richiesti nello Sprint 1:

- `GET /health` risponde `{"status":"ok"}`
- `GET /modules` restituisce l'elenco moduli

Endpoint CRM disponibili dallo Sprint 2:

- `GET /contacts`
- `GET /contacts/{id}`
- `POST /contacts`
- `PUT /contacts/{id}`
- `DELETE /contacts/{id}`

All'avvio il backend aggiorna in modo non distruttivo la tabella `contacts` e inserisce i contatti demo della Master Anagrafica se non sono gia presenti. Lo stesso aggiornamento e disponibile anche come script SQL manuale:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\002_master_anagrafica_contacts.sql
```

Endpoint Mediazioni disponibili dallo Sprint 3:

- `GET /mediations`
- `GET /mediations/{id}`
- `POST /mediations`
- `PUT /mediations/{id}`
- `DELETE /mediations/{id}`
- `GET /mediation-tariffs`
- `POST /mediation-tariffs`
- `PUT /mediation-tariffs/{id}`
- `GET /mediation-tariffs/{id}/rows`
- `POST /mediation-tariffs/{id}/rows`
- `PUT /mediation-tariff-rows/{id}`
- `POST /mediations/{id}/calculate-fees`

All'avvio il backend aggiorna in modo non distruttivo anche le tabelle per procedimenti di mediazione, sedi, tariffari e righe tariffarie. La migrazione manuale e disponibile qui:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\003_mediazioni_tariffari.sql
```

Endpoint Configuration Center disponibili dallo Sprint 4:

- `GET /config/modules`
- `PUT /config/modules/{id}`
- `GET /config/numbering-rules`
- `POST /config/numbering-rules`
- `PUT /config/numbering-rules/{id}`
- `GET /config/matters`
- `POST /config/matters`
- `PUT /config/matters/{id}`
- `DELETE /config/matters/{id}`
- `GET /config/workflows`
- `POST /config/workflows`
- `PUT /config/workflows/{id}`
- `GET /config/workflows/{id}/steps`
- `POST /config/workflows/{id}/steps`
- `PUT /config/workflow-steps/{id}`
- `DELETE /config/workflow-steps/{id}`
- `GET /config/roles`
- `POST /config/roles`
- `PUT /config/roles/{id}`
- `GET /config/permissions`
- `GET /config/roles/{id}/permissions`
- `POST /config/roles/{id}/permissions`
- `GET /config/economic-parameters`
- `POST /config/economic-parameters`
- `PUT /config/economic-parameters/{id}`
- `GET /audit-logs`

Migrazione manuale Configuration Center:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\004_configuration_center.sql
```

Endpoint regole economiche disponibili dallo Sprint 4 Task 002:

- `GET /economic-rules`
- `POST /economic-rules`
- `PUT /economic-rules/{id}`
- `POST /mediations/{id}/calculate-economic-split`

Migrazione manuale regole economiche Mediacon:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\005_economic_rules_mediacon.sql
```

Hotfix motore economico:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\006_hotfix_economic_engine.sql
```

Endpoint Document Intake Mediazioni disponibili dallo Sprint 5:

- `POST /mediations/intake/pdf`
- `POST /mediations/intake/zip`
- `POST /mediations/intake/create`

Migrazione manuale Document Intake:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\007_document_intake_ai.sql
```

Endpoint Case Engine / Fascicoli disponibili dallo Sprint 6:

- `GET /cases`
- `GET /cases/{id}`
- `POST /cases`
- `PUT /cases/{id}`
- `DELETE /cases/{id}`
- `GET /cases/{id}/documents`
- `POST /cases/{id}/documents`
- `POST /cases/{id}/upload-zip`
- `GET /cases/{id}/timeline`
- `GET /cases/{id}/tasks`
- `POST /cases/{id}/tasks`
- `GET /cases/{id}/deadlines`
- `POST /cases/{id}/deadlines`
- `GET /cases/{id}/contacts`
- `POST /cases/{id}/contacts`

Migrazione manuale Case Engine:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\008_case_engine.sql
```

Endpoint AI Document Engine disponibili dallo Sprint 7:

- `POST /cases/{id}/documents/bulk`
- `GET /cases/{id}/checklist`
- `GET /cases/{id}/suggestions`
- `POST /cases/{id}/assistant`
- `GET /case-search?q=...`

Migrazione manuale AI Document Engine:

```powershell
psql -U postgres -d mediacon_hub_erp -f database\migrations\009_ai_document_engine.sql
```

## Frontend

Da una seconda finestra PowerShell:

```powershell
cd C:\Users\PC-Gabriele\Downloads\Mediacon-Hub-ERP-Complete\frontend
npm install
npm run dev
```

Apri:

```text
http://localhost:5173
```

La Dashboard chiama `http://localhost:8000/health` e mostra se il backend è online o offline.

## Script Windows

Avvio backend:

```powershell
scripts\start-backend.bat
```

Avvio frontend:

```powershell
scripts\start-frontend.bat
```

## Moduli navigabili

- Dashboard
- CRM
- Mediazioni
- Formazione
- Orientamento
- OCC
- Crisi Impresa
- Documenti
- Pagamenti
- Scadenze
- Impostazioni

I moduli CRM, Mediazioni e Impostazioni contengono le prime funzioni reali. Gli altri moduli restano pagine vuote e navigabili. Login, AI, PDF, PEC, firma digitale e funzioni avanzate non sono implementati.
