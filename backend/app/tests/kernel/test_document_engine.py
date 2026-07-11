from backend.app.kernel import document_engine


def test_generate_document_placeholder():
    result = document_engine.generate_document("convocazione", {"case_id": 1})
    assert result["template_key"] == "convocazione"
    assert result["status"] == "generated_placeholder"


def test_create_document_version():
    result = document_engine.create_document_version(10, {"version": 2})
    assert result["document_id"] == 10
    assert result["version"] == 2


def test_calculate_hash():
    result = document_engine.calculate_document_hash("nexus")
    assert result["hash_algorithm"] == "sha256"
    assert len(result["hash"]) == 64


def test_mark_document_for_signature():
    result = document_engine.mark_document_for_signature(10, [{"role": "mediatore"}])
    assert result["document_id"] == 10
    assert result["status"] == "to_signature"
    assert result["recipients"][0]["role"] == "mediatore"


def test_mark_document_for_preservation():
    result = document_engine.mark_document_for_preservation(10, "mock")
    assert result["document_id"] == 10
    assert result["status"] == "to_preservation"
    assert result["provider"] == "mock"
