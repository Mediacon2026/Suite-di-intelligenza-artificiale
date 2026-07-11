# Video Conference Provider Layer

## Scopo
Il Video Conference Provider Layer isola Nexus ERP dai singoli fornitori di videoconferenza. Il modulo Mediazione usa questo layer per creare, aggiornare, cancellare e sincronizzare incontri telematici o misti senza accoppiarsi direttamente alle API del provider.

## Provider supportati

| Provider | Stato | Note |
| --- | --- | --- |
| WEBEX | Iniziale | Provider iniziale per MED-016. API reali non ancora chiamate. |
| TEAMS | Futuro | Predisposizione architetturale. |
| ZOOM | Futuro | Predisposizione architetturale. |
| GOOGLE_MEET | Futuro | Predisposizione architetturale. |

## Contratto provider
Ogni provider deve implementare:
- `create_meeting()`;
- `update_meeting()`;
- `cancel_meeting()`;
- `get_join_url()`;
- `sync_participants()`.

## Layer backend
La predisposizione iniziale vive in:

```text
backend/app/integrations/video/
+-- base_provider.py
+-- webex_provider.py
+-- meeting_service.py
```

## Webex provider iniziale
Webex e' il provider iniziale. In questa fase:
- non vengono chiamate API Webex reali;
- non vengono gestite credenziali Webex;
- non vengono creati meeting remoti effettivi;
- il provider restituisce metadati placeholder e stati `pending_provider_activation`.

## Responsabilita' del provider layer
- Normalizzare input e output dei provider.
- Esporre un contratto unico al modulo Mediazione.
- Registrare identificativi esterni come `webex_meeting_id`.
- Separare join URL, stato meeting e sincronizzazione partecipanti.
- Preparare retry, logging e gestione errori.

## Responsabilita' escluse
- Validazione normativa della mediazione.
- Firma digitale.
- Conservazione CAD.
- Invio email o PEC.
- Gestione documentale.

Queste responsabilita' sono coperte da Document Engine, Signature Engine, Preservation Engine e Notification Engine.

## Dati minimi meeting
- provider;
- provider meeting id;
- join URL;
- data e ora;
- modalita': presenza, telematica, mista;
- stato;
- partecipanti;
- log sincronizzazione.

## Evoluzione prevista
Quando sara' attivata l'integrazione reale Webex, il layer dovra' aggiungere:
- configurazioni sicure per credenziali;
- gestione token;
- chiamate API;
- mapping errori provider;
- sync presenze;
- test di integrazione;
- audit delle operazioni.
