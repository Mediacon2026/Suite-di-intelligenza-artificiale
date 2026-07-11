# Kernel API Contracts

## Scopo
Questo documento definisce i contratti applicativi minimi del Nexus Kernel 1.0. Gli engine sono funzioni interne Python, non ancora API pubbliche complete. I contratti servono a stabilizzare input, output, errori, effetti collaterali ed eventi generati.

## Formato errori
Gli errori Kernel devono essere restituiti come JSON leggibile:

```json
{
  "error_code": "VALIDATION_ERROR",
  "message": "Descrizione errore",
  "details": {}
}
```

## Case Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `create_case()` | `dict` dati fascicolo | `status`, `case` | `ValidationError` futuro | Nessuno nella versione placeholder | `case.created` tramite Kernel Service |
| `update_case_status()` | `case_id`, `status`, `reason` | stato aggiornato | `ValidationError` futuro | Nessuno | Nessuno |
| `link_document_to_case()` | `case_id`, `document_id` o documento | link documento | `DocumentError` futuro | Nessuno | `document.uploaded` futuro |
| `link_contact_to_case()` | `case_id`, `contact_id`, `role` | link contatto | `ValidationError` futuro | Nessuno | Nessuno |
| `create_case_timeline_event()` | `case_id`, `event_type`, `title`, `description` | evento timeline | `ValidationError` futuro | Nessuno | Nessuno |
| `get_case_summary()` | `case_id`, dati opzionali | riepilogo fascicolo | `ValidationError` futuro | Nessuno | Nessuno |

## Rules Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `evaluate_rule()` | regola, contesto | esito valutazione | `RuleEvaluationError` futuro | Nessuno | Nessuno |
| `evaluate_rules_for_case()` | `case_id`, regole, contesto | elenco valutazioni | `RuleEvaluationError` futuro | Nessuno | Nessuno |
| `get_active_rules()` | modulo, organismo | elenco regole attive | Nessuno nella versione placeholder | Nessuno | Nessuno |
| `apply_economic_rules()` | `case_id`, contesto | esito placeholder | `RuleEvaluationError` futuro | Nessuno | Nessuno |
| `apply_document_rules()` | `case_id`, contesto | esito placeholder | `RuleEvaluationError` futuro | Nessuno | Nessuno |
| `apply_workflow_rules()` | `case_id`, contesto | esito placeholder | `RuleEvaluationError` futuro | Nessuno | Nessuno |

## Workflow Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `start_workflow()` | `case_id`, `workflow_key`, contesto | workflow avviato | `WorkflowError` futuro | Nessuno | Nessuno |
| `advance_workflow()` | `case_id`, `workflow_key`, step corrente, step successivo | avanzamento workflow | `WorkflowError` futuro | Nessuno | Nessuno |
| `get_current_step()` | `case_id`, `workflow_key` | step corrente | `WorkflowError` futuro | Nessuno | Nessuno |
| `get_next_actions()` | `case_id`, `workflow_key` | azioni successive | `WorkflowError` futuro | Nessuno | Nessuno |
| `complete_step()` | `case_id`, `workflow_key`, `step_key` | step completato | `WorkflowError` futuro | Nessuno | Nessuno |

## Event Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `publish_event()` | `event_type`, payload | evento pubblicato | `ValidationError` futuro | Registra evento in memoria nella versione Alpha | Evento richiesto |
| `handle_event()` | evento | esito handler | `KernelError` futuro | Esegue handler registrati | Nessuno |
| `register_event_handler()` | `event_type`, callable | conferma registrazione | `ValidationError` futuro | Registra handler in memoria | Nessuno |
| `list_events_for_case()` | `case_id` | eventi del fascicolo | Nessuno | Nessuno | Nessuno |

Eventi iniziali: `case.created`, `document.uploaded`, `mediation.created`, `mediator.assigned`, `meeting.scheduled`, `document.generated`, `signature.requested`, `signature.completed`, `cad.preserved`.

## Document Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `generate_document()` | `template_key`, dati | documento placeholder | `DocumentError` futuro | Nessuno | `document.generated` tramite Kernel Service |
| `render_template()` | contenuto template, dati | contenuto renderizzato | `DocumentError` futuro | Nessuno | Nessuno |
| `create_document_version()` | `document_id`, dati versione | versione creata | `DocumentError` futuro | Nessuno | Nessuno |
| `calculate_document_hash()` | contenuto, algoritmo | hash documento | `DocumentError` se algoritmo non valido | Nessuno | Nessuno |
| `mark_document_for_signature()` | `document_id`, destinatari | stato firma | `DocumentError` futuro | Nessuno | `signature.requested` futuro |
| `mark_document_for_preservation()` | `document_id`, provider | stato conservazione | `DocumentError` futuro | Nessuno | `cad.preserved` futuro |

## Notification Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `create_notification()` | destinatario, oggetto, corpo, canale | notifica creata | `NotificationError` futuro | Nessun invio reale | Nessuno |
| `send_email_placeholder()` | email, oggetto, corpo | email non inviata placeholder | `NotificationError` futuro | Nessun invio reale | Nessuno |
| `send_pec_placeholder()` | PEC, oggetto, corpo | PEC non inviata placeholder | `NotificationError` futuro | Nessun invio reale | Nessuno |
| `send_mediator_assignment_notice()` | mediatore, mediazione | avviso predisposto | `NotificationError` futuro | Nessun invio reale | Nessuno |
| `send_meeting_notice()` | destinatari, incontro | avviso predisposto | `NotificationError` futuro | Nessun invio reale | Nessuno |

## Audit Engine

| Metodo | Input | Output | Errori possibili | Effetti collaterali | Eventi generati |
| --- | --- | --- | --- | --- | --- |
| `log_action()` | azione, payload | audit placeholder | `AuditError` futuro | Nessuno nella versione placeholder | Nessuno |
| `log_ai_action()` | azione, payload | audit AI placeholder | `AuditError` futuro | Nessuno | Nessuno |
| `log_document_action()` | azione, payload | audit documento placeholder | `AuditError` futuro | Nessuno | Nessuno |
| `log_signature_action()` | azione, payload | audit firma placeholder | `AuditError` futuro | Nessuno | Nessuno |
| `log_rule_evaluation()` | azione, payload | audit regola placeholder | `AuditError` futuro | Nessuno | Nessuno |

## API di test Kernel

### `GET /kernel/status`
Restituisce stato Kernel ed engine.

### `POST /kernel/events/test`
Input:

```json
{
  "event_type": "case.created",
  "case_id": 1
}
```

Output: evento pubblicato e gestito con messaggio di conferma.
