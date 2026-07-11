# Database Architecture

## Scopo
Definire i principi di progettazione del database di Mediacon Nexus ERP e la relazione tra modello dati, moduli e release.

## Principi
- Tabelle progettate per domini coerenti e responsabilita' chiare.
- Migrazioni non distruttive quando possibile.
- Chiavi primarie e relazioni esplicite.
- Auditabilita' degli eventi rilevanti.
- Separazione tra dati configurabili, dati transazionali e dati documentali.

## Aree dati principali
- Organismi, sedi, utenti, ruoli e permessi.
- Anagrafiche, contatti, CRM e lead.
- Fascicoli, documenti, timeline, task e scadenze.
- Procedimenti verticali per mediazione, formazione, OCC, orientamento e crisi.
- Parametri economici, regole, pagamenti e compensi.
- Configurazioni workflow, template, parser e AI.

## Governance schema
Ogni nuova tabella o colonna deve essere documentata in `docs/04_DATABASE/DATA_DICTIONARY.md` e, quando rilevante, rappresentata in `docs/04_DATABASE/ER_DIAGRAM.md`.
