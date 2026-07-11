from __future__ import annotations

from typing import Any

from .base_preservation_provider import PreservationProvider


class MockPreservationProvider(PreservationProvider):
    """Non-operational preservation provider used before real integrations."""

    provider_code = "MOCK_PRESERVATION"

    def create_preservation_package(self, package_data: dict[str, Any]) -> dict[str, Any]:
        package_id = str(package_data.get("package_id") or "preservation-package-pending")
        return {
            "provider": self.provider_code,
            "provider_package_id": package_id,
            "status": "draft",
        }

    def send_document(self, document_data: dict[str, Any]) -> dict[str, Any]:
        document_id = str(document_data.get("document_id") or "document-pending")
        return {
            "provider": self.provider_code,
            "document_id": document_id,
            "status": "sent_placeholder",
        }

    def get_preservation_status(self, preservation_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "preservation_id": preservation_id,
            "status": "pending_provider_activation",
        }

    def get_receipt(self, preservation_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "preservation_id": preservation_id,
            "receipt": None,
            "status": "receipt_not_available",
        }
