from __future__ import annotations

from typing import Any

from .base_signature_provider import SignatureProvider
from .mock_signature_provider import MockSignatureProvider


class SignatureService:
    """Facade for signature providers."""

    def __init__(self, providers: dict[str, SignatureProvider] | None = None) -> None:
        self.providers = providers or {"MOCK_SIGNATURE": MockSignatureProvider()}

    def _provider(self, provider_code: str) -> SignatureProvider:
        provider = self.providers.get(provider_code.upper())
        if provider is None:
            raise ValueError(f"Unsupported signature provider: {provider_code}")
        return provider

    def create_signature_request(self, provider_code: str, request_data: dict[str, Any]) -> dict[str, Any]:
        return self._provider(provider_code).create_signature_request(request_data)

    def send_signature_request(self, provider_code: str, request_id: str) -> dict[str, Any]:
        return self._provider(provider_code).send_signature_request(request_id)

    def get_signature_status(self, provider_code: str, request_id: str) -> dict[str, Any]:
        return self._provider(provider_code).get_signature_status(request_id)

    def verify_signature(self, provider_code: str, document_id: str) -> dict[str, Any]:
        return self._provider(provider_code).verify_signature(document_id)

    def cancel_signature_request(self, provider_code: str, request_id: str) -> dict[str, Any]:
        return self._provider(provider_code).cancel_signature_request(request_id)
