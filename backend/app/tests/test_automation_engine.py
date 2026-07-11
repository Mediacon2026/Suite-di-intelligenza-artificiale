from backend.app.kernel.automation import AutomationEngine
from backend.app.kernel.automation.automation_executor import verbal_for_outcome


def test_mediator_assignment_automation():
    engine = AutomationEngine()
    result = engine.execute(1, {"event_type": "mediator.assigned"})
    actions = [action["action_key"] for rule in result["results"] for action in rule["actions"]]
    assert result["status"] == "executed"
    assert "generate_assunzione_incarico" in actions
    assert "generate_dichiarazione_imparzialita" in actions
    assert "prepare_mediator_email" in actions
    assert "update_timeline" in actions
    assert "update_next_action" in actions


def test_document_signed_updates_mediator_status():
    engine = AutomationEngine()
    result = engine.execute(2, {"event_type": "document.signed", "document_type": "assunzione_incarico"})
    actions = [action for rule in result["results"] for action in rule["actions"]]
    assert result["status"] == "executed"
    assert any(action.get("mediator_status") == "accepted" for action in actions)
    assert any(action.get("next_action_updated") is True for action in actions)


def test_meeting_created_prepares_notice_and_webex():
    engine = AutomationEngine()
    result = engine.execute(3, {"event_type": "meeting.created", "meeting_mode": "telematica"})
    actions = [action["action_key"] for rule in result["results"] for action in rule["actions"]]
    assert "generate_first_meeting_notice" in actions
    assert "generate_modulo_adesione" in actions
    assert "generate_privacy" in actions
    assert "generate_regolamento" in actions
    assert "prepare_webex_link" in actions


def test_meeting_completed_suggests_verbal():
    engine = AutomationEngine()
    result = engine.execute(4, {"event_type": "meeting.completed", "outcome": "rinvio"})
    actions = [action for rule in result["results"] for action in rule["actions"]]
    assert any(action.get("suggested_document") == "verbale_rinvio" for action in actions)
    assert verbal_for_outcome("accordo_successivo") == "verbale_accordo"


def test_automation_audit_contracts():
    engine = AutomationEngine()
    result = engine.execute(5, {"event_type": "mediator.assigned"})
    actions = [action for rule in result["results"] for action in rule["actions"]]
    assert any(action.get("audit_type") == "ai" for action in actions)
    assert any(action.get("audit_type") == "workflow" for action in actions)
    assert any(action.get("audit_type") == "automation" for action in actions)


def test_automation_status_and_logs():
    engine = AutomationEngine()
    engine.schedule(6, {"event_type": "mediator.assigned"})
    engine.execute(6, {"event_type": "mediator.assigned"})
    status = engine.status(6)
    assert status["pending"] == 1
    assert status["executed"] == 2
    assert len([log for log in engine.logs if log["case_id"] == 6]) == 3
