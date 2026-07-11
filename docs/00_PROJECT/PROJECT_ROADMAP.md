# Project Roadmap

## Progetto
Mediacon Nexus ERP e' un sistema gestionale modulare per organismi, sedi e servizi professionali collegati a mediazione, formazione, OCC, orientamento, crisi d'impresa, CRM, gestione documentale, workflow e funzioni AI.

## Obiettivo della roadmap
La roadmap definisce la sequenza industriale delle release, separando stabilizzazione tecnica, copertura funzionale e consolidamento operativo. Ogni release deve essere collegata ai codici modulo presenti in `docs/MODULE_INDEX.md`.

## Release previste

### Release 0 - Foundation
- Consolidamento repository, documentazione, convenzioni di versione e governance.
- Allineamento architettura backend, frontend, database e API.
- Definizione del perimetro minimo per ambienti locali e futuri ambienti di test.

### Release 0.8 - Portali
- Portale Avvocati per deposito istanze, consultazione pratiche, adesione, upload documenti, pagamenti, firma, download verbali, notifiche e calendario.
- Portale Mediatori per nomine, accettazione incarico, firma documenti, agenda, verbali, compensi, statistiche personali, aggiornamenti professionali e scadenze formative.
- Portale Parti per adesione, caricamento documenti, pagamento, partecipazione Webex, firma, consultazione appuntamenti e download documenti.
- Portale Admin Organismo e Super Admin SaaS per gestione tenant, utenti, moduli e configurazioni.

### Release 0.9 - Marketplace SaaS
- Nexus Marketplace per attivazione moduli, piani di billing, configurazione tenant e white label.
- Moduli attivabili per Mediazione, Formazione, OCC, Orientamento, Crisi d'Impresa, servizi futuri e componenti trasversali.
- Predisposizione commerciale SaaS multi-organismo, multi-sede e multi-tenant.

### Release 1.0 - Commercial MVP
- Obiettivo operativo: da istanza ricevuta a fascicolo completo, mediatore nominato e convocazione pronta in meno di 3 minuti.
- Intake PDF/ZIP, Review Center, Fascicolo universale, creazione automatica mediazione, pacchetto nomina, convocazione, checklist, timeline e dashboard minima.
- Prima versione commercializzabile per Organismi di Mediazione con estensione progressiva a portali e marketplace.

### Release 1 - Core Platform
- Configuration Center, autenticazione, ruoli, permessi e audit log.
- Multi organismo, multi sede e parametri di sistema.
- Primo livello di notification, document, workflow ed economic engine.

### Release 2 - Case Engine e CRM
- Fascicolo universale, contatti, documenti, timeline, attivita' e scadenze.
- CRM operativo per lead, contatti, follow up e statistiche iniziali.
- Integrazione base tra fascicoli, anagrafiche e moduli verticali.

### Release 3 - Verticali Operativi
- Mediazione, formazione, OCC, orientamento e crisi d'impresa.
- Funzioni verticali per procedure, calendari, documenti, pagamenti, statistiche e report.
- Standardizzazione dei flussi tramite workflow engine.

### Release 4 - AI, Parser e Analytics
- Parser registry, OCR, classificazione documentale e confidence engine.
- AI assistant, report generator e decision support.
- Dashboard BI, KPI, forecast ed export direzionali.

## Criteri di avanzamento
- Ogni release deve avere requisiti approvati, test book aggiornato e note di rilascio.
- Ogni modulo rilasciato deve avere codice, descrizione, stato e dipendenze documentate.
- Nessuna release deve introdurre modifiche non tracciate in `CHANGELOG.md` e `RELEASE_NOTES.md`.
