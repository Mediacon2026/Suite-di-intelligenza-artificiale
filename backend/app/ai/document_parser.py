import re
from email import policy
from email.parser import BytesParser
from pathlib import Path

from pypdf import PdfReader

from .document_classifier import basic_metadata, classify_document
from ..parsers.parser_registry import get_parser


def read_text(path: str | Path) -> tuple[str, int | None]:
    file_path = Path(path)
    suffix = file_path.suffix.lower()
    if suffix == ".pdf":
        reader = PdfReader(str(file_path))
        return "\n".join(page.extract_text() or "" for page in reader.pages), len(reader.pages)
    if suffix == ".eml":
        message = BytesParser(policy=policy.default).parsebytes(file_path.read_bytes())
        parts = []
        if message.get("subject"):
            parts.append(f"Oggetto: {message.get('subject')}")
        if message.get("from"):
            parts.append(f"Da: {message.get('from')}")
        if message.get("to"):
            parts.append(f"A: {message.get('to')}")
        body = message.get_body(preferencelist=("plain", "html"))
        if body:
            parts.append(body.get_content())
        return "\n".join(parts), None
    if suffix in {".txt", ".csv"}:
        return file_path.read_text(encoding="utf-8", errors="ignore"), None
    return "", None


def extract_structured_data(text: str, filename: str = "") -> dict:
    normalized = re.sub(r"[ \t]+", " ", text or "")
    emails = sorted(set(re.findall(r"[\w.\-+]+@[\w.\-]+\.[A-Za-z]{2,}", normalized)))
    fiscal_codes = sorted(set(re.findall(r"\b[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]\b", normalized, re.IGNORECASE)))
    vat_numbers = sorted(set(re.findall(r"\b(?:IT)?[0-9]{11}\b", normalized)))
    phones = sorted(set(re.findall(r"(?:\+39\s*)?(?:0\d{1,4}[\s.-]?\d{5,8}|3\d{2}[\s.-]?\d{6,7})", normalized)))
    values = sorted(set(re.findall(r"(?:€|euro)?\s*[0-9]{1,3}(?:[.\s][0-9]{3})*(?:,[0-9]{2})", normalized, re.IGNORECASE)))
    dates = sorted(set(re.findall(r"\b\d{1,2}[/-]\d{1,2}[/-]\d{2,4}\b", normalized)))
    pec = [email for email in emails if "pec" in email.lower() or "legalmail" in email.lower()]

    def find(labels: list[str]) -> str | None:
        for label in labels:
            match = re.search(rf"{label}\s*[:\-]?\s*(.+?)(?=\n|Parte|Avvocato|PEC|Valore|Oggetto|Materia|$)", normalized, re.IGNORECASE | re.DOTALL)
            if match:
                return re.sub(r"\s+", " ", match.group(1)).strip(" .:-")
        return None

    return {
        "document_name": filename,
        "subjects": sorted(set(re.findall(r"\b[A-Z][a-zàèéìòù]+ [A-Z][a-zàèéìòù]+\b", normalized)))[:20],
        "fiscal_codes": fiscal_codes,
        "vat_numbers": vat_numbers,
        "pec": pec,
        "emails": emails,
        "phones": phones,
        "addresses": [],
        "economic_values": values,
        "dates": dates,
        "case_number": find(["Numero pratica", "Pratica", "N. pratica"]),
        "object": find(["Oggetto"]),
        "matter": find(["Materia"]),
        "claimant": find(["Parte istante", "Istante", "Ricorrente"]),
        "invited_party": find(["Parte invitata", "Invitata", "Convenuto"]),
        "lawyer": find(["Avvocato", "Legale"]),
    }


def parse_document(path: str | Path, original_filename: str | None = None) -> dict:
    metadata = basic_metadata(path, original_filename)
    text, pages = read_text(path)
    extracted = extract_structured_data(text, metadata["original_filename"])
    doc_type = classify_document(metadata["original_filename"], text)
    parser_result = get_parser("mediation", text, metadata["original_filename"]).parse(text, metadata["original_filename"])
    if parser_result.get("document_type"):
        doc_type = parser_result["document_type"]
    return {
        **metadata,
        "document_type": doc_type,
        "classification_status": "classified",
        "extracted_text": text,
        "extracted_json": {
            **extracted,
            **parser_result.get("extracted_fields", {}),
            "document_type": doc_type,
            "parser_result": parser_result,
            "page_count": pages,
        },
    }
