from backend.app.ai.checklist_service import build_checklist
from backend.app.kernel import audit_engine, case_engine, document_engine, event_engine, workflow_engine
from backend.app.kernel.kernel_service import create_case_from_intake
from backend.app.parsers.parser_registry import get_parser


def test_intake_to_case_flow_without_duplicates():
    intake_session = {
        "id": 1,
        "user": "alpha-tester",
        "organization_id": 1,
        "status": "in_review",
        "documents": [],
        "parser_results": [],
        "review_status": "pending",
    }
    text = (
        "Istanza di Mediazione presso Sede Operativa di Pachino Via Fratelli Bandiera 82\n"
        "Parte istante: Mario Rossi\n"
        "Avvocato istante: Avv. Bianchi\n"
        "Parte invitata: Alfa Srl\n"
        "Materia: Condominio\n"
        "Oggetto: Recupero somme\n"
        "Valore controversia: 10000,00\n"
        "PEC: alfa@pec.it"
    )

    parser = get_parser("mediation", text, "Istanza.pdf")
    parser_result = parser.parse(text, "Istanza.pdf")
    document = {
        "id": 10,
        "filename": "Istanza.pdf",
        "document_type": parser_result["document_type"],
        "parser_result": parser_result,
    }
    intake_session["documents"].append(document)
    intake_session["parser_results"].append(parser_result)
    intake_session["review_status"] = "confirmed"

    case_created = create_case_from_intake({"id": 100, "case_type": "mediation", "title": "Mediazione Mario Rossi"})
    case_id = case_created["case"]["case"]["id"]
    mediation = {"id": 200, "case_id": case_id, "status": "depositata"}
    workflow = workflow_engine.start_workflow(case_id, "mediation")
    linked_document = case_engine.link_document_to_case(case_id, document_id=document["id"], document=document)
    timeline_event = case_engine.create_case_timeline_event(case_id, "case.created", "Fascicolo creato")
    generated_document = document_engine.generate_document("startup_placeholder", {"case_id": case_id})
    document_event = event_engine.publish_event("document.generated", {"case_id": case_id, "document": generated_document})
    checklist = build_checklist("mediation", [document], {"status": mediation["status"], "timeline": [timeline_event]})
    audit = audit_engine.log_ai_action("intake_to_case_flow", {"intake_session_id": intake_session["id"], "case_id": case_id})
    events = event_engine.list_events_for_case(case_id)

    assert intake_session["id"]
    assert parser_result["document_type"] == "istanza_mediazione"
    assert case_id == 100
    assert mediation["id"] == 200
    assert workflow["status"] == "started"
    assert linked_document["linked"] is True
    assert timeline_event["event_type"] == "case.created"
    assert checklist["items"]
    assert audit["status"] == "logged_placeholder"
    assert document_event in events["events"]
    assert len({document["id"] for document in intake_session["documents"]}) == len(intake_session["documents"])
