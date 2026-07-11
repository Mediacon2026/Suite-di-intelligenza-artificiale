from .checklist_service import suggestions_from_checklist


def answer_question(question: str, case_record: dict, documents: list[dict], checklist: dict, deadlines: list[dict], tasks: list[dict]) -> dict:
    q = question.lower()
    extracted = {}
    for doc in documents:
        extracted.update(doc.get("extracted_json") or {})

    if "istante" in q:
        answer = extracted.get("claimant") or "Parte istante non trovata nei documenti analizzati."
    elif "invitata" in q:
        answer = extracted.get("invited_party") or "Parte invitata non trovata nei documenti analizzati."
    elif "avvocat" in q:
        answer = extracted.get("lawyer") or "Avvocati non trovati nei documenti analizzati."
    elif "valore" in q:
        answer = ", ".join(extracted.get("economic_values") or []) or "Valore non trovato."
    elif "materia" in q:
        answer = extracted.get("matter") or "Materia non trovata."
    elif "documenti mancano" in q or "mancano" in q:
        answer = " ".join(suggestions_from_checklist(checklist, extracted)) or "La checklist risulta completa."
    elif "scade" in q or "termine" in q:
        open_deadlines = [d for d in deadlines if not d.get("completed")]
        answer = open_deadlines[0]["deadline_date"] if open_deadlines else "Nessuna scadenza aperta."
    elif "incontri" in q:
        answer = str(sum(1 for task in tasks if "incontro" in (task.get("title") or "").lower()))
    elif "ultimo verbale" in q:
        verbali = [doc for doc in documents if doc.get("document_type") == "verbale"]
        answer = verbali[0]["original_filename"] if verbali else "Nessun verbale caricato."
    else:
        answer = "Posso rispondere su parti, avvocati, valore, materia, documenti mancanti, scadenze e verbali."

    return {"answer": answer, "suggestions": suggestions_from_checklist(checklist, extracted)}
