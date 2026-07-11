from __future__ import annotations

from typing import Any


SUPPORTED_EVENTS = {
    "case.created",
    "mediator.assigned",
    "mediator.accepted",
    "meeting.created",
    "meeting.completed",
    "document.generated",
    "document.signed",
    "pec.sent",
    "pec.delivered",
    "pec.failed",
    "payment.received",
    "workflow.changed",
}

MEETING_OUTCOMES = {
    "mancata_adesione",
    "rinvio",
    "negativo",
    "accordo_primo_incontro",
    "accordo_successivo",
    "negativo_successivo",
}

AUTOMATION_RULES: list[dict[str, Any]] = [
    {
        "rule_key": "mediator_assignment_package",
        "event_type": "mediator.assigned",
        "description": "Genera pacchetto nomina mediatore e aggiorna timeline, audit e next action.",
        "actions": [
            "generate_assunzione_incarico",
            "generate_dichiarazione_imparzialita",
            "prepare_mediator_email",
            "update_timeline",
            "audit_ai",
            "audit_workflow",
            "audit_automation",
            "update_next_action",
        ],
    },
    {
        "rule_key": "mediator_acceptance_signed",
        "event_type": "document.signed",
        "document_type": "assunzione_incarico",
        "description": "Marca il mediatore come accettato e aggiorna la prossima azione.",
        "actions": [
            "update_mediator_status",
            "update_timeline",
            "audit_ai",
            "audit_workflow",
            "audit_automation",
            "update_next_action",
        ],
    },
    {
        "rule_key": "meeting_created_startup_documents",
        "event_type": "meeting.created",
        "description": "Prepara convocazione, atto adesione, privacy, regolamento e Webex se necessario.",
        "actions": [
            "generate_first_meeting_notice",
            "generate_modulo_adesione",
            "generate_privacy",
            "generate_regolamento",
            "prepare_webex_link",
            "update_timeline",
            "audit_ai",
            "audit_workflow",
            "audit_automation",
            "update_next_action",
        ],
    },
    {
        "rule_key": "meeting_completed_verbal_suggestion",
        "event_type": "meeting.completed",
        "description": "Suggerisce il verbale corretto in base all'esito.",
        "actions": [
            "suggest_verbal",
            "prepare_outcome_document",
            "update_timeline",
            "audit_ai",
            "audit_workflow",
            "audit_automation",
            "update_next_action",
        ],
    },
]


def rules_for_event(event_type: str, context: dict[str, Any] | None = None) -> list[dict[str, Any]]:
    context = context or {}
    rules = []
    for rule in AUTOMATION_RULES:
        if rule["event_type"] != event_type:
            continue
        document_type = rule.get("document_type")
        if document_type and context.get("document_type") != document_type:
            continue
        rules.append(rule)
    return rules
