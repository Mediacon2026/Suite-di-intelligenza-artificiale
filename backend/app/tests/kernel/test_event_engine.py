from backend.app.kernel import event_engine


def test_publish_event():
    event = event_engine.publish_event("case.created", {"case_id": 101})
    assert event["event_type"] == "case.created"
    assert event["known_event"] is True


def test_register_event_handler():
    def handler(event):
        return {"seen": event["event_type"]}

    result = event_engine.register_event_handler("case.created", handler)
    event = event_engine.publish_event("case.created", {"case_id": 102})
    handled = event_engine.handle_event(event)
    assert result["registered"] is True
    assert handled["handled"] is True
    assert {"seen": "case.created"} in handled["handler_results"]


def test_list_events_for_case():
    event_engine.publish_event("document.uploaded", {"case_id": 103, "document_id": 1})
    result = event_engine.list_events_for_case(103)
    assert result["case_id"] == 103
    assert result["count"] >= 1
