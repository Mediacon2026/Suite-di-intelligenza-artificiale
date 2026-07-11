from __future__ import annotations

from uuid import uuid4

from sqlalchemy import inspect, text

from backend.app.database import engine


def test_recovery_runtime_tables_exist():
    required = {
        "intake_sessions",
        "intake_session_documents",
        "mediator_assignments",
        "email_messages",
        "schema_migrations",
    }
    with engine.connect() as connection:
        assert required.issubset(set(inspect(connection).get_table_names()))


def test_database_transaction_really_rolls_back():
    marker = f"recovery-rollback-{uuid4()}"
    connection = engine.connect()
    transaction = connection.begin()
    try:
        connection.execute(
            text(
                """
                INSERT INTO audit_logs (organization_id, entity_name, action, new_value)
                VALUES (1, 'recovery_test', :marker, CAST(:payload AS jsonb))
                """
            ),
            {"marker": marker, "payload": "{}"},
        )
        transaction.rollback()
    finally:
        connection.close()

    with engine.connect() as verification:
        count = verification.execute(
            text("SELECT COUNT(*) FROM audit_logs WHERE action = :marker"),
            {"marker": marker},
        ).scalar_one()
    assert count == 0


def test_office_contacts_match_parser_registry():
    expected = {
        "casarano": ("info@mediacon.org", "mediacon@arubapec.it"),
        "pachino": ("pachino@mediacon.org", "mediaconpachino@arubapec.it"),
        "napoli": ("napoli@mediacon.org", "mediaconnapoli@arubapec.it"),
    }
    with engine.connect() as connection:
        rows = connection.execute(text("SELECT lower(name), email, pec FROM offices")).fetchall()
    actual = {row[0]: (row[1], row[2]) for row in rows}
    for office, contacts in expected.items():
        assert actual[office] == contacts
