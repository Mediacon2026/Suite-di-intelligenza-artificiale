from __future__ import annotations

import re

from .base_parser import BaseParser
from .document_classifier import classify_document


REQUIRED_FIELDS = [
    "organization_name",
    "organization_registration_number",
    "office_name",
    "office_city",
    "office_address",
    "office_email",
    "office_pec",
    "office_status",
    "mediation_number",
    "deposit_date",
    "deposit_time",
    "claimant",
    "claimant_lawyer",
    "invited_party",
    "invited_party_lawyer",
    "mediator_name",
    "first_meeting_date",
    "first_meeting_time",
    "meeting_mode",
    "meeting_location",
    "matter",
    "claim_value",
    "mediation_type",
    "competent_court",
    "object",
    "reasons",
    "pec_addresses",
    "email_addresses",
]


MEDIACON_OFFICES = [
    {
        "city": "Casarano",
        "office_name": "Sede Principale di Casarano",
        "address": "Via Bruno Buozzi 10",
        "email": "info@mediacon.org",
        "pec": "mediacon@arubapec.it",
    },
    {
        "city": "Pachino",
        "office_name": "Sede Operativa di Pachino",
        "address": "Via Fratelli Bandiera 82",
        "email": "pachino@mediacon.org",
        "pec": "mediaconpachino@arubapec.it",
    },
    {
        "city": "Napoli",
        "office_name": "Sede Operativa di Napoli",
        "address": "Via Enrico Pessina 66",
        "email": "napoli@mediacon.org",
        "pec": "mediaconnapoli@arubapec.it",
    },
]


def clean_value(value: str | None) -> str:
    if not value:
        return ""
    cleaned = re.sub(r"\s+", " ", value).strip(" \t\r\n:;-")
    if not cleaned or re.fullmatch(r"[._\-\s]+", cleaned):
        return ""
    return cleaned


def normalize_text(text: str | None) -> str:
    return re.sub(r"[ \t]+", " ", text or "")


def find_first(text: str, patterns: list[str]) -> str:
    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE | re.DOTALL)
        if match:
            return clean_value(match.group(1))
    return ""


def find_date(text: str, labels: list[str]) -> str:
    for label in labels:
        match = re.search(rf"{label}\s*[:\-]?\s*(\d{{1,2}}[\/\-.]\d{{1,2}}[\/\-.]\d{{2,4}})", text, re.IGNORECASE)
        if match:
            return clean_value(match.group(1))
    return ""


def find_time(text: str, labels: list[str]) -> str:
    for label in labels:
        match = re.search(rf"{label}.*?(\d{{1,2}}[:.]\d{{2}})", text, re.IGNORECASE | re.DOTALL)
        if match:
            return clean_value(match.group(1).replace(".", ":"))
    return ""


