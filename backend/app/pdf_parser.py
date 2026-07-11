import re
from pathlib import Path

from pypdf import PdfReader


def extract_pdf_text(path: str | Path) -> str:
    reader = PdfReader(str(path))
    pages = []
    for page in reader.pages:
        pages.append(page.extract_text() or "")
    return "\n".join(pages)


def parse_mediation_application(text: str) -> dict:
    normalized = re.sub(r"[ \t]+", " ", text)

    def find(label_patterns: list[str], stop_words: str = r"\n|Parte|Avvocato|PEC|Oggetto|Valore|Materia|Ragioni") -> str | None:
        for label in label_patterns:
            match = re.search(rf"{label}\s*[:\-]?\s*(.+?)(?={stop_words}|$)", normalized, re.IGNORECASE | re.DOTALL)
            if match:
                return re.sub(r"\s+", " ", match.group(1)).strip(" .:-")
        return None

    pecs = re.findall(r"[\w.\-+]+@[\w.\-]+\.[A-Za-z]{2,}", normalized)
    value_match = re.search(r"(?:€|euro)?\s*([0-9]{1,3}(?:[.\s][0-9]{3})*(?:,[0-9]{2})|[0-9]+(?:\.[0-9]{2})?)", find([r"Valore(?:\s+della\s+lite)?"]) or "")
    claim_value = None
    if value_match:
        claim_value = float(value_match.group(1).replace(".", "").replace(" ", "").replace(",", "."))

    return {
        "claimant": find([r"Parte\s+istante", r"Istante", r"Ricorrente"]),
        "invited_party": find([r"Parte\s+invitata", r"Invitata", r"Convenuto"]),
        "lawyer": find([r"Avvocato", r"Legale"]),
        "pec": pecs[0] if pecs else None,
        "object": find([r"Oggetto"]),
        "claim_value": claim_value,
        "matter": find([r"Materia"]),
        "reasons": find([r"Ragioni", r"Motivi", r"Descrizione"]),
        "raw_text_preview": normalized[:3000],
    }
