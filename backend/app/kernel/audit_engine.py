from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


def _log(action_type: str, action: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    return {
        "action_type": action_type,
        "action": action,
        "payload": payload or {},
        "status": "logged_placeholder",
        "created_at": datetime.now(timezone.utc).isoformat(),
    }


def log_action(action: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    return _log("user_action", action, payload)


def log_ai_action(action: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    return _log("ai_action", action, payload)


def log_document_action(action: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    return _log("document_action", action, payload)


def log_signature_action(action: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    return _log("signature_action", action, payload)


def log_rule_evaluation(action: str, payload: dict[str, Any] | None = None) -> dict[str, Any]:
    return _log("rule_evaluation", action, payload)
