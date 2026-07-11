from backend.app.kernel import rules_engine


def test_get_active_rules():
    result = rules_engine.get_active_rules("mediazione", 1)
    assert result["module"] == "mediazione"
    assert result["organization_id"] == 1
    assert result["rules"] == []


def test_evaluate_rule():
    result = rules_engine.evaluate_rule({"rule_key": "test_rule", "active": True}, {"case_id": 1})
    assert result["rule_key"] == "test_rule"
    assert result["applied"] is True


def test_evaluate_rules_for_case_without_rules():
    result = rules_engine.evaluate_rules_for_case(1)
    assert result["case_id"] == 1
    assert result["evaluations"] == []
