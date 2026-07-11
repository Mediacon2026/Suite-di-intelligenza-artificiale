# Nexus Functional Bible

## Visione generale
Nexus ERP e' una piattaforma gestionale multi-organismo, multi-sede e multi-modulo progettata per supportare servizi professionali complessi. L'installazione Mediacon rappresenta il primo ambiente pilota del prodotto: una configurazione concreta del sistema, non il limite funzionale della piattaforma.

La Functional Bible e' il documento madre dei requisiti funzionali. Deve guidare roadmap, sviluppo, test, documentazione utente e decisioni di prodotto.

## Principi fondamentali
- Il prodotto deve essere configurabile prima di essere customizzato.
- Ogni funzione deve essere collegata a un codice modulo stabile.
- Ogni processo operativo deve lasciare traccia nel fascicolo, nell'audit o nella timeline.
- I motori trasversali devono servire piu' moduli senza duplicare logica.
- Le release devono essere incrementali, verificabili e documentate.
- Le funzioni AI devono assistere l'utente, non sostituire controlli e responsabilita'.

## Multi-organismo
Nexus ERP deve permettere la gestione di piu' organismi o entita' operative all'interno della stessa piattaforma. Ogni organismo puo' avere configurazioni, sedi, utenti, ruoli, workflow, template, parametri economici e dati operativi propri.

Requisiti chiave:
- isolamento logico dei dati;
- configurazioni per organismo;
- utenti associabili a uno o piu' organismi;
- report e dashboard filtrabili per organismo;
- audit coerente con il contesto operativo.

## Multi-sede
Il sistema deve supportare sedi fisiche, operative o amministrative. Ogni sede puo' avere calendario, utenti, procedimenti, aule, risorse e statistiche.

Requisiti chiave:
- anagrafica sedi;
- assegnazione utenti e pratiche;
- permessi e visibilita' per sede;
- metriche di performance per sede;
- integrazione con calendario e disponibilita'.

## Fascicolo universale
Il fascicolo universale e' l'unita' operativa centrale. Ogni modulo verticale deve poter creare, consultare o collegare un fascicolo, mantenendo documenti, contatti, scadenze, attivita', timeline, checklist e stato.

Requisiti chiave:
- codice fascicolo univoco;
- relazioni con contatti e soggetti coinvolti;
- documenti versionati;
- timeline eventi;
- task e scadenze;
- archivio e conservazione logica;
- collegamento a procedimenti verticali.

## Configuration Center
Il Configuration Center deve consentire la gestione amministrativa della piattaforma senza interventi diretti sul codice.

Ambiti configurabili:
- moduli attivi;
- organismi e sedi;
- ruoli e permessi;
- materie e tipologie pratica;
- numerazioni;
- workflow;
- parametri economici;
- template e regole operative.

## Economic Engine
L'Economic Engine governa parametri economici, tariffari, compensi, riparti, pagamenti e calcoli verticali.

Requisiti chiave:
- regole configurabili per organismo e modulo;
- calcoli tracciabili e verificabili;
- gestione eccezioni;
- supporto a mediazione, formazione, OCC, orientamento e crisi;
- output utilizzabile da documenti, dashboard e report.

## Document Engine
Il Document Engine gestisce documenti caricati, generati, classificati, collegati e archiviati.

Requisiti chiave:
- upload singolo e massivo;
- associazione a fascicoli e procedimenti;
- versioning;
- metadati;
- permessi;
- integrazione con Template Engine, Parser Engine e AI Engine.

## Parser Engine
Il Parser Engine interpreta documenti e pacchetti informativi, estraendo dati strutturati dove tecnicamente possibile.

Requisiti chiave:
- registry dei parser;
- parser specializzati per modulo;
- OCR e classificazione;
- confidence score;
- revisione umana dei dati estratti;
- audit dell'origine dei dati.

## Workflow Engine
Il Workflow Engine governa stati, transizioni, checklist, attivita', automazioni e blocchi operativi.

Requisiti chiave:
- workflow configurabili;
- step e responsabilita';
- condizioni di avanzamento;
- scadenze automatiche;
- notifiche;
- integrazione con fascicolo e moduli verticali.

## AI Engine
L'AI Engine offre assistenza intelligente su documenti, fascicoli, report, analisi e decision support.

Requisiti chiave:
- assistente contestuale;
- classificazione documentale;
- suggerimenti operativi;
- generazione bozze;
- reportistica assistita;
- limiti, tracciabilita' e supervisione umana.

## Mediazione dalla A alla Z
Il modulo Mediazione deve coprire l'intero ciclo operativo del procedimento.

Ambiti funzionali:
- apertura procedimento;
- parti, avvocati e mediatori;
- gestione incontri e calendario;
- documenti, verbali e accordi;
- pagamenti, compensi e riparti;
- PEC, firma digitale e archiviazione, ove integrate;
- statistiche e DGStat ove tecnicamente possibile.

## Formazione dalla A alla Z
Il modulo Formazione deve gestire corsi, docenti, iscritti, presenze, attestati e pagamenti.

Ambiti funzionali:
- catalogo corsi;
- calendario lezioni;
- docenti e aule;
- iscrizioni e presenze;
- quiz e valutazioni;
- attestati;
- pagamenti e statistiche.

## OCC dalla A alla Z
Il modulo OCC deve gestire procedure di composizione della crisi, soggetti, documenti economici, scadenze e relazioni.

Ambiti funzionali:
- apertura procedura;
- debitore, creditori, patrimonio e redditi;
- documenti e checklist;
- relazione;
- compensi;
- tribunale;
- scadenze e statistiche.

## Orientamento dalla A alla Z
Il modulo Orientamento deve gestire lead, studenti, universita', offerte, immatricolazioni, provvigioni e follow up.

Ambiti funzionali:
- acquisizione lead;
- profilazione studente;
- gestione universita' e offerte;
- documenti;
- immatricolazioni;
- provvigioni;
- follow up e statistiche.

## Crisi d'impresa dalla A alla Z
Il modulo Crisi d'Impresa deve supportare aziende, creditori, advisor, business plan, monitoraggio, scadenze e report.

Ambiti funzionali:
- apertura procedura;
- anagrafica azienda;
- creditori;
- business plan;
- advisor;
- monitoraggio;
- tribunale;
- report e statistiche.

## Command Center
Il Command Center e' la vista direzionale e operativa della piattaforma. Deve offrire controllo su stato del sistema, andamento moduli, KPI, scadenze, carichi di lavoro e anomalie.

Requisiti chiave:
- dashboard multi-organismo e multi-sede;
- KPI operativi e direzionali;
- viste per modulo;
- alert e notifiche;
- export;
- integrazione con Analytics Engine.

## Regole di sviluppo
- Ogni sviluppo deve dichiarare i codici modulo impattati.
- Ogni nuova funzione deve aggiornare requisiti, API, test e manuali quando necessario.
- Ogni modifica ai workflow deve essere documentata.
- Ogni nuova entita' persistente deve aggiornare il dizionario dati.
- Le integrazioni esterne devono passare dalla strategia API e rispettare autorizzazioni, audit e gestione errori.
- Le funzioni pilota Mediacon devono restare compatibili con il prodotto Nexus ERP multi-organismo.
