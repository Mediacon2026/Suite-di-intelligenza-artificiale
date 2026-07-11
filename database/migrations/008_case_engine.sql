-- Sprint 6 - Case Engine + Fascicolo digitale universale
-- Safe migration for existing local PostgreSQL databases. It does not delete data.

CREATE TABLE IF NOT EXISTS cases (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    case_type VARCHAR(40) NOT NULL,
    case_number VARCHAR(80),
    title VARCHAR(255) NOT NULL,
    status VARCHAR(100) DEFAULT 'aperto',
    priority VARCHAR(60) DEFAULT 'media',
    opened_at DATE,
    closed_at DATE,
    related_entity_id INTEGER,
    related_entity_type VARCHAR(80),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_documents (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    document_type VARCHAR(100),
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    original_filename VARCHAR(255),
    file_size BIGINT,
    mime_type VARCHAR(160),
    classification_status VARCHAR(80),
    extracted_text TEXT,
    extracted_json JSONB,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_timeline (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by VARCHAR(160),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_tasks (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to VARCHAR(160),
    due_date DATE,
    status VARCHAR(100) DEFAULT 'aperta',
    priority VARCHAR(60) DEFAULT 'media',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_deadlines (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    deadline_date DATE NOT NULL,
    deadline_type VARCHAR(100),
    completed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_contacts (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    role VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
