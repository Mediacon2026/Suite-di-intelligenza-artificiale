from __future__ import annotations

from datetime import datetime, timezone
from typing import Any, Callable


INITIAL_EVENT_TYPES = {
    "case.created",
    "document.uploaded",
    "mediation.created",
    "mediator.assigned",
    "mediator.accepted",
    "meeting.created",
    "meeting.completed",
    "meeting.scheduled",
    "document.generated",
    "document.signed",
    "signature.requested",
    "signature.completed",
    "cad.preserved",
    "pec.sent",
    "pec.delivered",
    "pec.failed",
    "payment.received",
    "workflow.changed",
}

_EVENTS: list[dict[str, Any]] = []
_HANDLERS: dict[str, list[Callable[[dict[str, Any]], dict[str, Any] | None]]] = {}


def publish_event(event_type: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    event = {
        "event_type": event_type,
        "known_event": event_type in INITIAL_EVENT_TYPES,
        "payload": payload or {},
        "published_at": datetime.now(timezone.utc).isoformat(),
    }
    _EVENTS.append(event)
    return event


def handle_event(event: dict[str, Any]) -> dict[str, Any]:
    event_type = event.get("event_type", "")
    results = []
    for handler in _HANDLERS.get(event_type, []):
        result = handler(event)
        if result is not None:
            results.append(result)
    return {
        "handled": True,
        "event_type": event_type,
        "known_event": event_type in INITIAL_EVENT_TYPES,
        "handler_results": results,
        "message": "evento gestito correttamente",
    }


def register_event_handler(event_type: str, handler: Callable[[dict[str, Any]], dict[str, Any] | None]) -> dict[str, Any]:
    _HANDLERS.setdefault(event_type, []).append(handler)
    return {"event_type": event_type, "registered": True, "handlers_count": len(_HANDLERS[event_type])}


def list_events_for_case(case_id: int) -> dict[str, Any]:
    events = [event for event in _EVENTS if event.get("payload", {}).get("case_id") == case_id]
    return {"case_id": case_id, "events": events, "count": len(events)}
