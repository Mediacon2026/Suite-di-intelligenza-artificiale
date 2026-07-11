from __future__ import annotations

from datetime import datetime, timezone
from typing import Any

from .automation_executor import AutomationExecutor
from .automation_rules import SUPPORTED_EVENTS, rules_for_event
from .automation_scheduler import AutomationScheduler


class AutomationEngine:
    def __init__(self):
        self.executor = AutomationExecutor()
        self.scheduler = AutomationScheduler()
        self.logs: list[dict[str, Any]] = []

    def evaluate(self, case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
        context = context or {}
        event_type = context.get("event_type") or "case.created"
        rules = rules_for_event(event_type, context)
        return {
            "case_id": case_id,
            "event_type": event_type,
            "supported": event_type in SUPPORTED_EVENTS,
            "rules": rules,
            "rules_count": len(rules),
            "evaluated_at": datetime.now(timezone.utc).isoformat(),
        }

    def schedule(self, case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
        evaluation = self.evaluate(case_id, context)
        scheduled = self.scheduler.schedule(case_id, evaluation["rules"])
        self.logs.append({**scheduled, "type": "schedule"})
        return scheduled

    def run_rule(self, rule: dict[str, Any], case_id: int | None = None, context: dict[str, Any] | None = None) -> dict[str, Any]:
        result = self.executor.execute_rule(rule, case_id or int((context or {}).get("case_id") or 0), context)
        self.logs.append({**result, "type": "rule"})
        return result

    def execute(self, case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
        evaluation = self.evaluate(case_id, context)
        results = [self.run_rule(rule, case_id, context) for rule in evaluation["rules"]]
        status = "executed" if results else "manual"
        log = {
            "case_id": case_id,
            "event_type": evaluation["event_type"],
            "status": status,
            "results": results,
            "executed_at": datetime.now(timezone.utc).isoformat(),
            "type": "execution",
        }
        self.logs.append(log)
        return log

    def status(self, case_id: int) -> dict[str, Any]:
        case_logs = [log for log in self.logs if log.get("case_id") == case_id]
        return {
            "case_id": case_id,
            "executed": len([log for log in case_logs if log.get("status") == "executed"]),
            "pending": len([log for log in case_logs if log.get("status") == "scheduled"]),
            "error": len([log for log in case_logs if log.get("status") == "error"]),
            "manual": len([log for log in case_logs if log.get("status") == "manual"]),
        }
