from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


class AutomationScheduler:
    def schedule(self, case_id: int, rules: list[dict[str, Any]]) -> dict[str, Any]:
        return {
            "case_id": case_id,
            "status": "scheduled" if rules else "idle",
            "pending_rules": [rule["rule_key"] for rule in rules],
            "scheduled_at": datetime.now(timezone.utc).isoformat(),
        }
