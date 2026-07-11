# Alpha Test Report

## Versione
Nexus ERP 0.1.1 Alpha Recovery (`0.1.1-alpha-recovery`)

## Data
2026-07-11

## Ambito
Stabilizzazione Alpha del flusso:

```text
Intake -> Parser -> Case -> Workflow -> Document -> Audit
```

Componenti inclusi:
- Configuration Center;
- CRM base;
- Mediazioni base;
- Fascicoli;
- Parser Mediazione;
- Intake Engine;
- Nexus Kernel 1.0.

## Test eseguiti

| Categoria | Comando / metodo | Esito |
| --- | --- | --- |
| Python compile | `backend\.venv\Scripts\python.exe -m py_compile` su tutti i file Python backend | Superato |
| Test unitari Kernel | `backend\.venv\Scripts\python.exe backend\app\tests\run_alpha_tests.py` | Superato |
| Test integrazione | Incluso nel runner Alpha | Superato |
| Test API | Incluso nel runner Alpha con client ASGI locale | Superato |
| pytest | `backend\.venv\Scripts\python.exe -m pytest backend/app/tests -q` | Superato |
| PostgreSQL rollback | Inserimento transazionale annullato e verificato assente | Superato |
| Frontend build | `npm run build` in `frontend/` | Superato |

## Risultati
- Test pytest eseguiti: 65.
- Test superati: 65.
- Test falliti: 0.

## Dettaglio copertura
- Case Engine: creazione fascicolo, cambio stato, link documento, link contatto, timeline.
- Rules Engine: regole attive placeholder, valutazione regola, caso senza regole.
- Workflow Engine: avvio, lettura step, avanzamento, completamento.
- Event Engine: pubblicazione evento, handler, elenco eventi per fascicolo.
- Document Engine: generazione placeholder, versione, hash, firma, conservazione.
- Notification Engine: notifica, email placeholder, PEC placeholder.
- Audit Engine: azione utente, AI, documento, firma.
- API: `/health`, `/health/full`, `/kernel/status`, `/kernel/events/test`, `/parsers/classify`, `/parsers/mediation/test`.
- Integrazione: Intake simulato, Parser Mediazione, Case Kernel, Workflow, Document, checklist, timeline/eventi, audit.
- ALPHA-002: riconoscimento sedi Pachino/Napoli, sede ambigua non inventata, regole Next Action iniziali.
- ALPHA-003: Automation Engine su nomina mediatore, firma documento, incontro creato, incontro completato, audit, timeline e next action.

## Test falliti
Nessun test applicativo fallito.

## Problemi noti
- `pytest` e' installato nel virtual environment ed e' dichiarato in `backend/requirements-dev.txt`; il runner interno resta disponibile come smoke suite leggera.
- I test API usano un client ASGI locale per evitare dipendenze aggiuntive come `httpx2`.
- Il collaudo visuale in browser resta manuale e va completato con backend attivo e dati reali.
- Alcuni engine Kernel restano placeholder sicuri e non persistono ancora su database dedicato.
- Il Next Action Engine calcola la prossima azione a runtime; una persistenza dedicata potra' essere introdotta in Alpha 0.2.
- L'Automation Engine v1 usa log in memoria per lo stato runtime; una tabella dedicata potra' essere introdotta in Alpha 0.2.

## Rischi
- Flussi che richiedono database reale, template documentali e provider esterni non sono coperti da test end-to-end completi.
- Event Engine Alpha mantiene eventi in memoria, quindi non e' ancora adatto a scenari multi-processo o audit persistente.
- Notification, firma e conservazione sono predisposizioni e non inviano o conservano realmente.

## Raccomandazioni prima della Alpha 0.2
- Mantenere aggiornate le dipendenze test dedicate al profilo di sviluppo.
- Introdurre test end-to-end con database temporaneo o schema dedicato.
- Rendere persistenti eventi Kernel e audit Kernel.
- Eseguire smoke test manuale browser su Dashboard, CRM, Mediazioni, Fascicoli, Impostazioni e Intake Review.
- Collegare gradualmente il Kernel alle logiche reali senza duplicare responsabilita' nei moduli verticali.
