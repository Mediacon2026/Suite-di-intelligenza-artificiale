from __future__ import annotations

from abc import ABC, abstractmethod
from typing import Any


class PreservationProvider(ABC):
    """Abstract contract for CAD preservation providers."""

    provider_code: str

    @abstractmethod
    def create_preservation_package(self, package_data: dict[str, Any]) -> dict[str, Any]:
        """Create a preservation package."""

    @abstractmethod
    def send_document(self, document_data: dict[str, Any]) -> dict[str, Any]:
        """Send a document or package to preservation."""

    @abstractmethod
    def get_preservation_status(self, preservation_id: str) -> dict[str, Any]:
        """Return preservation status from the provider."""

    @abstractmethod
    def get_receipt(self, preservation_id: str) -> dict[str, Any]:
        """Return preservation receipt metadata."""
