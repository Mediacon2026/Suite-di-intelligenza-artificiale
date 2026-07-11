from __future__ import annotations

import re


DOCUMENT_RULES = [
    ("dichiarazione_imparzialita", ["dichiarazione imparzialita", "dichiarazione di imparzialita", "imparzialità", "riservatezza"]),
    ("assunzione_incarico", ["assunzione incarico", "accettazione incarico", "incarico mediatore"]),
    ("lettera_convocazione", ["convocazione", "primo incontro", "lettera primo incontro"]),
    ("atto_adesione", ["atto di adesione", "modulo adesione", "aderisce alla procedura"]),
    ("istanza_mediazione", ["istanza di mediazione", "domanda di mediazione", "d.lgs. 28/2010", "organismo di mediazione"]),
    ("ricevuta_pagamento", ["ricevuta", "pagamento", "bonifico", "iban", "quietanza"]),
    ("procura", ["procura", "mandato alle liti", "delega"]),
    ("verbale", ["verbale", "verbale primo incontro", "verbale finale"]),
    ("accordo", ["accordo", "verbale di accordo", "accordo conciliativo"]),
]


def normalize(value: str | None) -> str:
    text = (value or "").lower()
    text = text.replace("_", " ").replace("-", " ")
    text = re.sub(r"\s+", " ", text)
    return text.strip()


def classify_document(text: str, filename: str = "") -> dict:
    haystack = f"{normalize(filename)} {normalize(text)}"
    best_type = "allegato_generico"
    best_score = 0.25
    for document_type, keywords in DOCUMENT_RULES:
        hits = sum(1 for keyword in keywords if normalize(keyword) in haystack)
        if hits:
            score = min(0.55 + (hits * 0.15), 0.95)
            if score > best_score:
                best_type = document_type
                best_score = score

    if "istanza pachino" in haystack or ("istanza" in haystack and "pachino" in haystack):
        return {"document_type": "istanza_mediazione", "confidence": 0.9}
    if "istanza napoli" in haystack or ("istanza" in haystack and "napoli" in haystack):
        return {"document_type": "istanza_mediazione", "confidence": 0.9}

    return {"document_type": best_type, "confidence": round(best_score, 2)}
