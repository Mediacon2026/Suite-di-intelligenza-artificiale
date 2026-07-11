from backend.app.kernel import notification_engine


def test_create_notification():
    result = notification_engine.create_notification("utente", "Oggetto", "Corpo")
    assert result["recipient"] == "utente"
    assert result["status"] == "created"


def test_send_email_placeholder():
    result = notification_engine.send_email_placeholder("test@example.com", "Oggetto", "Corpo")
    assert result["to"] == "test@example.com"
    assert result["channel"] == "email"
    assert result["status"] == "not_sent_placeholder"


def test_send_pec_placeholder():
    result = notification_engine.send_pec_placeholder("test@pec.it", "Oggetto", "Corpo")
    assert result["to"] == "test@pec.it"
    assert result["channel"] == "pec"
    assert result["status"] == "not_sent_placeholder"
