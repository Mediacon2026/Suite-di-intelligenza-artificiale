MEDIAZIONE_CHECKLIST = [
    ("istanza_presente", "Istanza presente", "istanza"),
    ("procura_presente", "Procura presente", "procura"),
    ("documento_identita_presente", "Documento identita presente", "documento_identita"),
    ("codice_fiscale_presente", "Codice fiscale presente", "codice_fiscale"),
    ("pagamento_spese_iniziali", "Pagamento spese iniziali", "ricevuta_pagamento"),
    ("mediatore_nominato", "Mediatore nominato", None),
    ("assunzione_incarico_firmata", "Assunzione incarico firmata", "assunzione_incarico"),
    ("dichiarazione_imparzialita_firmata", "Dichiarazione imparzialita firmata", "dichiarazione_imparzialita"),
    ("convocazione_generata", "Convocazione generata", "convocazione_primo_incontro"),
    ("convocazione_inviata", "Convocazione inviata", None),
    ("primo_incontro_fissato", "Primo incontro fissato", None),
    ("verbale_primo_incontro", "Verbale primo incontro", "verbale_primo_incontro"),
    ("verbale_finale", "Verbale finale", "verbale_finale"),
    ("dgstat_aggiornato", "DGStat aggiornato", None),
    ("pratica_archiviata", "Pratica archiviata", None),
]


def normalize_doc_type(value: str | None) -> str:
    return (value or "").strip().lower()


def document_present(doc_types: set[str], expected: str | None) -> bool:
    if not expected:
        return False
    if expected in doc_types:
        return True
    if expected == "verbale_finale":
        return any(item in doc_types for item in {"verbale_finale", "verbale_negativo", "verbale_accordo"})
    if expected == "ricevuta_pagamento":
        return any("pagamento" in item or "ricevuta" in item for item in doc_types)
    return False


def checklist_status(complete: bool, verifiable: bool = True) -> str:
    if complete:
        return "completo"
    return "incompleto" if verifiable else "da verificare"


def build_checklist(case_type: str, documents: list[dict], context: dict | None = None) -> dict:
    if case_type != "mediation":
        return {"items": [], "completion_percentage": 0}

    context = context or {}
    doc_types = {normalize_doc_type(doc.get("document_type")) for doc in documents}
    items = []

    computed = {
        "mediatore_nominato": bool(context.get("mediator_id") or context.get("mediator_assignment")),
        "assunzione_incarico_firmata": bool(context.get("mediator_assignment", {}).get("accepted_at")),
        "dichiarazione_imparzialita_firmata": bool(context.get("mediator_assignment", {}).get("accepted_at")),
        "convocazione_inviata": any(event.get("event_type") == "first_meeting_notice_sent" for event in context.get("timeline", [])),
        "primo_incontro_fissato": bool(context.get("sessions")),
        "dgstat_aggiornato": bool(context.get("dgstat_number")),
        "pratica_archiviata": context.get("status") in {"archiviata", "chiusa", "archived"},
    }

    for key, label, expected_doc in MEDIAZIONE_CHECKLIST:
        present = computed.get(key, document_present(doc_types, expected_doc))
        verifiable = key not in {"convocazione_inviata", "dgstat_aggiornato"} or bool(context)
        items.append(
            {
                "key": key,
                "label": label,
                "present": present,
                "status": checklist_status(present, verifiable),
            }
        )

    complete = sum(1 for item in items if item["status"] == "completo")
    return {
        "items": items,
        "completion_percentage": round((complete / len(items)) * 100, 2) if items else 0,
    }


def suggestions_from_checklist(checklist: dict, extracted: dict | None = None) -> list[str]:
    suggestions = [f"Manca {item['label']}." for item in checklist.get("items", []) if item.get("status") != "completo"]
    extracted = extracted or {}
    if not extracted.get("economic_values") and not extracted.get("claim_value"):
        suggestions.append("Valore non indicato.")
    return suggestions
