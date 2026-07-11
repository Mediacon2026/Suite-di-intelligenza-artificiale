from __future__ import annotations

from typing import Any

from .base_provider import VideoConferenceProvider
from .webex_provider import WebexProvider


class MeetingService:
    """Facade for video conference providers."""

    def __init__(self, providers: dict[str, VideoConferenceProvider] | None = None) -> None:
        self.providers = providers or {"WEBEX": WebexProvider()}

    def _provider(self, provider_code: str) -> VideoConferenceProvider:
        provider = self.providers.get(provider_code.upper())
        if provider is None:
            raise ValueError(f"Unsupported video conference provider: {provider_code}")
        return provider

    def create_meeting(self, provider_code: str, meeting_data: dict[str, Any]) -> dict[str, Any]:
        return self._provider(provider_code).create_meeting(meeting_data)

    def update_meeting(self, provider_code: str, meeting_id: str, meeting_data: dict[str, Any]) -> dict[str, Any]:
        return self._provider(provider_code).update_meeting(meeting_id, meeting_data)

    def cancel_meeting(self, provider_code: str, meeting_id: str) -> dict[str, Any]:
        return self._provider(provider_code).cancel_meeting(meeting_id)

    def get_join_url(self, provider_code: str, meeting_id: str) -> str | None:
        return self._provider(provider_code).get_join_url(meeting_id)

    def sync_participants(self, provider_code: str, meeting_id: str) -> list[dict[str, Any]]:
        return self._provider(provider_code).sync_participants(meeting_id)
