# Release Notes

Questo documento raccoglie le note di rilascio destinate a stakeholder tecnici, amministratori e responsabili operativi.

## Template release

```text
Versione:
Data:
Stato:
Ambiente:
Moduli coinvolti:
```

## Contenuti richiesti per ogni release
- Sintesi del valore rilasciato.
- Elenco funzionalita' nuove o modificate.
- Migrazioni database richieste.
- Impatti su permessi, configurazioni e workflow.
- Rischi noti e limitazioni.
- Test eseguiti e risultato.

## Release iniziale documentale

### Sintesi
Avviata la governance documentale del progetto Mediacon Nexus ERP con struttura `docs/` organizzata per progetto, architettura, requisiti, moduli, database, API, test e manuali.

### Moduli coinvolti
- Tutti i moduli censiti in `docs/MODULE_INDEX.md`.

### Note operative
La documentazione e' una baseline iniziale e dovra' essere raffinata a ogni release applicativa.

## Nexus ERP Alpha 0.1.0

```text
Versione: 0.1.0-alpha
Data: 2026-07-10
Stato: Alpha
Ambiente: locale / pilota Mediacon
Moduli coinvolti: CORE, CRM, MED, CASE, PARSER, AI, KERNEL
```

### Sintesi
Prima baseline integrata di Nexus ERP come piattaforma Alpha. La release valida i componenti essenziali per operare su Configuration Center, CRM base, Mediazioni base, Fascicoli, Parser Mediazione, Intake Engine e Nexus Kernel 1.0.

### Funzionalita' incluse
- Configuration Center.
- CRM base.
- Mediazioni base.
- Fascicoli.
- Parser Mediazione.
- Intake Engine.
- Nexus Kernel 1.0.
- Mediazione Zero Click Alpha.
- Next Action Engine.
- Nexus Automation Engine v1.

### Migrazioni database
Nessuna migrazione manuale obbligatoria per questa release. Le strutture gia' predisposte usano inizializzazione applicativa sicura dove presente. ALPHA-002 riusa tabelle esistenti per mediazioni, fascicoli, sessioni, documenti generati, timeline, audit e nomine mediatore.

### Rischi noti
- Alcuni engine Kernel sono placeholder operativi e non sostituiscono ancora servizi persistenti completi.
- I provider email, PEC, firma e conservazione sono predisposti ma non collegati a servizi reali.
- Il Next Action Engine Alpha calcola la prossima azione da stato mediazione, nomina, incontri, documenti e timeline; non e' ancora persistito in una tabella dedicata.
- L'Automation Engine Alpha conserva i log in memoria e registra gli effetti principali su timeline/audit quando invocato via API.

### Test richiesti
- Python `py_compile`.
- Test unitari Kernel.
- Test API.
- Test integrazione Alpha.
- Test ALPHA-002 su sedi Pachino/Napoli, sede ambigua e regole Next Action.
- Test ALPHA-003 su nomina, firma, convocazione, verbale, audit, timeline e next action.
- Frontend Vite build.

## Nexus ERP 0.1.1 Alpha Recovery

```text
Versione: 0.1.1-alpha-recovery
Data: 2026-07-11
Stato: Alpha Recovery
Ambiente: locale / pilota Mediacon
Moduli coinvolti: RECOVERY-001, STABILITY-001, PARSER-002, ALPHA-002, DATABASE, GIT
```

### Recuperi verificati
- Git locale nuovamente valido sul branch `main`, senza push.
- PostgreSQL allineato tramite migrazioni additive tracciate.
- Parser esteso al campo ragioni con confidence.
- Next Action completato nel calcolo fino ad archiviazione.
- pytest installato nel profilo sviluppo e suite ripristinata.
- Test reali delle regole economiche Casarano/Pachino/Napoli e rollback PostgreSQL.

### Limitazioni dichiarate
- Webex, email/PEC, firma e conservazione non sono integrazioni reali.
- Diversi engine Kernel restano contratti placeholder o in-memory.
- Automation Engine produce ancora alcuni effetti preparatori/placeholder.
- Non è stato eseguito alcun push o collaudo con provider esterni.
