def confidence_label(score: float | int | None) -> str:
    value = float(score or 0)
    if value >= 0.85:
        return "alta"
    if value >= 0.60:
        return "media"
    if value >= 0.35:
        return "bassa"
    return "da_verificare"
