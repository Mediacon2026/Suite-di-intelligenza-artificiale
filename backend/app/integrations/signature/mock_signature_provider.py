from __future__ import annotations

from typing import Any

from .base_signature_provider import SignatureProvider


class MockSignatureProvider(SignatureProvider):
    """Non-operational signature provider used before real integrations."""

    provider_code = "MOCK_SIGNATURE"

    def create_signature_request(self, request_data: dict[str, Any]) -> dict[str, Any]:
        request_id = str(request_data.get("request_id") or "signature-request-pending")
        return {
            "provider": self.provider_code,
            "provider_request_id": request_id,
            "status": "draft",
        }

    def send_signature_request(self, request_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "provider_request_id": request_id,
            "status": "sent_placeholder",
        }

    def get_signature_status(self, request_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "provider_request_id": request_id,
            "status": "pending_provider_activation",
        }

    def verify_signature(self, document_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "document_id": document_id,
            "signature_valid": None,
            "integrity_valid": None,
            "status": "verification_not_available",
        }

    def cancel_signature_request(self, request_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "provider_request_id": request_id,
            "status": "cancelled_placeholder",
        }
