# Nexus API Strategy

## Scopo
La strategia API di Nexus ERP definisce come la piattaforma si prepara a integrazioni future, mantenendo separazione tra prodotto, installazioni pilota e fornitori esterni.

Le integrazioni devono essere progettate come adattatori configurabili, governati da API Gateway, permessi, audit, gestione errori e documentazione contrattuale.

## Principi
- Nessuna integrazione esterna deve essere accoppiata direttamente alla logica di dominio.
- Ogni connettore deve avere configurazione per organismo e, se necessario, per sede.
- Le credenziali devono essere gestite in modo sicuro e non salvate in documentazione o codice sorgente.
- Ogni chiamata critica deve essere tracciata.
- Le integrazioni devono degradare in modo controllato quando il servizio esterno non e' disponibile.
- Dove un'integrazione dipende da vincoli normativi o disponibilita' tecnica, il sistema deve prevedere una modalita' manuale o semi-automatica.

## Integrazioni previste

### PEC
Predisposizione per invio, ricezione, protocollazione e collegamento PEC a fascicoli, procedimenti e documenti.

### Firma digitale
Predisposizione per richiesta, stato e archiviazione di firme digitali su verbali, accordi, attestati, relazioni e documenti ufficiali.

### SPID/CIE
Predisposizione per autenticazione federata o identificazione forte degli utenti, ove coerente con contesto normativo e fornitore scelto.

### pagoPA
Predisposizione per pagamenti, stati pagamento, ricevute e riconciliazione economica tramite canali pagoPA.

### Calendario
Predisposizione per sincronizzazione eventi, disponibilita', incontri, lezioni, scadenze e reminder con calendari esterni.

### Email
Predisposizione per invio email transazionali, notifiche operative, template di comunicazione e archiviazione conversazioni rilevanti.

### Videoconferenza
Predisposizione per creazione link riunione, calendario, inviti, tracciamento incontri e collegamento al fascicolo.

### Fatturazione
Predisposizione per emissione, ricezione, stati, riconciliazione e collegamento fatture a pagamenti, compensi e pratiche.

### Ministero/DGStat
Predisposizione per esportazioni, statistiche, report e invii verso sistemi ministeriali o DGStat ove tecnicamente possibile e autorizzato dalle specifiche disponibili.

## Livelli architetturali
- API interne di dominio.
- API Gateway per sicurezza, logging e versionamento.
- Adapter per fornitori esterni.
- Job asincroni per operazioni lente o differite.
- Registro integrazioni per stato, errori e retry.

## Requisiti documentali
Ogni integrazione attivata deve avere:
- scheda funzionale;
- scheda tecnica;
- configurazioni richieste;
- permessi;
- errori noti;
- test book;
- istruzioni operative per amministratori.