class MediationParser(BaseParser):
    module = "mediazione"
    case_type = "mediation"

    def parse(self, text: str, filename: str = "") -> dict:
        normalized = normalize_text(text)
        classifier = classify_document(normalized, filename)
        fields = self.extract_fields(normalized)
        missing = [field for field in REQUIRED_FIELDS if not fields.get(field)]
        confidence_by_field = {
            field: (0.9 if fields.get(field) else 0.2)
            for field in REQUIRED_FIELDS
        }
        confidence_score = round(sum(confidence_by_field.values()) / len(confidence_by_field), 2)
        return {
            "module": self.module,
            "case_type": self.case_type,
            "document_type": classifier["document_type"],
            "confidence_score": confidence_score,
            "extracted_fields": fields,
            "confidence_by_field": confidence_by_field,
            "missing_fields": missing,
            "suggested_actions": self.suggest_actions(missing, classifier["document_type"]),
            "validation_errors": [],
        }

    def extract_fields(self, text: str) -> dict:
        emails = sorted(set(re.findall(r"[\w.\-+]+@[\w.\-]+\.[A-Za-z]{2,}", text)))
        pec_addresses = [email for email in emails if "pec" in email.lower() or "legalmail" in email.lower() or "arubapec" in email.lower()]
        fields = {field: "" for field in REQUIRED_FIELDS}
        fields["organization_name"] = find_first(text, [r"(Organismo di Mediazione\s+[^\n]+)", r"(Mediacon[^\n]*)"])
        fields["organization_registration_number"] = find_first(text, [r"(?:n\.?|numero)\s*(?:iscrizione|registro)?\s*[:\-]?\s*(\d{1,6})"])
        fields["mediation_number"] = find_first(text, [r"(?:mediazione|procedura|pratica)\s*(?:n\.?|numero)?\s*[:\-]?\s*([A-Z0-9./-]+)"])
        fields["deposit_date"] = find_date(text, ["deposito", "depositata il", "istanza del"])
        fields["deposit_time"] = find_time(text, ["deposito", "depositata"])
        fields["claimant"] = find_first(text, [r"(?:parte istante|istante|ricorrente)\s*[:\-]?\s*(.+?)(?=\n|parte invitata|avv|avvocato|pec|$)"])
        fields["claimant_lawyer"] = find_first(text, [r"(?:avvocato istante|avv\.?\s*istante|difensore istante|legale istante)\s*[:\-]?\s*(.+?)(?=\n|parte invitata|pec|$)", r"(?:avvocato|avv\.?|legale)\s*[:\-]?\s*(.+?)(?=\n|pec|$)"])
        fields["invited_party"] = find_first(text, [r"(?:parte invitata|invitata|convenuto|resistente)\s*[:\-]?\s*(.+?)(?=\n|avv|avvocato|pec|$)"])
        fields["invited_party_lawyer"] = find_first(text, [r"(?:avvocato invitata|avvocato invitato|avv\.?\s*invitata|avv\.?\s*invitato|legale invitata|legale invitato)\s*[:\-]?\s*(.+?)(?=\n|pec|$)"])
        fields["mediator_name"] = find_first(text, [r"(?:mediatore|mediatrice)\s*[:\-]?\s*(.+?)(?=\n|primo incontro|incontro|$)"])
        fields["first_meeting_date"] = find_date(text, ["primo incontro", "incontro fissato", "incontro"])
        fields["first_meeting_time"] = find_time(text, ["primo incontro", "incontro fissato", "incontro"])
        fields["meeting_mode"] = self.detect_meeting_mode(text)
        fields["meeting_location"] = find_first(text, [r"(?:presso|luogo|sede|link)\s*[:\-]?\s*(.+?)(?=\n|ore|$)"])
        fields["matter"] = find_first(text, [r"(?:materia)\s*[:\-]?\s*(.+?)(?=\n|oggetto|valore|$)"])
        fields["claim_value"] = find_first(text, [r"(?:valore(?: della lite)?|valore controversia)\s*[:\-]?\s*(?:euro|€)?\s*([0-9. ]+(?:,\d{2})?)"])
        fields["mediation_type"] = find_first(text, [r"(?:mediazione)\s*(obbligatoria|volontaria|delegata|demandata)"])
        fields["competent_court"] = find_first(text, [r"(?:tribunale competente|foro competente|competente il tribunale di)\s*[:\-]?\s*(.+?)(?=\n|$)"])
        fields["object"] = find_first(text, [r"(?:oggetto)\s*[:\-]?\s*(.+?)(?=\n|ragioni|materia|valore|$)"])
        fields["reasons"] = find_first(text, [r"(?:ragioni(?: della pretesa)?|motivi)\s*[:\-]?\s*(.+?)(?=\n|materia|valore|tribunale|$)"])
        fields["pec_addresses"] = pec_addresses
        fields["email_addresses"] = emails
        self.apply_mediacon_office(text, fields)
        return fields

    def apply_mediacon_office(self, text: str, fields: dict) -> None:
        lowered = text.lower()
        for office in MEDIACON_OFFICES:
            if (
                office["city"].lower() in lowered
                or office["address"].lower() in lowered
                or office["email"].lower() in lowered
                or office["pec"].lower() in lowered
                or office["office_name"].lower() in lowered
            ):
                fields["organization_name"] = fields["organization_name"] or "Mediacon"
                fields["office_city"] = office["city"]
                fields["office_name"] = office["office_name"]
                fields["office_address"] = office["address"]
                fields["office_email"] = office["email"]
                fields["office_pec"] = office["pec"]
                fields["office_status"] = "confermato"
                return
        fields["office_city"] = fields.get("office_city") or None
        fields["office_status"] = "da_verificare"

    def detect_meeting_mode(self, text: str) -> str:
        lowered = text.lower()
        if "mista" in lowered:
            return "mista"
        if "videoconferenza" in lowered or "telematica" in lowered or "webex" in lowered:
            return "telematica"
        if "presso la sede" in lowered:
            return "presenza"
        return ""

    def suggest_actions(self, missing: list[str], document_type: str) -> list[str]:
        actions = []
        if missing:
            actions.append("Aprire Review Center e verificare i campi mancanti.")
        if document_type == "istanza_mediazione":
            actions.append("Preparare creazione fascicolo e procedura di mediazione dopo conferma.")
        if document_type == "lettera_convocazione":
            actions.append("Collegare convocazione alla timeline della mediazione.")
        return actions
