# Software Architecture

## Scopo
Descrivere l'architettura software di Mediacon Nexus ERP, i componenti principali e le responsabilita' applicative.

## Vista generale
Il sistema e' organizzato come piattaforma ERP modulare con:
- backend API per logica applicativa, persistenza e regole di dominio;
- frontend web per operativita' utente e amministrazione;
- database relazionale per dati transazionali e configurazioni;
- motori trasversali per documenti, workflow, economia, template, parser, AI e analytics.

## Principi architetturali
- Modularita' per dominio funzionale.
- Separazione tra logica core, verticali e motori trasversali.
- Tracciabilita' tramite audit log e codici modulo.
- Estendibilita' controllata tramite registry, configurazioni e workflow.
- Compatibilita' progressiva tra release.

## Componenti logici
- Core Platform: configurazioni, autenticazione, ruoli, permessi, audit, organismi e sedi.
- Case Engine: fascicolo universale, timeline, checklist, attivita', scadenze e documenti.
- Moduli verticali: Mediazione, Formazione, OCC, Orientamento e Crisi.
- Engines: Document, Parser, Template, Economic, Workflow, AI e Analytics.

## Decisioni da mantenere aggiornate
Ogni modifica architetturale rilevante deve indicare motivazione, alternative considerate, impatto sui moduli e impatto sui test.
