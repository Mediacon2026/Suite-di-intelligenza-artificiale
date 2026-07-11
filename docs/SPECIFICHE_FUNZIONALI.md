# Mediacon Hub ERP
# Specifiche Funzionali v1.0

---

# Introduzione

Mediacon Hub ERP è una piattaforma gestionale web progettata per digitalizzare e integrare tutte le attività svolte da Mediacon.

L'obiettivo del sistema è fornire un ambiente unico nel quale gestire persone, pratiche, documenti, pagamenti, attività e statistiche.

Il software dovrà essere completamente modulare, multiutente, multi-sede e predisposto per l'integrazione con Intelligenza Artificiale.

---

# Architettura generale

La piattaforma sarà composta dai seguenti moduli:

1. Dashboard
2. CRM
3. Organismo di Mediazione
4. Ente di Formazione
5. Polo di Orientamento
6. OCC
7. Crisi d'Impresa
8. Advisor
9. Gestione Documentale
10. Contabilità
11. Report
12. AI Assistant
13. Configurazione

---

# Dashboard

La dashboard rappresenta il centro operativo dell'intero sistema.

L'utente deve visualizzare immediatamente:

• mediazioni aperte

• mediazioni chiuse

• mediazioni sospese

• mediazioni da convocare

• incontri della settimana

• pratiche OCC

• pratiche Crisi d'Impresa

• corsi attivi

• studenti in orientamento

• preventivi inviati

• immatricolazioni

• incassi

• pagamenti

• compensi da liquidare

• notifiche

• attività assegnate

---

# CRM

Il CRM costituisce il cuore del database.

Ogni soggetto dovrà essere registrato una sola volta.

Tipologie:

- Persona fisica
- Società
- Avvocato
- Mediatore
- Docente
- Studente
- Cliente
- Debitore
- Creditore
- Professionista
- Pubblica Amministrazione

Ogni contatto potrà appartenere contemporaneamente a più moduli.

Esempio:

Mario Rossi

✓ Avvocato

✓ Cliente OCC

✓ Docente

✓ Mediatore

utilizzando sempre la stessa anagrafica.

---

# Organismo di Mediazione

Il sistema dovrà gestire:

• apertura pratica

• assegnazione numero interno

• assegnazione numero DGStat

• convocazioni

• incontri

• verbali

• accordi

• documenti

• indennità

• pagamenti

• compensi

• statistiche

---

## Stati della pratica

Bozza

Depositata

Convocata

Primo incontro

In corso

Sospesa

Chiusa positiva

Chiusa negativa

Mancata adesione

Archiviata

---

# Regole fondamentali

Il numero interno è annuale.

Il numero DGStat è trimestrale.

Il trimestre viene determinato automaticamente dalla data di deposito.

Il sistema deve impedire duplicazioni.

Ogni modifica deve essere registrata nello storico.

---

# Multi sede

Il sistema deve poter gestire:

Casarano

Pachino

Napoli

ed un numero illimitato di future sedi.

Ogni sede possiede:

• utenti

• mediatori

• statistiche

• documenti

• pagamenti

• compensi

---

# Multiutente

Ogni utente deve possedere:

ruolo

permessi

firma digitale

firma grafica

preferenze

dashboard personalizzata

---

# Gestione documentale

Ogni pratica deve contenere:

istanza

adesione

verbali

convocazioni

documenti identità

procure

ricevute

relazioni

accordi

file vari

Tutti i documenti devono essere versionati.

---

# Motore PDF

Il sistema deve produrre automaticamente:

Verbali

Convocazioni

Attestati

Preventivi

Ricevute

Relazioni OCC

Accordi

Report

Statistiche

---

# Sistema notifiche

Notifiche interne

Email

PEC

Promemoria

Scadenze

---

# AI Assistant

Il sistema dovrà integrare assistenti AI specializzati.

Ogni assistente lavorerà esclusivamente sul proprio modulo.

---

Fine Specifiche Funzionali v1.0
