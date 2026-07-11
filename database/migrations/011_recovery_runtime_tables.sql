-- RECOVERY-001: allineamento non distruttivo delle tabelle create in precedenza
-- soltanto dal bootstrap runtime di backend/app/main.py.

BEGIN;

CREATE TABLE IF NOT EXISTS intake_sessions (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    user_id INTEGER,
    status VARCHAR(80) DEFAULT 'in_review',
    review_status VARCHAR(80) DEFAULT 'pending',
    extracted_json JSONB DEFAULT '{}'::jsonb,
    parser_results JSONB DEFAULT '[]'::jsonb,
    created_case_id INTEGER REFERENCES cases(id) ON DELETE SET NULL,
    created_mediation_id INTEGER REFERENCES mediations(id) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS intake_session_documents (
    id SERIAL PRIMARY KEY,
    intake_session_id INTEGER NOT NULL REFERENCES intake_sessions(id) ON DELETE CASCADE,
    document_type VARCHAR(120),
    module VARCHAR(80),
    confidence NUMERIC(5,2) DEFAULT 0,
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    mime_type VARCHAR(160),
    parser_result JSONB DEFAULT '{}'::jsonb,
    extracted_json JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mediator_assignments (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    mediator_contact_id INTEGER,
    mediator_name VARCHAR(255),
    status VARCHAR(80) NOT NULL DEFAULT 'draft',
    assignment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acceptance_deadline TIMESTAMP,
    email_sent_at TIMESTAMP,
    accepted_at TIMESTAMP,
    refused_at TIMESTAMP,
    reminder_sent_at TIMESTAMP,
    signature_link TEXT,
    generated_documents JSONB NOT NULL DEFAULT '[]'::jsonb,
    signed_documents JSONB NOT NULL DEFAULT '[]'::jsonb,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS email_messages (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER REFERENCES mediations(id) ON DELETE CASCADE,
    assignment_id INTEGER REFERENCES mediator_assignments(id) ON DELETE SET NULL,
    recipient_email VARCHAR(255),
    subject TEXT,
    body_text TEXT,
    status VARCHAR(80) NOT NULL DEFAULT 'draft',
    sent_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_intake_sessions_status ON intake_sessions(status, review_status);
CREATE INDEX IF NOT EXISTS idx_intake_session_documents_session ON intake_session_documents(intake_session_id);
CREATE INDEX IF NOT EXISTS idx_mediator_assignments_mediation ON mediator_assignments(mediation_id);
CREATE INDEX IF NOT EXISTS idx_email_messages_mediation ON email_messages(mediation_id);

COMMIT;
