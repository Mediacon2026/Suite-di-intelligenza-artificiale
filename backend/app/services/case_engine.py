from pathlib import Path

from .document_classifier import file_metadata
from .pdf_parser import extract_case_json, extract_pdf_text


def analyze_case_document(path: str | Path, original_filename: str | None = None) -> dict:
    metadata = file_metadata(path, original_filename)
    metadata["classification_status"] = "classified"
    metadata["mime_type"] = "application/pdf" if metadata["filename"].lower().endswith(".pdf") else "application/octet-stream"
    metadata["extracted_text"] = None
    metadata["extracted_json"] = {}

    if metadata["filename"].lower().endswith(".pdf"):
        try:
            text = extract_pdf_text(path)
            metadata["extracted_text"] = text
            metadata["extracted_json"] = extract_case_json(text)
        except Exception as exc:
            metadata["classification_status"] = "classified_pdf_parse_failed"
            metadata["extracted_json"] = {"error": str(exc)}

    return metadata
