from __future__ import annotations

from typing import Any

from .base_provider import VideoConferenceProvider


class WebexProvider(VideoConferenceProvider):
    """Webex adapter placeholder.

    The real Webex API integration is intentionally not implemented yet.
    This class returns deterministic metadata that lets the rest of the
    platform be wired safely before provider credentials are introduced.
    """

    provider_code = "WEBEX"

    def create_meeting(self, meeting_data: dict[str, Any]) -> dict[str, Any]:
        meeting_id = str(meeting_data.get("webex_meeting_id") or "webex-pending")
        return {
            "provider": self.provider_code,
            "provider_meeting_id": meeting_id,
            "join_url": meeting_data.get("webex_join_url"),
            "status": "pending_provider_activation",
        }

    def update_meeting(self, meeting_id: str, meeting_data: dict[str, Any]) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "provider_meeting_id": meeting_id,
            "join_url": meeting_data.get("webex_join_url"),
            "status": "pending_provider_activation",
        }

    def cancel_meeting(self, meeting_id: str) -> dict[str, Any]:
        return {
            "provider": self.provider_code,
            "provider_meeting_id": meeting_id,
            "status": "cancel_pending_provider_activation",
        }

    def get_join_url(self, meeting_id: str) -> str | None:
        return None

    def sync_participants(self, meeting_id: str) -> list[dict[str, Any]]:
        return []
