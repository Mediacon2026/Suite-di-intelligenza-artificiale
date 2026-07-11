from backend.app.kernel import audit_engine


def test_log_action():
    result = audit_engine.log_action("create", {"case_id": 1})
    assert result["action_type"] == "user_action"
    assert result["status"] == "logged_placeholder"


def test_log_ai_action():
    result = audit_engine.log_ai_action("parse", {"document_id": 1})
    assert result["action_type"] == "ai_action"


def test_log_document_action():
    result = audit_engine.log_document_action("generate", {"document_id": 1})
    assert result["action_type"] == "document_action"


def test_log_signature_action():
    result = audit_engine.log_signature_action("request", {"signature_id": 1})
    assert result["action_type"] == "signature_action"
