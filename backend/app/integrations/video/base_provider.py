from __future__ import annotations

from abc import ABC, abstractmethod
from typing import Any


class VideoConferenceProvider(ABC):
    """Abstract contract for video meeting providers."""

    provider_code: str

    @abstractmethod
    def create_meeting(self, meeting_data: dict[str, Any]) -> dict[str, Any]:
        """Create a remote meeting and return provider metadata."""

    @abstractmethod
    def update_meeting(self, meeting_id: str, meeting_data: dict[str, Any]) -> dict[str, Any]:
        """Update a remote meeting and return provider metadata."""

    @abstractmethod
    def cancel_meeting(self, meeting_id: str) -> dict[str, Any]:
        """Cancel a remote meeting."""

    @abstractmethod
    def get_join_url(self, meeting_id: str) -> str | None:
        """Return the join URL for a remote meeting."""

    @abstractmethod
    def sync_participants(self, meeting_id: str) -> list[dict[str, Any]]:
        """Synchronize participant presence data from the provider."""
