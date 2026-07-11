from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


def _timestamp() -> str:
    return datetime.now(timezone.utc).isoformat()


def create_case(data: dict[str, Any] | None = None) -> dict[str, Any]:
    payload = data or {}
    return {
        "status": "created",
        "case": {
            "id": payload.get("id"),
            "case_number": payload.get("case_number"),
            "case_type": payload.get("case_type", "universal"),
            "title": payload.get("title", "Nuovo fascicolo"),
            "created_at": _timestamp(),
        },
    }


def update_case_status(case_id: int, status: str, reason: str | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "status": status,
        "reason": reason,
        "updated_at": _timestamp(),
    }


def link_document_to_case(case_id: int, document_id: int | None = None, document: dict[str, Any] | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "document_id": document_id or (document or {}).get("id"),
        "document": document or {},
        "linked": True,
        "linked_at": _timestamp(),
    }


def link_contact_to_case(case_id: int, contact_id: int, role: str | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "contact_id": contact_id,
        "role": role or "related_party",
        "linked": True,
        "linked_at": _timestamp(),
    }


def create_case_timeline_event(case_id: int, event_type: str, title: str, description: str | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "event_type": event_type,
        "title": title,
        "description": description,
        "created_at": _timestamp(),
    }


def get_case_summary(case_id: int, data: dict[str, Any] | None = None) -> dict[str, Any]:
    payload = data or {}
    return {
        "case_id": case_id,
        "title": payload.get("title", "Fascicolo"),
        "status": payload.get("status", "unknown"),
        "documents_count": len(payload.get("documents", [])),
        "contacts_count": len(payload.get("contacts", [])),
        "timeline_count": len(payload.get("timeline", [])),
        "generated_at": _timestamp(),
    }
