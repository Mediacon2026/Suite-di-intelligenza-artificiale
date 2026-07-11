# Alpha Smoke Test

## Versione
Nexus ERP Alpha 0.1.0 (`0.1.0-alpha`)

## Obiettivo
Verificare che le aree principali dell'interfaccia si aprano senza regressioni evidenti dopo la stabilizzazione Kernel e l'aggiunta dei test Alpha.

## Checklist frontend

| Area | Percorso UI | Esito atteso | Stato |
| --- | --- | --- | --- |
| Dashboard | Menu Dashboard | La pagina mostra stato sistema e widget nuove acquisizioni | Da eseguire in browser |
| CRM | Menu CRM | La pagina CRM carica elenco e dettaglio contatti | Da eseguire in browser |
| Mediazioni | Menu Mediazioni | La pagina mediazioni carica elenco, dettagli e azioni principali | Da eseguire in browser |
| Fascicoli | Menu Fascicoli | La pagina fascicoli carica elenco, documenti, checklist e timeline | Da eseguire in browser |
| Impostazioni | Menu Impostazioni | Il Configuration Center apre sezioni configurazione | Da eseguire in browser |
| Intake Review | Menu Intake Review | La pagina consente caricamento documenti e review a quattro colonne | Da eseguire in browser |

## Verifica automatica correlata
La build Vite e' parte dello smoke test tecnico. Se `npm run build` termina correttamente, il frontend compila e le rotte/componenti principali risultano importabili.

## Note operative
Questa checklist non sostituisce il collaudo manuale in browser con backend attivo e dati reali. Serve a guidare il controllo Alpha prima della demo o del rilascio interno.
