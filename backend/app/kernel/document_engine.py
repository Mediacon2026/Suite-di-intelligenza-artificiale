from __future__ import annotations

import hashlib
from datetime import datetime, timezone
from typing import Any


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def generate_document(template_key: str, data: dict[str, Any] | None = None) -> dict[str, Any]:
    return {
        "template_key": template_key,
        "status": "generated_placeholder",
        "data": data or {},
        "generated_at": _now(),
    }


def render_template(template_content: str, data: dict[str, Any] | None = None) -> dict[str, Any]:
    rendered = template_content
    for key, value in (data or {}).items():
        rendered = rendered.replace("{{" + key + "}}", str(value))
    return {"rendered": rendered, "status": "rendered"}


def create_document_version(document_id: int, version_data: dict[str, Any] | None = None) -> dict[str, Any]:
    return {
        "document_id": document_id,
        "version": (version_data or {}).get("version", 1),
        "status": "version_created",
        "created_at": _now(),
    }


def calculate_document_hash(content: bytes | str, algorithm: str = "sha256") -> dict[str, Any]:
    raw = content.encode("utf-8") if isinstance(content, str) else content
    digest = hashlib.new(algorithm)
    digest.update(raw)
    return {"hash": digest.hexdigest(), "hash_algorithm": algorithm}


def mark_document_for_signature(document_id: int, recipients: list[dict[str, Any]] | None = None) -> dict[str, Any]:
    return {
        "document_id": document_id,
        "status": "to_signature",
        "recipients": recipients or [],
        "marked_at": _now(),
    }


def mark_document_for_preservation(document_id: int, provider: str | None = None) -> dict[str, Any]:
    return {
        "document_id": document_id,
        "status": "to_preservation",
        "provider": provider or "future_provider",
        "marked_at": _now(),
    }
