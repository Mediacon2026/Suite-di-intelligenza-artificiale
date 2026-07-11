from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def verbal_for_outcome(outcome: str | None) -> str:
    mapping = {
        "mancata_adesione": "verbale_mancata_adesione",
        "rinvio": "verbale_rinvio",
        "negativo": "verbale_negativo",
        "accordo_primo_incontro": "verbale_accordo",
        "accordo_successivo": "verbale_accordo",
        "negativo_successivo": "verbale_negativo",
    }
    return mapping.get(outcome or "", "verbale_primo_incontro")


class AutomationExecutor:
    def execute_action(self, action_key: str, case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
        context = context or {}
        result: dict[str, Any] = {
            "case_id": case_id,
            "action_key": action_key,
            "status": "executed",
            "executed_at": _now(),
        }
        if action_key.startswith("generate_"):
            result["document_type"] = action_key.replace("generate_", "")
        if action_key == "prepare_mediator_email":
            result["email_status"] = "prepared_placeholder"
        if action_key == "prepare_webex_link":
            result["provider"] = "WEBEX"
            result["meeting_url"] = context.get("meeting_url") or "Da generare"
        if action_key in {"audit_ai", "audit_workflow", "audit_automation"}:
            result["audit_type"] = action_key.replace("audit_", "")
        if action_key == "suggest_verbal":
            result["suggested_document"] = verbal_for_outcome(context.get("outcome"))
        if action_key == "prepare_outcome_document":
            result["document_type"] = verbal_for_outcome(context.get("outcome"))
        if action_key == "update_next_action":
            result["next_action_updated"] = True
        if action_key == "update_timeline":
            result["timeline_updated"] = True
        if action_key == "update_mediator_status":
            result["mediator_status"] = "accepted"
        return result

    def execute_rule(self, rule: dict[str, Any], case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
        actions = [self.execute_action(action, case_id, context) for action in rule.get("actions", [])]
        return {
            "case_id": case_id,
            "rule_key": rule["rule_key"],
            "event_type": rule["event_type"],
            "status": "executed",
            "actions": actions,
            "executed_at": _now(),
        }
