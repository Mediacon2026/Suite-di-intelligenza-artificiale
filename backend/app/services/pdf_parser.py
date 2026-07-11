import re
from pathlib import Path

from pypdf import PdfReader


def extract_pdf_text(path: str | Path) -> str:
    reader = PdfReader(str(path))
    return "\n".join(page.extract_text() or "" for page in reader.pages)


def extract_case_json(text: str) -> dict:
    normalized = re.sub(r"[ \t]+", " ", text or "")

    def find(labels: list[str]) -> str | None:
        for label in labels:
            match = re.search(
                rf"{label}\s*[:\-]?\s*(.+?)(?=\n|Parte|Avvocato|PEC|Valore|Oggetto|Materia|$)",
                normalized,
                re.IGNORECASE | re.DOTALL,
            )
            if match:
                return re.sub(r"\s+", " ", match.group(1)).strip(" .:-")
        return None

    pecs = re.findall(r"[\w.\-+]+@[\w.\-]+\.[A-Za-z]{2,}", normalized)
    value_text = find(["Valore", "Valore della lite"]) or ""
    value_match = re.search(r"([0-9]{1,3}(?:[.\s][0-9]{3})*(?:,[0-9]{2})|[0-9]+(?:\.[0-9]{2})?)", value_text)

    return {
        "claimant": find(["Parte istante", "Istante", "Ricorrente"]),
        "invited_party": find(["Parte invitata", "Invitata", "Convenuto"]),
        "lawyer": find(["Avvocato", "Legale"]),
        "pec": pecs[0] if pecs else None,
        "claim_value": float(value_match.group(1).replace(".", "").replace(" ", "").replace(",", ".")) if value_match else None,
        "object": find(["Oggetto"]),
        "matter": find(["Materia"]),
    }
