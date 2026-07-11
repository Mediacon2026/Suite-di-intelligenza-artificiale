-- Mediacon Hub ERP - PostgreSQL schema MVP

CREATE TABLE organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    vat_number VARCHAR(50),
    fiscal_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE offices (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(10),
    email VARCHAR(255),
    pec VARCHAR(255),
    phone VARCHAR(50),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    role_id INTEGER REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    contact_type VARCHAR(50) NOT NULL DEFAULT 'persona',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    company_name VARCHAR(255),
    fiscal_code VARCHAR(50),
    vat_number VARCHAR(50),
    email VARCHAR(255),
    pec VARCHAR(255),
    phone VARCHAR(50),
    mobile VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(10),
    zip_code VARCHAR(20),
    notes TEXT,
    is_lawyer BOOLEAN DEFAULT FALSE,
    is_mediator BOOLEAN DEFAULT FALSE,
    is_trainer BOOLEAN DEFAULT FALSE,
    is_student BOOLEAN DEFAULT FALSE,
    is_debtor BOOLEAN DEFAULT FALSE,
    is_creditor BOOLEAN DEFAULT FALSE,
    is_client BOOLEAN DEFAULT FALSE,
    is_company BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE mediators (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    active BOOLEAN DEFAULT TRUE,
    compensation_percentage NUMERIC(5,2)
);

CREATE TABLE mediations (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    internal_number VARCHAR(50),
    dgstat_number VARCHAR(50),
    year INTEGER DEFAULT EXTRACT(YEAR FROM CURRENT_DATE),
    quarter INTEGER DEFAULT EXTRACT(QUARTER FROM CURRENT_DATE),
    deposit_date DATE DEFAULT CURRENT_DATE,
    first_session_date DATE,
    closure_date DATE,
    subject TEXT,
    matter VARCHAR(255),
    dispute_value NUMERIC(14,2) DEFAULT 0,
    mediation_type VARCHAR(100),
    status VARCHAR(100) NOT NULL DEFAULT 'depositata',
    outcome VARCHAR(100),
    mediator_id INTEGER REFERENCES mediators(id) ON DELETE SET NULL,
    sessions_count INTEGER DEFAULT 0,
    suspended BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trainings (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    course_code VARCHAR(80),
    category VARCHAR(120),
    start_date DATE,
    end_date DATE,
    location VARCHAR(255),
    participants_count INTEGER DEFAULT 0,
    status VARCHAR(100) DEFAULT 'programmato',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orientations (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    service_type VARCHAR(120),
    appointment_date DATE,
    operator_name VARCHAR(160),
    status VARCHAR(100) DEFAULT 'aperto',
    outcome TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE occ_cases (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    procedure_type VARCHAR(160),
    protocol_number VARCHAR(100),
    filing_date DATE,
    manager_name VARCHAR(160),
    debt_amount NUMERIC(14,2) DEFAULT 0,
    status VARCHAR(100) DEFAULT 'istruttoria',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE crisis_cases (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255),
    advisor_name VARCHAR(160),
    service_type VARCHAR(160),
    start_date DATE,
    risk_level VARCHAR(60),
    status VARCHAR(100) DEFAULT 'analisi',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    area VARCHAR(100) NOT NULL,
    reference_id INTEGER,
    document_type VARCHAR(100),
    file_name VARCHAR(255),
    file_path TEXT,
    status VARCHAR(100) DEFAULT 'bozza',
    uploaded_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    area VARCHAR(100) NOT NULL,
    reference_id INTEGER,
    contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    vat NUMERIC(12,2) DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    method VARCHAR(100),
    payment_date DATE,
    due_date DATE,
    status VARCHAR(100) DEFAULT 'da_pagare',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE deadlines (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    area VARCHAR(100),
    reference_id INTEGER,
    due_date DATE NOT NULL,
    priority VARCHAR(60) DEFAULT 'media',
    assignee_name VARCHAR(160),
    status VARCHAR(100) DEFAULT 'aperta',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE app_settings (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    setting_key VARCHAR(160) NOT NULL,
    setting_value TEXT,
    category VARCHAR(100) DEFAULT 'generale',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (organization_id, setting_key)
);

CREATE TABLE integration_placeholders (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(160) NOT NULL,
    integration_type VARCHAR(80) NOT NULL,
    enabled BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_contacts_search ON contacts USING gin (
    to_tsvector('simple', coalesce(first_name,'') || ' ' || coalesce(last_name,'') || ' ' || coalesce(company_name,'') || ' ' || coalesce(email,'') || ' ' || coalesce(pec,'') || ' ' || coalesce(fiscal_code,'') || ' ' || coalesce(vat_number,''))
);
CREATE INDEX idx_mediations_status ON mediations(status);
CREATE INDEX idx_deadlines_due_date ON deadlines(due_date);

-- Sprint 3 incremental schema lives in database/migrations/003_mediazioni_tariffari.sql.
