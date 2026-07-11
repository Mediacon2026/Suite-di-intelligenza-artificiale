def timeline_event(event_type: str, title: str, description: str | None = None) -> dict:
    return {"event_type": event_type, "title": title, "description": description}
