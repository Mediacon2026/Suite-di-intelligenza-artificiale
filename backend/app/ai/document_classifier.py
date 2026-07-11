from pathlib import Path


CATEGORIES = {
    "istanza": ["istanza", "domanda", "ricorso", "mediazione"],
    "procura": ["procura", "mandato"],
    "documento_identita": ["identita", "identità", "carta_identita", "documento"],
    "codice_fiscale": ["codice_fiscale", "codice fiscale", "tessera_sanitaria", "cf"],
    "ricevuta_pagamento": ["ricevuta", "quietanza"],
    "bonifico": ["bonifico", "cro"],
    "pec_inviata": ["pec_inviata", "consegna", "accettazione"],
    "pec_ricevuta": ["pec_ricevuta", "ricevuta_pec", "postacert"],
    "convocazione": ["convocazione", "invito"],
    "verbale": ["verbale"],
    "accordo": ["accordo", "conciliazione"],
    "relazione": ["relazione"],
    "contratto": ["contratto", "scrittura"],
    "visura": ["visura", "camerale"],
    "allegato_generico": ["allegato", "all"],
}


def classify_document(filename: str, text: str | None = None) -> str:
    haystack = f"{filename} {text or ''}".lower().replace("-", "_")
    for category, keywords in CATEGORIES.items():
        if any(keyword in haystack for keyword in keywords):
            return category
    return "altro"


def basic_metadata(path: str | Path, original_filename: str | None = None) -> dict:
    file_path = Path(path)
    suffix = file_path.suffix.lower()
    return {
        "filename": file_path.name,
        "original_filename": original_filename or file_path.name,
        "file_path": str(file_path),
        "file_size": file_path.stat().st_size if file_path.exists() else 0,
        "mime_type": {
            ".pdf": "application/pdf",
            ".zip": "application/zip",
            ".eml": "message/rfc822",
            ".msg": "application/vnd.ms-outlook",
            ".jpg": "image/jpeg",
            ".jpeg": "image/jpeg",
            ".png": "image/png",
            ".tif": "image/tiff",
            ".tiff": "image/tiff",
        }.get(suffix, "application/octet-stream"),
    }
