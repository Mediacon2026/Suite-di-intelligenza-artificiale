# Non Functional Requirements

## Scopo
Definire requisiti non funzionali per qualita', sicurezza, affidabilita' e manutenibilita' del sistema.

## Sicurezza
- Autenticazione e autorizzazione basate su ruoli e permessi.
- Audit log per operazioni rilevanti.
- Protezione dei dati personali e dei documenti caricati.
- Separazione coerente dei dati tra organismi e sedi.

## Prestazioni
- Tempi di risposta adeguati per dashboard, liste operative e dettagli fascicolo.
- Import documentale e parsing progettati per carichi progressivi.
- Query e indici ottimizzati sulle entita' principali.

## Affidabilita'
- Migrazioni database controllate e documentate.
- Gestione errori chiara per utenti e amministratori.
- Test di regressione sui flussi principali.

## Manutenibilita'
- Codici modulo stabili.
- Documentazione aggiornata a ogni release.
- Separazione tra codice applicativo, configurazioni, documenti e test.
