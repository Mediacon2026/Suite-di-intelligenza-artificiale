from __future__ import annotations

from typing import Any

from . import audit_engine, case_engine, document_engine, event_engine, notification_engine, rules_engine, workflow_engine


def get_kernel_status() -> dict[str, Any]:
    return {
        "kernel": "online",
        "engines": {
            "case_engine": "ready",
            "rules_engine": "ready",
            "workflow_engine": "ready",
            "event_engine": "ready",
            "document_engine": "ready",
            "notification_engine": "ready",
            "audit_engine": "ready",
        },
    }


def create_case_from_intake(intake: dict[str, Any]) -> dict[str, Any]:
    case = case_engine.create_case(intake)
    event = event_engine.publish_event("case.created", {"case": case, "intake": intake})
    audit = audit_engine.log_ai_action("create_case_from_intake", {"intake": intake, "case": case})
    return {"case": case, "event": event, "audit": audit}


def create_mediation_from_case(case_id: int, data: dict[str, Any] | None = None) -> dict[str, Any]:
    event = event_engine.publish_event("mediation.created", {"case_id": case_id, "data": data or {}})
    return {"case_id": case_id, "status": "prepared_placeholder", "event": event}


def assign_mediator(case_id: int, mediator: dict[str, Any]) -> dict[str, Any]:
    event = event_engine.publish_event("mediator.assigned", {"case_id": case_id, "mediator": mediator})
    notice = notification_engine.send_mediator_assignment_notice(mediator, {"case_id": case_id})
    audit = audit_engine.log_action("assign_mediator", {"case_id": case_id, "mediator": mediator})
    return {"case_id": case_id, "mediator": mediator, "event": event, "notice": notice, "audit": audit}


def schedule_meeting(case_id: int, meeting: dict[str, Any]) -> dict[str, Any]:
    event = event_engine.publish_event("meeting.scheduled", {"case_id": case_id, "meeting": meeting})
    return {"case_id": case_id, "meeting": meeting, "event": event}


def generate_startup_documents(case_id: int, templates: list[str] | None = None) -> dict[str, Any]:
    documents = [document_engine.generate_document(template, {"case_id": case_id}) for template in (templates or [])]
    event = event_engine.publish_event("document.generated", {"case_id": case_id, "documents": documents})
    return {"case_id": case_id, "documents": documents, "event": event}


def calculate_case_status(case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
    workflow = workflow_engine.get_current_step(case_id, (context or {}).get("workflow_key", "default"))
    rules = rules_engine.evaluate_rules_for_case(case_id, context=context)
    summary = case_engine.get_case_summary(case_id, context)
    return {
        "case_id": case_id,
        "status": "calculated_placeholder",
        "workflow": workflow,
        "rules": rules,
        "summary": summary,
    }
