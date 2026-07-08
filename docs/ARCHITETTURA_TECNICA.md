# Mediacon Hub ERP
## Architettura Tecnica v1.0

---

# Obiettivo

Realizzare una piattaforma web moderna, modulare, sicura e scalabile, utilizzabile da una o più organizzazioni, accessibile da PC, tablet e smartphone.

---

# Stack tecnologico

## Frontend

Framework: React

Funzioni:

- Dashboard
- Gestione pratiche
- Ricerca
- Calendario
- Report
- Moduli AI

---

## Backend

Framework: FastAPI (Python)

Responsabilità:

- API REST
- Business Logic
- Gestione utenti
- Calcoli
- Workflow
- Sicurezza
- Generazione PDF
- Integrazione AI

---

## Database

PostgreSQL

Il database sarà relazionale e progettato per garantire:

- integrità dei dati;
- elevate prestazioni;
- scalabilità;
- supporto multi-organizzazione.

---

# Architettura a moduli

Il sistema è composto da moduli indipendenti.

## Core

Gestisce:

- utenti;
- ruoli;
- permessi;
- sedi;
- contatti;
- documenti;
- notifiche;
- log;
- configurazioni.

---

## CRM

Gestisce:

- persone;
- aziende;
- avvocati;
- mediatori;
- docenti;
- studenti;
- clienti;
- debitori;
- creditori.

---

## Mediazione

Gestisce:

- pratiche;
- incontri;
- verbali;
- accordi;
- convocazioni;
- indennità;
- compensi;
- DGStat.

---

## Formazione

Gestisce:

- corsi;
- lezioni;
- iscritti;
- docenti;
- presenze;
- attestati.

---

## Orientamento

Gestisce:

- lead;
- università;
- corsi;
- offerte;
- preventivi;
- follow-up;
- immatricolazioni.

---

## OCC

Gestisce:

- procedure;
- debitori;
- creditori;
- documentazione;
- relazioni;
- scadenze.

---

## Crisi d'Impresa

Gestisce:

- imprese;
- advisor;
- business plan;
- banche;
- fornitori;
- pratiche.

---

## Documentale

Gestisce tutti gli allegati.

Supporta:

- PDF;
- Word;
- Excel;
- immagini;
- PEC;
- firme.

---

## Dashboard

Visualizza KPI, attività, notifiche e scadenze.

---

# Workflow Engine

Ogni pratica segue un flusso configurabile.

Esempio Mediazione:

Deposito

↓

Verifica Segreteria

↓

Nomina Mediatore

↓

Convocazione

↓

Primo incontro

↓

Ulteriori incontri

↓

Chiusura

↓

DGStat

↓

Archivio

Lo stesso principio sarà utilizzato per OCC, Formazione e Orientamento.

---

# Motore Documentale

Ogni documento sarà generato partendo da un modello.

Esempi:

- convocazioni;
- verbali;
- accordi;
- attestati;
- preventivi;
- relazioni OCC.

---

# Sistema notifiche

Supporto a:

- notifiche interne;
- email;
- PEC;
- promemoria;
- attività assegnate.

---

# Sistema AI

Il sistema integra assistenti AI specializzati.

Gli assistenti possono:

- preparare bozze;
- analizzare documenti;
- verificare completezza pratiche;
- generare riepiloghi;
- suggerire attività.

---

# Sicurezza

Il sistema deve garantire:

- autenticazione;
- autorizzazioni;
- tracciamento modifiche;
- backup;
- cifratura password;
- protezione documenti.

---

# Scalabilità

Il sistema deve poter supportare:

- nuove sedi;
- nuovi moduli;
- nuovi utenti;
- nuovi organismi.

---

# Tecnologie previste

Frontend:
React

Backend:
FastAPI

Database:
PostgreSQL

Storage:
S3 compatibile

Autenticazione:
JWT

PDF:
ReportLab

AI:
OpenAI API

Container:
Docker

Deploy:
Linux + Nginx

---

Fine documento.
