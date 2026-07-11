from backend.app.parsers.mediation_parser import MediationParser


def test_pachino_office_detected():
    result = MediationParser().parse("Istanza di Mediazione presso Sede Operativa di Pachino Via Fratelli Bandiera 82")
    assert result["extracted_fields"]["office_city"] == "Pachino"


def test_napoli_office_detected():
    result = MediationParser().parse("Organismo di Mediazione - Sede Operativa di Napoli Via Enrico Pessina 66")
    assert result["extracted_fields"]["office_city"] == "Napoli"


def test_convocation_document_type():
    result = MediationParser().parse("Lettera di convocazione primo incontro", "Lettera primo incontro.docx")
    assert result["document_type"] == "lettera_convocazione"


def test_assignment_document_type():
    result = MediationParser().parse("Assunzione incarico mediatore", "Assunzione incarico.ODT")
    assert result["document_type"] == "assunzione_incarico"


def test_impartiality_document_type():
    result = MediationParser().parse("Dichiarazione imparzialita e riservatezza", "Dichiarazione imparzialita.ODT")
    assert result["document_type"] == "dichiarazione_imparzialita"
