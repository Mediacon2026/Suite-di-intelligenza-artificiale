from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def create_notification(recipient: str, subject: str, body: str, channel: str = "internal") -> dict[str, Any]:
    return {
        "recipient": recipient,
        "subject": subject,
        "body": body,
        "channel": channel,
        "status": "created",
        "created_at": _now(),
    }


def send_email_placeholder(to_email: str, subject: str, body: str) -> dict[str, Any]:
    return {
        "to": to_email,
        "subject": subject,
        "body": body,
        "status": "not_sent_placeholder",
        "channel": "email",
        "created_at": _now(),
    }


def send_pec_placeholder(to_pec: str, subject: str, body: str) -> dict[str, Any]:
    return {
        "to": to_pec,
        "subject": subject,
        "body": body,
        "status": "not_sent_placeholder",
        "channel": "pec",
        "created_at": _now(),
    }


def send_mediator_assignment_notice(mediator: dict[str, Any], mediation: dict[str, Any]) -> dict[str, Any]:
    return {
        "type": "mediator_assignment",
        "mediator": mediator,
        "mediation": mediation,
        "status": "prepared_placeholder",
        "created_at": _now(),
    }


def send_meeting_notice(recipients: list[dict[str, Any]], meeting: dict[str, Any]) -> dict[str, Any]:
    return {
        "type": "meeting_notice",
        "recipients": recipients,
        "meeting": meeting,
        "status": "prepared_placeholder",
        "created_at": _now(),
    }
