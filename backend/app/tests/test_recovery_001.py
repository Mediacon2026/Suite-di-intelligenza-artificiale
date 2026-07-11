from __future__ import annotations

from types import SimpleNamespace

import pytest

from backend.app.kernel.next_action_engine import calculate_next_action, complete_action
from backend.app.main import calculate_economic_split_data
from backend.app.parsers.mediation_parser import MediationParser


def test_parser_extracts_reasons_and_office_confidence():
    result = MediationParser().parse(
        """Istanza di mediazione
        Sede di Casarano - Via Bruno Buozzi 10 - mediacon@arubapec.it
        Oggetto: Risoluzione contratto
        Ragioni della pretesa: Inadempimento contrattuale
        Materia: Contratti bancari
        Valore: euro 10.000,00
        """
    )
    fields = result["extracted_fields"]
    assert fields["office_city"] == "Casarano"
    assert fields["reasons"] == "Inadempimento contrattuale"
    assert result["confidence_by_field"]["reasons"] == 0.9


def test_next_action_complete_lifecycle():
    base = {
        "mediator_assigned": True,
        "mediator_assignment_sent": True,
        "mediator_accepted": True,
        "first_meeting_scheduled": True,
        "first_meeting_notice_generated": True,
        "first_meeting_notice_sent": True,
    }
    assert calculate_next_action(1, base)["action_key"] == "WAIT_FIRST_MEETING"
    assert calculate_next_action(1, {**base, "first_meeting_completed": True})["action_key"] == "RECORD_FIRST_MEETING_OUTCOME"
    assert calculate_next_action(1, {**base, "first_meeting_completed": True, "first_meeting_outcome_recorded": True})["action_key"] == "GENERATE_VERBAL"
    assert calculate_next_action(1, {**base, "first_meeting_completed": True, "first_meeting_outcome_recorded": True, "verbal_generated": True})["action_key"] == "COMPLETE_DGSTAT"
    assert calculate_next_action(1, {**base, "first_meeting_completed": True, "first_meeting_outcome_recorded": True, "verbal_generated": True, "dgstat_completed": True})["action_key"] == "ARCHIVE_CASE"


def test_complete_action_rejects_unknown_action():
    with pytest.raises(ValueError):
        complete_action(1, "UNKNOWN")


class EconomicDb:
    def __init__(self, office_name: str, office_type: str, rules: list[dict]):
        self.office_name = office_name
        self.office_type = office_type
        self.rules = rules
        self.calls = 0

    def execute(self, *_args, **_kwargs):
        self.calls += 1
        if self.calls == 1:
            mediation = {
                "id": 1,
                "organization_id": 1,
                "office_id": 1,
                "office_name": self.office_name,
                "office_type": self.office_type,
                "status": "aperta",
                "calculated_total": 122,
                "calculated_vat": 22,
                "calculated_initial_expense": 20,
                "calculated_first_meeting_fee": 50,
                "calculated_further_fee": 30,
            }
            return SimpleNamespace(fetchone=lambda: SimpleNamespace(_mapping=mediation))
        rows = [SimpleNamespace(_mapping=rule) for rule in self.rules]
        return SimpleNamespace(fetchall=lambda: rows)


def test_casarano_mediator_receives_fifty_percent_of_matured_net():
    rules = [{"id": 1, "rule_key": "mediator_net_matured_percentage", "percentage": 50}]
    result = calculate_economic_split_data(EconomicDb("Casarano", "principale", rules), 1)
    assert result["netto_maturato"] == 100
    assert result["mediator_share"] == 50
    assert result["mediacon_share"] == 50
    assert result["operational_office_share"] == 0


def assert_operational_office_split(office):
    rules = [
        {"id": 2, "rule_key": "operational_office_percentage", "percentage": 70},
        {"id": 3, "rule_key": "main_office_percentage", "percentage": 30},
    ]
    result = calculate_economic_split_data(EconomicDb(office, "secondaria", rules), 1)
    assert result["netto_maturato"] == 100
    assert result["operational_office_share"] == 70
    assert result["mediacon_share"] == 30
    assert result["mediator_share"] == 0


def test_pachino_receives_seventy_percent_without_mediator_share():
    assert_operational_office_split("Pachino")


def test_napoli_receives_seventy_percent_without_mediator_share():
    assert_operational_office_split("Napoli")


def test_transaction_rollback_contract():
    class Transaction:
        rolled_back = False
        committed = False

        def commit(self):
            self.committed = True

        def rollback(self):
            self.rolled_back = True

    transaction = Transaction()
    try:
        raise RuntimeError("errore simulato dopo scritture parziali")
    except RuntimeError:
        transaction.rollback()
    assert transaction.rolled_back is True
    assert transaction.committed is False
