from __future__ import annotations

from abc import ABC, abstractmethod
from typing import Any


class SignatureProvider(ABC):
    """Abstract contract for document signature providers."""

    provider_code: str

    @abstractmethod
    def create_signature_request(self, request_data: dict[str, Any]) -> dict[str, Any]:
        """Create a signature request for one or more documents."""

    @abstractmethod
    def send_signature_request(self, request_id: str) -> dict[str, Any]:
        """Send a signature request to configured recipients."""

    @abstractmethod
    def get_signature_status(self, request_id: str) -> dict[str, Any]:
        """Return provider status for a signature request."""

    @abstractmethod
    def verify_signature(self, document_id: str) -> dict[str, Any]:
        """Verify signature validity and document integrity."""

    @abstractmethod
    def cancel_signature_request(self, request_id: str) -> dict[str, Any]:
        """Cancel a pending signature request."""
