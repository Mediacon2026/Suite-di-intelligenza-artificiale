# Nexus Kernel 1.0 - Core Engines Architecture

## Visione del Kernel
Nexus Kernel 1.0 e' il cuore operativo di Nexus ERP. Il suo compito e' governare le capacita' comuni della piattaforma, mettendo a disposizione dei moduli verticali un insieme coerente di motori riutilizzabili.

I moduli Mediazione, Formazione, OCC, Orientamento, Crisi d'Impresa, Advisor, Documentale e AI non devono duplicare logiche trasversali. Devono invece parlare con il Kernel per fascicoli, regole, workflow, eventi, documenti, notifiche e audit.

## Principi fondamentali
- Tutti i moduli parlano con il Kernel.
- Nessun modulo duplica logiche comuni.
- Il fascicolo universale e' il contenitore operativo principale.
- Regole, workflow, documenti e notifiche sono governati da motori centrali.
- Ogni azione rilevante deve produrre audit e, quando necessario, evento di piattaforma.
- Il Kernel espone funzioni interne stabili prima ancora di esporre API pubbliche definitive.

## Case Engine
Il Case Engine governa il fascicolo universale. Fornisce funzioni per creare fascicoli, aggiornare stati, collegare documenti, collegare contatti, creare eventi timeline e restituire riepiloghi operativi.

Responsabilita':
- creare un fascicolo per ogni pratica gestita da Nexus ERP;
- mantenere relazioni tra fascicolo, documenti, contatti, timeline e moduli verticali;
- offrire un punto unico per interrogare lo stato della pratica;
- evitare che Mediazione, OCC, Formazione o altri moduli implementino fascicoli paralleli.

## Rules Engine
Il Rules Engine valuta regole applicative, economiche, documentali e di workflow. In questa fase 1.0 contiene logica placeholder sicura, ma stabilisce il contratto futuro per regole configurabili.

Responsabilita':
- recuperare regole attive;
- valutare una regola singola;
- valutare piu' regole sul fascicolo;
- applicare regole economiche;
- applicare regole documentali;
- applicare regole di workflow.

## Workflow Engine
Il Workflow Engine governa stati, step e azioni successive. Ogni modulo puo' avere workflow specifici, ma la logica di avanzamento deve restare comune.

Responsabilita':
- avviare workflow;
- avanzare workflow;
- restituire lo step corrente;
- suggerire le azioni successive;
- completare step;
- restituire sempre dati JSON coerenti e tracciabili.

## Event Engine
L'Event Engine introduce un modello a eventi per collegare azioni operative e automazioni future.

Eventi iniziali:
- `case.created`
- `document.uploaded`
- `mediation.created`
- `mediator.assigned`
- `meeting.scheduled`
- `document.generated`
- `signature.requested`
- `signature.completed`
- `cad.preserved`

Responsabilita':
- pubblicare eventi;
- gestire eventi;
- registrare handler;
- elencare eventi collegati a un fascicolo;
- preparare automazioni future senza accoppiare i moduli verticali.

## Document Engine
Il Document Engine governa generazione, template, versioni, hash, firma e conservazione documentale.

Responsabilita':
- generare documenti da dati strutturati;
- renderizzare template;
- creare versioni documentali;
- calcolare hash;
- marcare documenti per firma;
- marcare documenti per conservazione CAD.

## Notification Engine
Il Notification Engine governa notifiche interne e canali esterni futuri. In questa fase non invia email o PEC reali.

Responsabilita':
- creare notifiche;
- predisporre placeholder email;
- predisporre placeholder PEC;
- predisporre avvisi nomina mediatore;
- predisporre avvisi incontro.

## Audit Engine
L'Audit Engine centralizza la tracciatura delle azioni operative, AI, documentali, firma e valutazione regole.

Responsabilita':
- registrare azioni utente;
- registrare azioni AI;
- registrare azioni documentali;
- registrare azioni firma;
- registrare valutazioni regole;
- rendere ricostruibile la storia tecnica e operativa della piattaforma.

## Kernel Service
Il Kernel Service orchestra i motori interni e rappresenta il punto di accesso applicativo al Kernel.

Operazioni previste:
- creare fascicolo da Intake;
- creare mediazione da fascicolo;
- assegnare mediatore;
- fissare incontro;
- generare documenti iniziali;
- calcolare stato pratica.

In Nexus Kernel 1.0 queste funzioni usano placeholder sicuri o funzioni esistenti quando disponibili. L'obiettivo e' creare il contratto architetturale senza riscrivere il backend.

## Regola di integrazione
Ogni nuova funzione comune deve essere inserita nel Kernel o riusare un engine del Kernel. I moduli verticali devono contenere solo logiche specifiche del dominio e delegare al Kernel tutto cio' che e' trasversale.

## Evoluzione
Le release successive potranno collegare il Kernel a:
- database schema dedicato per eventi e workflow;
- regole configurabili da UI;
- provider reali di firma, PEC e conservazione;
- automazioni asincrone;
- API pubbliche versionate;
- AI Core supervisionato dall'utente.
