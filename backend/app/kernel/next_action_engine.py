from __future__ import annotations

from datetime import datetime, timezone
from typing import Any


ACTIONS: dict[str, dict[str, Any]] = {
    "REVIEW_INTAKE": {
        "title": "Revisionare i dati acquisiti",
        "description": "Controllare i dati estratti dai documenti prima della creazione della pratica.",
        "priority": "alta",
        "button_label": "Apri review",
    },
    "COMPLETE_MISSING_DATA": {
        "title": "Completare i dati mancanti",
        "description": "Sono presenti campi obbligatori da verificare o completare.",
        "priority": "alta",
        "button_label": "Completa dati",
    },
    "ASSIGN_MEDIATOR": {
        "title": "Assegnare il mediatore",
        "description": "La procedura non ha ancora un mediatore designato.",
        "priority": "alta",
        "button_label": "Assegna mediatore",
    },
    "SEND_MEDIATOR_APPOINTMENT": {
        "title": "Inviare la nomina al mediatore",
        "description": "Il mediatore e' stato designato ma il pacchetto di nomina non risulta inviato.",
        "priority": "alta",
        "button_label": "Prepara invio",
    },
    "WAIT_MEDIATOR_ACCEPTANCE": {
        "title": "Attendere accettazione mediatore",
        "description": "La nomina e' stata inviata e si attende firma o accettazione del mediatore.",
        "priority": "media",
        "button_label": "Registra firma",
    },
    "SCHEDULE_FIRST_MEETING": {
        "title": "Fissare il primo incontro",
        "description": "Non risulta programmato il primo incontro.",
        "priority": "alta",
        "button_label": "Fissa incontro",
    },
    "GENERATE_FIRST_MEETING_NOTICE": {
        "title": "Generare la convocazione",
        "description": "Il primo incontro e' programmato ma la convocazione non risulta generata.",
        "priority": "alta",
        "button_label": "Genera convocazione",
    },
    "SEND_FIRST_MEETING_NOTICE": {
        "title": "Inviare la convocazione",
        "description": "La convocazione risulta generata ma non ancora inviata.",
        "priority": "alta",
        "button_label": "Prepara invio",
    },
    "WAIT_FIRST_MEETING": {
        "title": "Attendere il primo incontro",
        "description": "La convocazione e' pronta: attendere lo svolgimento dell'incontro.",
        "priority": "media",
        "button_label": "Apri incontro",
    },
    "RECORD_FIRST_MEETING_OUTCOME": {
        "title": "Registrare esito primo incontro",
        "description": "Il primo incontro deve essere completato con esito e note.",
        "priority": "media",
        "button_label": "Registra esito",
    },
    "GENERATE_VERBAL": {
        "title": "Generare verbale",
        "description": "Preparare il verbale coerente con l'esito dell'incontro.",
        "priority": "media",
        "button_label": "Genera verbale",
    },
    "COMPLETE_DGSTAT": {
        "title": "Completare DGStat",
        "description": "Aggiornare i dati statistici ministeriali della procedura.",
        "priority": "media",
        "button_label": "Completa DGStat",
    },
    "ARCHIVE_CASE": {
        "title": "Archiviare la pratica",
        "description": "La pratica puo' essere archiviata dopo completamento documenti e statistiche.",
        "priority": "bassa",
        "button_label": "Archivia",
    },
}


def _action(action_key: str, reason: str, case_id: int | None = None) -> dict[str, Any]:
    base = ACTIONS[action_key]
    return {
        "case_id": case_id,
        "action_key": action_key,
        "title": base["title"],
        "description": base["description"],
        "priority": base["priority"],
        "reason": reason,
        "button_label": base["button_label"],
        "calculated_at": datetime.now(timezone.utc).isoformat(),
    }


def calculate_next_action(case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
    ctx = context or {}
    if ctx.get("review_status") not in {None, "confirmed", "ready"}:
        return _action("REVIEW_INTAKE", "La sessione di intake non risulta confermata.", case_id)
    if ctx.get("missing_required_data"):
        return _action("COMPLETE_MISSING_DATA", "Sono presenti dati obbligatori mancanti o da verificare.", case_id)
    if not ctx.get("mediator_assigned"):
        return _action("ASSIGN_MEDIATOR", "Manca il mediatore designato.", case_id)
    if ctx.get("mediator_assigned") and not ctx.get("mediator_assignment_sent"):
        return _action("SEND_MEDIATOR_APPOINTMENT", "Il mediatore e' stato designato ma la nomina non risulta inviata.", case_id)
    if ctx.get("mediator_assignment_sent") and not ctx.get("mediator_accepted"):
        return _action("WAIT_MEDIATOR_ACCEPTANCE", "La nomina e' stata inviata ma non risulta accettata.", case_id)
    if not ctx.get("first_meeting_scheduled"):
        return _action("SCHEDULE_FIRST_MEETING", "Non risulta programmato il primo incontro.", case_id)
    if ctx.get("first_meeting_scheduled") and not ctx.get("first_meeting_notice_generated"):
        return _action("GENERATE_FIRST_MEETING_NOTICE", "Il primo incontro e' programmato ma manca la convocazione.", case_id)
    if ctx.get("first_meeting_notice_generated") and not ctx.get("first_meeting_notice_sent"):
        return _action("SEND_FIRST_MEETING_NOTICE", "La convocazione e' generata ma non risulta inviata.", case_id)
    if not ctx.get("first_meeting_completed"):
        return _action("WAIT_FIRST_MEETING", "La procedura e' pronta per il primo incontro.", case_id)
    if not ctx.get("first_meeting_outcome_recorded"):
        return _action("RECORD_FIRST_MEETING_OUTCOME", "Il primo incontro risulta concluso ma manca l'esito.", case_id)
    if not ctx.get("verbal_generated"):
        return _action("GENERATE_VERBAL", "L'esito e' registrato ma manca il verbale coerente.", case_id)
    if not ctx.get("dgstat_completed"):
        return _action("COMPLETE_DGSTAT", "Il verbale e' disponibile ma i dati DGStat non risultano completi.", case_id)
    return _action("ARCHIVE_CASE", "Documenti e dati statistici risultano completati.", case_id)


def get_available_actions(case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
    next_action = calculate_next_action(case_id, context)
    return {
        "case_id": case_id,
        "next_action": next_action,
        "actions": list(ACTIONS.keys()),
    }


def complete_action(case_id: int, action_key: str) -> dict[str, Any]:
    if action_key not in ACTIONS:
        raise ValueError(f"Azione non supportata: {action_key}")
    return {
        "case_id": case_id,
        "action_key": action_key,
        "status": "completed",
        "completed_at": datetime.now(timezone.utc).isoformat(),
    }


def recalculate_after_event(case_id: int, context: dict[str, Any] | None = None) -> dict[str, Any]:
    return {
        "case_id": case_id,
        "next_action": calculate_next_action(case_id, context),
    }
