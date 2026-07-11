-- Sprint 2 - Master Anagrafica CRM
-- Safe migration for existing local PostgreSQL databases. It does not delete data.

CREATE TABLE IF NOT EXISTS organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    vat_number VARCHAR(50),
    fiscal_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO organizations (id, name)
VALUES (1, 'Mediacon S.r.l.')
ON CONFLICT (id) DO NOTHING;

CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER REFERENCES organizations(id) ON DELETE CASCADE,
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

ALTER TABLE contacts ADD COLUMN IF NOT EXISTS organization_id INTEGER REFERENCES organizations(id) ON DELETE CASCADE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS mobile VARCHAR(50);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS zip_code VARCHAR(20);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_lawyer BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_mediator BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_trainer BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_student BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_debtor BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_creditor BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_client BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_company BOOLEAN DEFAULT FALSE;

INSERT INTO contacts (organization_id, contact_type, first_name, last_name, email, city, province, notes, is_mediator, is_trainer, is_client)
SELECT 1, 'persona', 'Gabriele', 'Petracca', 'gabriele.petracca@mediacon.local', 'Casarano', 'LE', 'Contatto demo Master Anagrafica', TRUE, TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE lower(coalesce(first_name, '')) = 'gabriele' AND lower(coalesce(last_name, '')) = 'petracca');

INSERT INTO contacts (organization_id, contact_type, first_name, last_name, email, city, province, notes, is_mediator, is_trainer, is_client)
SELECT 1, 'persona', 'Laura', 'Francioso', 'laura.francioso@mediacon.local', 'Casarano', 'LE', 'Contatto demo Master Anagrafica', TRUE, TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE lower(coalesce(first_name, '')) = 'laura' AND lower(coalesce(last_name, '')) = 'francioso');

INSERT INTO contacts (organization_id, contact_type, first_name, last_name, email, city, province, notes, is_lawyer, is_client)
SELECT 1, 'persona', 'Federico', 'Petracca', 'federico.petracca@mediacon.local', 'Casarano', 'LE', 'Contatto demo Master Anagrafica', TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE lower(coalesce(first_name, '')) = 'federico' AND lower(coalesce(last_name, '')) = 'petracca');

INSERT INTO contacts (organization_id, contact_type, company_name, email, pec, phone, city, province, notes, is_client, is_company)
SELECT 1, 'azienda', 'Mediacon S.r.l.', 'info@mediacon.org', 'mediacon@arubapec.it', '0833513189', 'Casarano', 'LE', 'Societa demo Master Anagrafica', TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM contacts WHERE lower(coalesce(company_name, '')) = 'mediacon s.r.l.');
