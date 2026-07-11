# API Architecture

## Scopo
Stabilire convenzioni e responsabilita' delle API di Mediacon Nexus ERP.

## Principi
- Endpoint coerenti per risorse, azioni e moduli.
- Contratti chiari per input, output, errori e autorizzazioni.
- Versionamento compatibile con la strategia di release.
- Documentazione aggiornata in `docs/05_API/API_REFERENCE.md`.

## Convenzioni iniziali
- Le risorse principali espongono operazioni di lettura, creazione, modifica e cancellazione quando coerente con il dominio.
- Le azioni di calcolo, importazione o generazione devono essere esplicite e tracciabili.
- Gli endpoint amministrativi devono dichiarare permessi e impatti operativi.

## Aree API
- Core e configurazione.
- CRM e anagrafiche.
- Case Engine.
- Moduli verticali.
- Motori documentali, parser, template, workflow, economici, AI e analytics.

## Requisiti di qualita'
Ogni API pubblicata deve avere caso d'uso, parametri, risposta attesa, errori principali e copertura di test.
