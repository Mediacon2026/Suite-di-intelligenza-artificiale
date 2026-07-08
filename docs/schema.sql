-- Mediacon Hub ERP - PostgreSQL schema MVP

CREATE TABLE organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    vat_number VARCHAR(50),
    fiscal_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE offices (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    name VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(10),
    email VARCHAR(255),
    pec VARCHAR(255),
    phone VARCHAR(50),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    office_id INTEGER REFERENCES offices(id),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    role_id INTEGER REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    contact_type VARCHAR(50) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    company_name VARCHAR(255),
    fiscal_code VARCHAR(50),
    vat_number VARCHAR(50),
    email VARCHAR(255),
    pec VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(10),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contact_roles (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    role_name VARCHAR(100) NOT NULL
);

CREATE TABLE mediators (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id),
    office_id INTEGER REFERENCES offices(id),
    active BOOLEAN DEFAULT TRUE,
    compensation_percentage NUMERIC(5,2)
);

CREATE TABLE mediations (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    office_id INTEGER NOT NULL REFERENCES offices(id),
    internal_number VARCHAR(50) NOT NULL,
    dgstat_number VARCHAR(50),
    year INTEGER NOT NULL,
    quarter INTEGER NOT NULL,
    deposit_date DATE NOT NULL,
    first_session_date DATE,
    closure_date DATE,
    duration_days INTEGER,
    subject TEXT NOT NULL,
    matter VARCHAR(255),
    dispute_value NUMERIC(14,2),
    mediation_type VARCHAR(100),
    status VARCHAR(100) NOT NULL DEFAULT 'depositata',
    outcome VARCHAR(100),
    mediator_id INTEGER REFERENCES mediators(id),
    sessions_count INTEGER DEFAULT 0,
    suspended BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (organization_id, internal_number)
);

CREATE TABLE mediation_parties (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    contact_id INTEGER NOT NULL REFERENCES contacts(id),
    party_role VARCHAR(50) NOT NULL,
    present BOOLEAN DEFAULT FALSE,
    legal_aid BOOLEAN DEFAULT FALSE,
    notes TEXT
);

CREATE TABLE mediation_lawyers (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    party_id INTEGER NOT NULL REFERENCES mediation_parties(id) ON DELETE CASCADE,
    lawyer_contact_id INTEGER NOT NULL REFERENCES contacts(id),
    delegation_present BOOLEAN DEFAULT FALSE,
    notes TEXT
);

CREATE TABLE mediation_sessions (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    session_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    mode VARCHAR(50),
    location_or_link TEXT,
    result VARCHAR(100),
    notes TEXT
);

CREATE TABLE mediation_fees (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    fee_type VARCHAR(100) NOT NULL,
    taxable_amount NUMERIC(12,2) DEFAULT 0,
    vat_amount NUMERIC(12,2) DEFAULT 0,
    living_expenses NUMERIC(12,2) DEFAULT 0,
    total_amount NUMERIC(12,2) DEFAULT 0,
    paid BOOLEAN DEFAULT FALSE,
    payment_date DATE
);

CREATE TABLE documents (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    area VARCHAR(100) NOT NULL,
    reference_id INTEGER,
    document_type VARCHAR(100),
    file_name VARCHAR(255) NOT NULL,
    file_path TEXT,
    uploaded_by INTEGER REFERENCES users(id),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    area VARCHAR(100) NOT NULL,
    reference_id INTEGER,
    contact_id INTEGER REFERENCES contacts(id),
    amount NUMERIC(12,2) NOT NULL,
    vat NUMERIC(12,2) DEFAULT 0,
    total NUMERIC(12,2) NOT NULL,
    method VARCHAR(100),
    payment_date DATE,
    status VARCHAR(100) DEFAULT 'da_pagare',
    reason TEXT
);

CREATE TABLE dgstat_reports (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id),
    office_id INTEGER REFERENCES offices(id),
    year INTEGER NOT NULL,
    quarter INTEGER NOT NULL,
    deposited_count INTEGER DEFAULT 0,
    closed_count INTEGER DEFAULT 0,
    active_count INTEGER DEFAULT 0,
    suspended_count INTEGER DEFAULT 0,
    positive_count INTEGER DEFAULT 0,
    negative_count INTEGER DEFAULT 0,
    no_show_count INTEGER DEFAULT 0,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
