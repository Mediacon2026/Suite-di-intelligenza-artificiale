from backend.app.kernel import workflow_engine


def test_start_workflow():
    result = workflow_engine.start_workflow(1, "mediation")
    assert result["case_id"] == 1
    assert result["workflow_key"] == "mediation"
    assert result["status"] == "started"


def test_get_current_step():
    result = workflow_engine.get_current_step(1, "mediation")
    assert result["case_id"] == 1
    assert result["status"] == "ready"
    assert "current_step" in result


def test_advance_workflow():
    result = workflow_engine.advance_workflow(1, "mediation", "start", "review")
    assert result["previous_step"] == "start"
    assert result["current_step"] == "review"
    assert result["status"] == "advanced"


def test_complete_step():
    result = workflow_engine.complete_step(1, "mediation", "review")
    assert result["step_key"] == "review"
    assert result["status"] == "completed"
