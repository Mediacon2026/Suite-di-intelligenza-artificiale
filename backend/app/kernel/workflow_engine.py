from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def start_workflow(case_id: int, workflow_key: str, context: dict[str, Any] | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "workflow_key": workflow_key,
        "status": "started",
        "current_step": "start",
        "context": context or {},
        "started_at": _now(),
    }


def advance_workflow(case_id: int, workflow_key: str, current_step: str, next_step: str | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "workflow_key": workflow_key,
        "previous_step": current_step,
        "current_step": next_step or "next",
        "status": "advanced",
        "advanced_at": _now(),
    }


def get_current_step(case_id: int, workflow_key: str) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "workflow_key": workflow_key,
        "current_step": "placeholder_step",
        "status": "ready",
    }


def get_next_actions(case_id: int, workflow_key: str) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "workflow_key": workflow_key,
        "actions": [],
        "status": "ready",
    }


def complete_step(case_id: int, workflow_key: str, step_key: str) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "workflow_key": workflow_key,
        "step_key": step_key,
        "status": "completed",
        "completed_at": _now(),
    }
