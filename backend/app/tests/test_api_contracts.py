import asyncio
import json

from backend.app.main import app


def asgi_request(method: str, path: str, payload: dict | None = None) -> tuple[int, dict]:
    body = json.dumps(payload or {}).encode("utf-8") if payload is not None else b""
    messages = []

    async def receive():
        return {"type": "http.request", "body": body, "more_body": False}

    async def send(message):
        messages.append(message)

    scope = {
        "type": "http",
        "asgi": {"version": "3.0"},
        "http_version": "1.1",
        "method": method,
        "scheme": "http",
        "path": path,
        "raw_path": path.encode("ascii"),
        "query_string": b"",
        "headers": [(b"content-type", b"application/json")],
        "client": ("testclient", 50000),
        "server": ("testserver", 80),
    }
    asyncio.run(app(scope, receive, send))
    status = next(message["status"] for message in messages if message["type"] == "http.response.start")
    raw_body = b"".join(message.get("body", b"") for message in messages if message["type"] == "http.response.body")
    return status, json.loads(raw_body.decode("utf-8") or "{}")


def test_health():
    status, body = asgi_request("GET", "/health")
    assert status == 200
    assert body["status"] == "ok"


def test_health_full():
    status, body = asgi_request("GET", "/health/full")
    assert status == 200
    assert body["status"] in {"ok", "degraded"}
    assert body["kernel"] == "online"
    assert body["parser_registry"] == "online"
    assert body["intake_engine"] == "online"
    assert body["version"] == "0.1.1-alpha-recovery"


def test_kernel_status():
    status, body = asgi_request("GET", "/kernel/status")
    assert status == 200
    assert body["kernel"] == "online"
    assert body["engines"]["case_engine"] == "ready"


def test_kernel_events_test():
    status, body = asgi_request("POST", "/kernel/events/test", {"event_type": "case.created", "case_id": 1})
    assert status == 200
    assert body["event"]["event_type"] == "case.created"
    assert body["result"]["handled"] is True


def test_kernel_events_invalid_input():
    status, body = asgi_request("POST", "/kernel/events/test", {"event_type": "", "case_id": 1})
    assert status == 400
    assert body["error_code"] == "VALIDATION_ERROR"
    assert body["details"]["field"] == "event_type"


def test_parser_classify():
    status, body = asgi_request("POST", "/parsers/classify", {"text": "Istanza di mediazione", "filename": "Istanza.pdf"})
    assert status == 200
    assert body["document_type"] == "istanza_mediazione"


def test_parser_classify_missing_fields_still_coherent():
    status, body = asgi_request("POST", "/parsers/classify", {})
    assert status == 200
    assert "document_type" in body
    assert "confidence" in body


def test_mediation_parser_test():
    status, body = asgi_request(
        "POST",
        "/parsers/mediation/test",
        {"text": "Istanza di Mediazione presso Sede Operativa di Napoli Via Enrico Pessina 66", "filename": "Istanza.pdf"},
    )
    assert status == 200
    assert body["module"] == "mediazione"
    assert body["case_type"] == "mediation"


def test_mediation_parser_missing_fields_still_coherent():
    status, body = asgi_request("POST", "/parsers/mediation/test", {})
    assert status == 200
    assert "missing_fields" in body
    assert "validation_errors" in body
