from backend.app.kernel import case_engine


def test_create_case():
    result = case_engine.create_case({"id": 1, "case_number": "CASE-1", "title": "Fascicolo test"})
    assert result["status"] == "created"
    assert result["case"]["id"] == 1
    assert result["case"]["title"] == "Fascicolo test"


def test_update_case_status():
    result = case_engine.update_case_status(1, "in_progress", "review confermata")
    assert result["case_id"] == 1
    assert result["status"] == "in_progress"


def test_link_document_to_case():
    result = case_engine.link_document_to_case(1, document_id=10)
    assert result["case_id"] == 1
    assert result["document_id"] == 10
    assert result["linked"] is True


def test_link_contact_to_case():
    result = case_engine.link_contact_to_case(1, 22, "claimant")
    assert result["case_id"] == 1
    assert result["contact_id"] == 22
    assert result["role"] == "claimant"


def test_create_case_timeline_event():
    result = case_engine.create_case_timeline_event(1, "case.created", "Fascicolo creato")
    assert result["case_id"] == 1
    assert result["event_type"] == "case.created"
    assert result["title"] == "Fascicolo creato"
