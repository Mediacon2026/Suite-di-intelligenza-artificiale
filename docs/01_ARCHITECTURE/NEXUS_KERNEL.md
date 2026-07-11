# Nexus Kernel

## Visione
Il Nexus Kernel e' il cuore architetturale della piattaforma Nexus ERP. Fornisce i servizi comuni, le regole trasversali e i punti di estensione che permettono ai moduli verticali di condividere identita', configurazioni, fascicoli, documenti, workflow, notifiche, AI, analytics e API.

Il Kernel non e' un singolo modulo applicativo isolato: e' l'insieme coordinato dei motori di piattaforma che rendono Nexus ERP multi-organismo, configurabile e scalabile.

## Principi architetturali
- Centralizzare le capacita' comuni.
- Evitare duplicazioni tra moduli verticali.
- Rendere configurabili regole, workflow, template e parametri.
- Garantire tracciabilita', permessi e audit.
- Preparare integrazioni future tramite API e gateway.

## Componenti del Kernel

### Identity Engine
Gestisce identita', autenticazione, utenti, ruoli, permessi e contesto operativo. Deve supportare il modello multi-organismo e multi-sede.

### Configuration Engine
Gestisce configurazioni di sistema, moduli attivi, parametri, numerazioni, materie, impostazioni per organismo e sedi.

### Case Engine
Fornisce il fascicolo universale, con timeline, attivita', scadenze, checklist, contatti, documenti, versioning e archivio.

### Economic Engine
Gestisce parametri economici, regole di calcolo, tariffari, compensi, pagamenti e riparti.

### Document Engine
Gestisce documenti caricati, generati, versionati, classificati e collegati ai fascicoli o ai moduli verticali.

### Parser Engine
Gestisce parser registry, OCR, classificazione, estrazione dati e confidence engine per documenti e pacchetti informativi.

### Template Engine
Gestisce modelli documentali, variabili, regole di compilazione, versioni template e generazione output.

### Workflow Engine
Gestisce stati, transizioni, step, checklist, automazioni, responsabilita' e blocchi operativi.

### Notification Engine
Gestisce notifiche interne, reminder, alert di scadenza e futuri canali esterni come email, calendario o messaggistica.

### AI Engine
Gestisce assistenti, classificazione intelligente, suggerimenti, report generator e decision support con supervisione umana.

### Analytics Engine
Gestisce KPI, dashboard, statistiche, performance, forecast, export e viste direzionali.

### API Gateway
Fornisce il punto logico di governo per API interne, API esterne, autenticazione delle integrazioni, versionamento, logging e gestione errori.

## Relazione con i moduli verticali
I moduli Mediazione, Formazione, OCC, Orientamento e Crisi devono usare il Kernel per le capacita' comuni. Le personalizzazioni verticali devono estendere il Kernel senza duplicarne responsabilita'.

## Governance tecnica
Ogni modifica a un componente Kernel deve dichiarare:
- codice modulo `KERNEL`;
- moduli verticali impattati;
- cambiamenti API;
- cambiamenti database, se presenti;
- test di regressione richiesti;
- impatti su manuali e configurazioni.
