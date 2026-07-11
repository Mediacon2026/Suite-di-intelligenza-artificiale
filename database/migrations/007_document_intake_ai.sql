-- Sprint 5 - Document Intake AI locale per Mediazioni
-- Safe migration for existing local PostgreSQL databases. It does not delete data.

CREATE TABLE IF NOT EXISTS document_intakes (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    intake_type VARCHAR(40) NOT NULL,
    source_filename VARCHAR(255) NOT NULL,
    extracted_json JSONB,
    status VARCHAR(80) DEFAULT 'extracted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mediation_documents (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    document_type VARCHAR(100) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);
