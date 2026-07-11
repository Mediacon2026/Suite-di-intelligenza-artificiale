from backend.app.kernel.next_action_engine import calculate_next_action
from backend.app.parsers.mediation_parser import MediationParser


def test_istanza_pachino_office_recognition():
    result = MediationParser().parse("Istanza Pachino - Via Fratelli Bandiera 82 - pachino@mediacon.org", "Istanza Pachino.pdf")
    fields = result["extracted_fields"]
    assert fields["office_city"] == "Pachino"
    assert fields["office_email"] == "pachino@mediacon.org"
    assert fields["office_pec"] == "mediaconpachino@arubapec.it"
    assert fields["office_status"] == "confermato"


def test_istanza_napoli_office_recognition():
    result = MediationParser().parse("Istanza Napoli - Via Enrico Pessina 66 - napoli@mediacon.org", "Istanza Napoli.pdf")
    fields = result["extracted_fields"]
    assert fields["office_city"] == "Napoli"
    assert fields["office_email"] == "napoli@mediacon.org"
    assert fields["office_pec"] == "mediaconnapoli@arubapec.it"
    assert fields["office_status"] == "confermato"


def test_ambiguous_office_is_not_invented():
    result = MediationParser().parse("Istanza di mediazione senza riferimenti alla sede", "Istanza.pdf")
    fields = result["extracted_fields"]
    assert fields["office_city"] is None
    assert fields["office_status"] == "da_verificare"


def test_next_action_assign_mediator_when_missing():
    result = calculate_next_action(1, {"mediator_assigned": False})
    assert result["action_key"] == "ASSIGN_MEDIATOR"


def test_next_action_send_assignment_when_mediator_selected():
    result = calculate_next_action(1, {"mediator_assigned": True, "mediator_assignment_sent": False})
    assert result["action_key"] == "SEND_MEDIATOR_APPOINTMENT"


def test_next_action_wait_acceptance_after_sent():
    result = calculate_next_action(1, {"mediator_assigned": True, "mediator_assignment_sent": True, "mediator_accepted": False})
    assert result["action_key"] == "WAIT_MEDIATOR_ACCEPTANCE"


def test_next_action_schedule_first_meeting_after_acceptance():
    result = calculate_next_action(
        1,
        {
            "mediator_assigned": True,
            "mediator_assignment_sent": True,
            "mediator_accepted": True,
            "first_meeting_scheduled": False,
        },
    )
    assert result["action_key"] == "SCHEDULE_FIRST_MEETING"
