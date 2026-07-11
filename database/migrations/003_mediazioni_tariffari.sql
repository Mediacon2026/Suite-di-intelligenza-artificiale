-- Sprint 3 - Procedimenti di Mediazione + Tariffari Organismo
-- Safe migration for existing local PostgreSQL databases. It does not delete data.

ALTER TABLE organizations ADD COLUMN IF NOT EXISTS registration_number VARCHAR(80);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS email VARCHAR(255);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS pec VARCHAR(255);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS phone VARCHAR(50);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS address TEXT;
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS city VARCHAR(100);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS province VARCHAR(10);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS zip_code VARCHAR(20);
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS logo_url TEXT;
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS active BOOLEAN DEFAULT TRUE;
ALTER TABLE organizations ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS offices (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    office_type VARCHAR(80) DEFAULT 'sede',
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(10),
    zip_code VARCHAR(20),
    email VARCHAR(255),
    pec VARCHAR(255),
    phone VARCHAR(50),
    manager_name VARCHAR(160),
    retrocession_percentage NUMERIC(5,2) DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE offices ADD COLUMN IF NOT EXISTS office_type VARCHAR(80) DEFAULT 'sede';
ALTER TABLE offices ADD COLUMN IF NOT EXISTS zip_code VARCHAR(20);
ALTER TABLE offices ADD COLUMN IF NOT EXISTS manager_name VARCHAR(160);
ALTER TABLE offices ADD COLUMN IF NOT EXISTS retrocession_percentage NUMERIC(5,2) DEFAULT 0;
ALTER TABLE offices ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE offices ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS mediation_tariffs (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    tariff_type VARCHAR(40) NOT NULL DEFAULT 'DM_150_2023',
    valid_from DATE,
    valid_to DATE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mediation_tariff_rows (
    id SERIAL PRIMARY KEY,
    tariff_id INTEGER NOT NULL REFERENCES mediation_tariffs(id) ON DELETE CASCADE,
    value_min NUMERIC(14,2) NOT NULL DEFAULT 0,
    value_max NUMERIC(14,2),
    initial_expense NUMERIC(12,2) NOT NULL DEFAULT 0,
    first_meeting_negative NUMERIC(12,2) NOT NULL DEFAULT 0,
    first_meeting_agreement NUMERIC(12,2) NOT NULL DEFAULT 0,
    further_meetings_agreement NUMERIC(12,2) NOT NULL DEFAULT 0,
    further_meetings_negative NUMERIC(12,2) NOT NULL DEFAULT 0,
    is_mandatory BOOLEAN DEFAULT TRUE,
    vat_rate NUMERIC(5,2) DEFAULT 22,
    notes TEXT
);

CREATE TABLE IF NOT EXISTS mediations (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    internal_number VARCHAR(50),
    dgstat_number VARCHAR(50),
    year INTEGER,
    quarter INTEGER,
    deposit_date DATE DEFAULT CURRENT_DATE,
    first_meeting_date DATE,
    closing_date DATE,
    status VARCHAR(100) DEFAULT 'depositata',
    outcome VARCHAR(100),
    mediation_type VARCHAR(40) DEFAULT 'obbligatoria',
    matter VARCHAR(255),
    submatter VARCHAR(255),
    claim_value NUMERIC(14,2) DEFAULT 0,
    object TEXT,
    reasons TEXT,
    mediator_id INTEGER,
    tariff_id INTEGER REFERENCES mediation_tariffs(id) ON DELETE SET NULL,
    tariff_row_id INTEGER REFERENCES mediation_tariff_rows(id) ON DELETE SET NULL,
    calculated_initial_expense NUMERIC(12,2) DEFAULT 0,
    calculated_first_meeting_fee NUMERIC(12,2) DEFAULT 0,
    calculated_further_fee NUMERIC(12,2) DEFAULT 0,
    calculated_vat NUMERIC(12,2) DEFAULT 0,
    calculated_total NUMERIC(12,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE mediations ADD COLUMN IF NOT EXISTS organization_id INTEGER REFERENCES organizations(id) ON DELETE CASCADE;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS internal_number VARCHAR(50);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS dgstat_number VARCHAR(50);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS year INTEGER;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quarter INTEGER;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS deposit_date DATE DEFAULT CURRENT_DATE;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS first_meeting_date DATE;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS closing_date DATE;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS status VARCHAR(100) DEFAULT 'depositata';
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS outcome VARCHAR(100);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS mediation_type VARCHAR(40) DEFAULT 'obbligatoria';
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS matter VARCHAR(255);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS submatter VARCHAR(255);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS claim_value NUMERIC(14,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS object TEXT;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS reasons TEXT;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS mediator_id INTEGER;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS tariff_id INTEGER REFERENCES mediation_tariffs(id) ON DELETE SET NULL;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS tariff_row_id INTEGER REFERENCES mediation_tariff_rows(id) ON DELETE SET NULL;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS calculated_initial_expense NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS calculated_first_meeting_fee NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS calculated_further_fee NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS calculated_vat NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS calculated_total NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS title VARCHAR(255);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS subject TEXT;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS dispute_value NUMERIC(14,2) DEFAULT 0;
ALTER TABLE mediations ALTER COLUMN title DROP NOT NULL;
ALTER TABLE mediations ALTER COLUMN subject DROP NOT NULL;

CREATE TABLE IF NOT EXISTS mediation_parties (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    party_role VARCHAR(40) NOT NULL,
    legal_aid BOOLEAN DEFAULT FALSE,
    present BOOLEAN DEFAULT FALSE,
    notes TEXT
);

CREATE TABLE IF NOT EXISTS mediation_lawyers (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    party_id INTEGER REFERENCES mediation_parties(id) ON DELETE CASCADE,
    lawyer_contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    power_of_attorney BOOLEAN DEFAULT FALSE,
    notes TEXT
);

ALTER TABLE mediation_lawyers ADD COLUMN IF NOT EXISTS power_of_attorney BOOLEAN DEFAULT FALSE;

CREATE TABLE IF NOT EXISTS mediation_sessions (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    session_number INTEGER DEFAULT 1,
    session_date DATE,
    start_time TIME,
    end_time TIME,
    mode VARCHAR(40) DEFAULT 'presenza',
    location_or_link TEXT,
    outcome VARCHAR(100),
    notes TEXT
);

ALTER TABLE mediation_sessions ADD COLUMN IF NOT EXISTS session_number INTEGER DEFAULT 1;
ALTER TABLE mediation_sessions ADD COLUMN IF NOT EXISTS outcome VARCHAR(100);

UPDATE organizations
SET name = 'Mediacon S.r.l.',
    registration_number = '707',
    email = coalesce(email, 'info@mediacon.org'),
    pec = coalesce(pec, 'mediacon@arubapec.it'),
    phone = coalesce(phone, '0833513189'),
    active = TRUE,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;

INSERT INTO offices (organization_id, name, office_type, address, city, province, email, pec, phone, manager_name, retrocession_percentage, active)
SELECT 1, 'Casarano', 'principale', 'Via Bruno Buozzi 10', 'Casarano', 'LE', 'info@mediacon.org', 'mediacon@arubapec.it', '0833513189', 'Direzione Mediacon', 0, TRUE
WHERE NOT EXISTS (SELECT 1 FROM offices WHERE organization_id = 1 AND lower(name) = 'casarano');

INSERT INTO offices (organization_id, name, office_type, address, city, province, active)
SELECT 1, 'Pachino', 'secondaria', 'Via Fratelli Bandiera 82', 'Pachino', 'SR', TRUE
WHERE NOT EXISTS (SELECT 1 FROM offices WHERE organization_id = 1 AND lower(name) = 'pachino');

INSERT INTO offices (organization_id, name, office_type, address, city, province, active)
SELECT 1, 'Napoli', 'secondaria', 'Via Enrico Pessina 66', 'Napoli', 'NA', TRUE
WHERE NOT EXISTS (SELECT 1 FROM offices WHERE organization_id = 1 AND lower(name) = 'napoli');

INSERT INTO mediation_tariffs (organization_id, name, description, tariff_type, valid_from, active)
SELECT 1, 'DM 150/2023 Mediacon', 'Tariffario iniziale DM 150/2023 per Mediacon', 'DM_150_2023', DATE '2023-11-15', TRUE
WHERE NOT EXISTS (SELECT 1 FROM mediation_tariffs WHERE organization_id = 1 AND name = 'DM 150/2023 Mediacon');

INSERT INTO mediation_tariff_rows (
    tariff_id, value_min, value_max, initial_expense, first_meeting_negative,
    first_meeting_agreement, further_meetings_agreement, further_meetings_negative,
    is_mandatory, vat_rate, notes
)
SELECT t.id, rows.value_min, rows.value_max, rows.initial_expense, rows.first_meeting_negative,
       rows.first_meeting_agreement, rows.further_meetings_agreement, rows.further_meetings_negative,
       TRUE, 22, rows.notes
FROM mediation_tariffs t
CROSS JOIN (
    VALUES
    (0::numeric, 1000::numeric, 40::numeric, 80::numeric, 120::numeric, 80::numeric, 40::numeric, 'Fino a 1.000 euro'),
    (1000.01::numeric, 5000::numeric, 75::numeric, 160::numeric, 240::numeric, 160::numeric, 80::numeric, 'Da 1.000,01 a 5.000 euro'),
    (5000.01::numeric, 10000::numeric, 120::numeric, 260::numeric, 390::numeric, 260::numeric, 130::numeric, 'Da 5.000,01 a 10.000 euro'),
    (10000.01::numeric, 25000::numeric, 180::numeric, 360::numeric, 540::numeric, 360::numeric, 180::numeric, 'Da 10.000,01 a 25.000 euro'),
    (25000.01::numeric, 50000::numeric, 240::numeric, 500::numeric, 750::numeric, 500::numeric, 250::numeric, 'Da 25.000,01 a 50.000 euro'),
    (50000.01::numeric, NULL::numeric, 320::numeric, 700::numeric, 1050::numeric, 700::numeric, 350::numeric, 'Oltre 50.000 euro')
) AS rows(value_min, value_max, initial_expense, first_meeting_negative, first_meeting_agreement, further_meetings_agreement, further_meetings_negative, notes)
WHERE t.organization_id = 1
  AND t.name = 'DM 150/2023 Mediacon'
  AND NOT EXISTS (SELECT 1 FROM mediation_tariff_rows r WHERE r.tariff_id = t.id);

INSERT INTO mediations (
    organization_id, office_id, internal_number, dgstat_number, year, quarter, deposit_date,
    status, outcome, mediation_type, matter, submatter, claim_value, object, reasons,
    tariff_id, tariff_row_id, calculated_initial_expense, calculated_first_meeting_fee,
    calculated_further_fee, calculated_vat, calculated_total, notes, title, subject, dispute_value
)
SELECT 1, o.id, 'MED-2026-001', 'DG-2026-001', 2026, 3, CURRENT_DATE,
       'depositata', NULL, 'obbligatoria', 'Contratti', 'Fornitura servizi', 15000,
       'Pagamento corrispettivi contrattuali', 'Contestazione su saldo fatture',
       calc.tariff_id, calc.row_id, calc.initial_expense, calc.first_meeting_negative,
       0, calc.vat, calc.total, 'Mediazione demo Sprint 3',
       'MED-2026-001', 'Pagamento corrispettivi contrattuali', 15000
FROM offices o
CROSS JOIN LATERAL (
    SELECT t.id AS tariff_id, r.id AS row_id, r.initial_expense, r.first_meeting_negative,
           round((r.initial_expense + r.first_meeting_negative) * r.vat_rate / 100, 2) AS vat,
           round((r.initial_expense + r.first_meeting_negative) * (1 + r.vat_rate / 100), 2) AS total
    FROM mediation_tariffs t
    JOIN mediation_tariff_rows r ON r.tariff_id = t.id
    WHERE t.organization_id = 1 AND t.active = TRUE AND 15000 BETWEEN r.value_min AND coalesce(r.value_max, 999999999)
    ORDER BY t.valid_from DESC NULLS LAST, t.id DESC
    LIMIT 1
) calc
WHERE o.organization_id = 1 AND lower(o.name) = 'casarano'
  AND NOT EXISTS (SELECT 1 FROM mediations WHERE organization_id = 1 AND internal_number = 'MED-2026-001');

INSERT INTO mediations (
    organization_id, office_id, internal_number, dgstat_number, year, quarter, deposit_date,
    status, outcome, mediation_type, matter, submatter, claim_value, object, reasons,
    tariff_id, tariff_row_id, calculated_initial_expense, calculated_first_meeting_fee,
    calculated_further_fee, calculated_vat, calculated_total, notes, title, subject, dispute_value
)
SELECT 1, o.id, 'MED-2026-002', 'DG-2026-002', 2026, 3, CURRENT_DATE,
       'in corso', NULL, 'volontaria', 'Condominio', 'Spese condominiali', 8200,
       'Ripartizione spese condominiali', 'Richiesta definizione bonaria',
       calc.tariff_id, calc.row_id, calc.initial_expense, calc.first_meeting_negative,
       0, calc.vat, calc.total, 'Mediazione demo Sprint 3',
       'MED-2026-002', 'Ripartizione spese condominiali', 8200
FROM offices o
CROSS JOIN LATERAL (
    SELECT t.id AS tariff_id, r.id AS row_id, r.initial_expense, r.first_meeting_negative,
           round((r.initial_expense + r.first_meeting_negative) * r.vat_rate / 100, 2) AS vat,
           round((r.initial_expense + r.first_meeting_negative) * (1 + r.vat_rate / 100), 2) AS total
    FROM mediation_tariffs t
    JOIN mediation_tariff_rows r ON r.tariff_id = t.id
    WHERE t.organization_id = 1 AND t.active = TRUE AND 8200 BETWEEN r.value_min AND coalesce(r.value_max, 999999999)
    ORDER BY t.valid_from DESC NULLS LAST, t.id DESC
    LIMIT 1
) calc
WHERE o.organization_id = 1 AND lower(o.name) = 'napoli'
  AND NOT EXISTS (SELECT 1 FROM mediations WHERE organization_id = 1 AND internal_number = 'MED-2026-002');
