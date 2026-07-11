-- Sprint 4 - Configuration Center Core
-- Safe migration for existing local PostgreSQL databases. It does not delete data.

CREATE TABLE IF NOT EXISTS config_modules (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    module_key VARCHAR(100) NOT NULL,
    module_name VARCHAR(160) NOT NULL,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (organization_id, module_key)
);

CREATE TABLE IF NOT EXISTS config_numbering_rules (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    module_key VARCHAR(100) NOT NULL,
    format_pattern VARCHAR(120) NOT NULL,
    current_year INTEGER NOT NULL,
    current_sequence INTEGER NOT NULL DEFAULT 0,
    reset_policy VARCHAR(60) DEFAULT 'yearly',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS config_mediation_matters (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    ministerial_code VARCHAR(80),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS config_workflows (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    module_key VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS config_workflow_steps (
    id SERIAL PRIMARY KEY,
    workflow_id INTEGER NOT NULL REFERENCES config_workflows(id) ON DELETE CASCADE,
    step_order INTEGER NOT NULL DEFAULT 1,
    step_key VARCHAR(120) NOT NULL,
    step_name VARCHAR(255) NOT NULL,
    required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS config_roles (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    role_key VARCHAR(120) NOT NULL,
    role_name VARCHAR(160) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (organization_id, role_key)
);

CREATE TABLE IF NOT EXISTS config_permissions (
    id SERIAL PRIMARY KEY,
    permission_key VARCHAR(160) NOT NULL UNIQUE,
    permission_name VARCHAR(160) NOT NULL,
    module_key VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS config_role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL REFERENCES config_roles(id) ON DELETE CASCADE,
    permission_id INTEGER NOT NULL REFERENCES config_permissions(id) ON DELETE CASCADE,
    UNIQUE (role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS config_economic_parameters (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    parameter_key VARCHAR(160) NOT NULL,
    parameter_value VARCHAR(160) NOT NULL,
    valid_from DATE,
    valid_to DATE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER REFERENCES organizations(id) ON DELETE SET NULL,
    user_id INTEGER,
    entity_name VARCHAR(160) NOT NULL,
    entity_id INTEGER,
    action VARCHAR(80) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO config_modules (organization_id, module_key, module_name, enabled)
SELECT 1, seed.module_key, seed.module_name, TRUE
FROM (
    VALUES
    ('crm', 'CRM'),
    ('mediazioni', 'Mediazioni'),
    ('formazione', 'Formazione'),
    ('orientamento', 'Orientamento'),
    ('occ', 'OCC'),
    ('crisi_impresa', 'Crisi Impresa'),
    ('documenti', 'Documenti'),
    ('pagamenti', 'Pagamenti'),
    ('scadenze', 'Scadenze'),
    ('ai', 'AI')
) AS seed(module_key, module_name)
WHERE NOT EXISTS (SELECT 1 FROM config_modules cm WHERE cm.organization_id = 1 AND cm.module_key = seed.module_key);

INSERT INTO config_numbering_rules (organization_id, module_key, format_pattern, current_year, current_sequence, reset_policy, active)
SELECT 1, 'mediazioni', '{sequence}/2026', 2026, 0, 'yearly', TRUE
WHERE NOT EXISTS (SELECT 1 FROM config_numbering_rules WHERE organization_id = 1 AND module_key = 'mediazioni');

INSERT INTO config_mediation_matters (organization_id, name, ministerial_code, active)
SELECT 1, seed.name, seed.code, TRUE
FROM (
    VALUES
    ('Condominio', 'CON'),
    ('Diritti Reali', 'DIR_REALI'),
    ('Divisione', 'DIV'),
    ('Successioni ereditarie', 'SUC'),
    ('Patti di famiglia', 'PAT_FAM'),
    ('Locazione', 'LOC'),
    ('Comodato', 'COM'),
    ('Affitto di aziende', 'AFF_AZ'),
    ('Responsabilita medica e sanitaria', 'RESP_MED'),
    ('Diffamazione', 'DIF'),
    ('Contratti assicurativi', 'ASS'),
    ('Contratti bancari', 'BAN'),
    ('Contratti finanziari', 'FIN')
) AS seed(name, code)
WHERE NOT EXISTS (SELECT 1 FROM config_mediation_matters cmm WHERE cmm.organization_id = 1 AND lower(cmm.name) = lower(seed.name));

INSERT INTO config_workflows (organization_id, module_key, name, active)
SELECT 1, 'mediazioni', 'Mediazione Base', TRUE
WHERE NOT EXISTS (SELECT 1 FROM config_workflows WHERE organization_id = 1 AND module_key = 'mediazioni' AND name = 'Mediazione Base');

INSERT INTO config_workflow_steps (workflow_id, step_order, step_key, step_name, required)
SELECT wf.id, seed.step_order, seed.step_key, seed.step_name, TRUE
FROM config_workflows wf
CROSS JOIN (
    VALUES
    (1, 'deposito', 'Deposito'),
    (2, 'controllo_segreteria', 'Controllo Segreteria'),
    (3, 'nomina_mediatore', 'Nomina Mediatore'),
    (4, 'convocazione', 'Convocazione'),
    (5, 'primo_incontro', 'Primo Incontro'),
    (6, 'ulteriori_incontri', 'Ulteriori Incontri'),
    (7, 'chiusura', 'Chiusura'),
    (8, 'dgstat', 'DGStat'),
    (9, 'archiviazione', 'Archiviazione')
) AS seed(step_order, step_key, step_name)
WHERE wf.organization_id = 1
  AND wf.name = 'Mediazione Base'
  AND NOT EXISTS (SELECT 1 FROM config_workflow_steps s WHERE s.workflow_id = wf.id AND s.step_key = seed.step_key);

INSERT INTO config_roles (organization_id, role_key, role_name, description, active)
SELECT 1, seed.role_key, seed.role_name, seed.description, TRUE
FROM (
    VALUES
    ('admin', 'Amministratore', 'Accesso completo'),
    ('responsabile_organismo', 'Responsabile Organismo', 'Gestione organismo'),
    ('segreteria', 'Segreteria', 'Gestione operativa'),
    ('mediatore', 'Mediatore', 'Gestione pratiche assegnate'),
    ('docente', 'Docente', 'Formazione'),
    ('orientatore', 'Orientatore', 'Orientamento'),
    ('gestore_occ', 'Gestore OCC', 'Sovraindebitamento'),
    ('advisor', 'Advisor', 'Crisi impresa'),
    ('lettura', 'Utente Lettura', 'Consultazione')
) AS seed(role_key, role_name, description)
WHERE NOT EXISTS (SELECT 1 FROM config_roles cr WHERE cr.organization_id = 1 AND cr.role_key = seed.role_key);

INSERT INTO config_permissions (permission_key, permission_name, module_key, description)
SELECT seed.permission_key, seed.permission_name, seed.module_key, seed.description
FROM (
    VALUES
    ('crm.read', 'Legge CRM', 'crm', 'Consultazione contatti'),
    ('crm.write', 'Modifica CRM', 'crm', 'Creazione e modifica contatti'),
    ('mediazioni.read', 'Legge Mediazioni', 'mediazioni', 'Consultazione procedimenti'),
    ('mediazioni.write', 'Modifica Mediazioni', 'mediazioni', 'Creazione e modifica procedimenti'),
    ('config.read', 'Legge Configurazione', 'config', 'Consultazione configurazione'),
    ('config.write', 'Modifica Configurazione', 'config', 'Gestione Configuration Center')
) AS seed(permission_key, permission_name, module_key, description)
WHERE NOT EXISTS (SELECT 1 FROM config_permissions cp WHERE cp.permission_key = seed.permission_key);

INSERT INTO config_economic_parameters (organization_id, parameter_key, parameter_value, valid_from, active)
SELECT 1, seed.parameter_key, seed.parameter_value, CURRENT_DATE, TRUE
FROM (
    VALUES
    ('iva', '22'),
    ('retrocession_percentage_standard', '20'),
    ('mediator_compensation_standard', '35')
) AS seed(parameter_key, parameter_value)
WHERE NOT EXISTS (
    SELECT 1 FROM config_economic_parameters cep
    WHERE cep.organization_id = 1 AND cep.parameter_key = seed.parameter_key AND cep.active = TRUE
);

INSERT INTO config_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM config_roles r
CROSS JOIN config_permissions p
WHERE r.organization_id = 1
  AND r.role_key = 'admin'
ON CONFLICT (role_id, permission_id) DO NOTHING;

INSERT INTO config_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM config_roles r
JOIN config_permissions p ON p.permission_key IN ('crm.read', 'mediazioni.read', 'config.read')
WHERE r.organization_id = 1
  AND r.role_key = 'lettura'
ON CONFLICT (role_id, permission_id) DO NOTHING;
