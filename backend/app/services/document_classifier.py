from pathlib import Path


DOCUMENT_KEYWORDS = {
    "istanza": ["istanza", "domanda", "mediazione"],
    "procura": ["procura", "mandato"],
    "documento_identita": ["identita", "identità", "carta", "documento"],
    "codice_fiscale": ["codice_fiscale", "codice fiscale", "cf", "tessera"],
    "ricevuta_pagamento": ["ricevuta", "pagamento", "bonifico", "pos"],
    "verbale": ["verbale"],
    "accordo": ["accordo"],
    "allegato": ["allegato", "all"],
}


def classify_document(filename: str) -> str:
    lower_name = filename.lower().replace("-", "_")
    for document_type, keywords in DOCUMENT_KEYWORDS.items():
        if any(keyword in lower_name for keyword in keywords):
            return document_type
    return "altro"


def file_metadata(path: str | Path, original_filename: str | None = None) -> dict:
    file_path = Path(path)
    return {
        "document_type": classify_document(original_filename or file_path.name),
        "filename": file_path.name,
        "file_path": str(file_path),
        "original_filename": original_filename or file_path.name,
        "file_size": file_path.stat().st_size if file_path.exists() else 0,
    }
