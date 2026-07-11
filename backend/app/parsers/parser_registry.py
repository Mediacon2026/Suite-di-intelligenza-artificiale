from __future__ import annotations

from .base_parser import BaseParser
from .mediation_parser import MediationParser


def get_parser(case_type: str, text: str, filename: str = ""):
    normalized = f"{case_type or ''} {filename or ''} {text or ''}".lower()
    if (
        (case_type or "").lower() == "mediation"
        or "istanza di mediazione" in normalized
        or "organismo di mediazione" in normalized
        or "d.lgs. 28/2010" in normalized
        or "d lgs 28/2010" in normalized
    ):
        return MediationParser()
    return BaseParser()
