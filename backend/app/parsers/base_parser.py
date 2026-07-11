from __future__ import annotations


class BaseParser:
    module = ""
    case_type = ""

    def parse(self, text: str, filename: str = "") -> dict:
        return {
            "module": self.module,
            "case_type": self.case_type,
            "document_type": "",
            "confidence_score": 0,
            "extracted_fields": {},
            "confidence_by_field": {},
            "missing_fields": [],
            "suggested_actions": [],
            "validation_errors": [],
        }
