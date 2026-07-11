from __future__ import annotations

from typing import Any

from .base_preservation_provider import PreservationProvider
from .mock_preservation_provider import MockPreservationProvider


class PreservationService:
    """Facade for CAD preservation providers."""

    def __init__(self, providers: dict[str, PreservationProvider] | None = None) -> None:
        self.providers = providers or {"MOCK_PRESERVATION": MockPreservationProvider()}

    def _provider(self, provider_code: str) -> PreservationProvider:
        provider = self.providers.get(provider_code.upper())
        if provider is None:
            raise ValueError(f"Unsupported preservation provider: {provider_code}")
        return provider

    def create_preservation_package(self, provider_code: str, package_data: dict[str, Any]) -> dict[str, Any]:
        return self._provider(provider_code).create_preservation_package(package_data)

    def send_document(self, provider_code: str, document_data: dict[str, Any]) -> dict[str, Any]:
        return self._provider(provider_code).send_document(document_data)

    def get_preservation_status(self, provider_code: str, preservation_id: str) -> dict[str, Any]:
        return self._provider(provider_code).get_preservation_status(preservation_id)

    def get_receipt(self, provider_code: str, preservation_id: str) -> dict[str, Any]:
        return self._provider(provider_code).get_receipt(preservation_id)
