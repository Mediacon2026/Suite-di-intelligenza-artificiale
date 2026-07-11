from pathlib import Path

from .pdf_parser import extract_pdf_text, parse_mediation_application


def extract_mediation_data_from_pdf(path: str | Path) -> dict:
    text = extract_pdf_text(path)
    return parse_mediation_application(text)
