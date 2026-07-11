-- Sprint 4 Task 002 - Regole economiche compensi mediatori e sedi operative
-- Safe migration for existing local PostgreSQL databases. It does not delete data.

CREATE TABLE IF NOT EXISTS economic_rules (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE CASCADE,
    rule_key VARCHAR(160) NOT NULL,
    rule_name VARCHAR(255) NOT NULL,
    rule_type VARCHAR(100) NOT NULL,
    percentage NUMERIC(7,2) NOT NULL DEFAULT 0,
    applies_to VARCHAR(120),
    mediation_outcome VARCHAR(120),
    calculation_base VARCHAR(120) DEFAULT 'netto_maturato',
    includes_startup_expenses BOOLEAN DEFAULT TRUE,
    includes_first_meeting_expenses BOOLEAN DEFAULT TRUE,
    includes_further_expenses BOOLEAN DEFAULT TRUE,
    applies_to_main_office BOOLEAN DEFAULT FALSE,
    applies_to_operational_office BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    valid_from DATE,
    valid_to DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS calculation_base VARCHAR(120) DEFAULT 'netto_maturato';
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS includes_startup_expenses BOOLEAN DEFAULT TRUE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS includes_first_meeting_expenses BOOLEAN DEFAULT TRUE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS includes_further_expenses BOOLEAN DEFAULT TRUE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS applies_to_main_office BOOLEAN DEFAULT FALSE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS applies_to_operational_office BOOLEAN DEFAULT FALSE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS notes TEXT;

CREATE INDEX IF NOT EXISTS idx_economic_rules_org_office ON economic_rules(organization_id, office_id, active);

INSERT INTO economic_rules (
    organization_id, office_id, rule_key, rule_name, rule_type, percentage,
    applies_to, mediation_outcome, active, valid_from
)
SELECT 1, o.id, 'mediator_first_meeting_net_percentage', 'Compenso mediatore sede principale', 'mediator_compensation', 50,
       'first_meeting_net', seed.outcome, TRUE, CURRENT_DATE
FROM offices o
CROSS JOIN (
    VALUES
    ('negativo_primo_incontro'),
    ('accordo_primo_incontro'),
    ('negativo_piu_incontri'),
    ('accordo_piu_incontri'),
    ('mancata_adesione'),
    ('rinuncia')
) AS seed(outcome)
WHERE o.organization_id = 1
  AND lower(o.name) = 'casarano'
  AND NOT EXISTS (
      SELECT 1 FROM economic_rules er
      WHERE er.organization_id = 1
        AND er.office_id = o.id
        AND er.rule_key = 'mediator_first_meeting_net_percentage'
        AND er.mediation_outcome = seed.outcome
  );

INSERT INTO economic_rules (
    organization_id, office_id, rule_key, rule_name, rule_type, percentage,
    applies_to, mediation_outcome, active, valid_from
)
SELECT 1, o.id, 'operational_office_percentage', 'Quota sede operativa', 'office_retrocession', 70,
       'practice_net', NULL, TRUE, CURRENT_DATE
FROM offices o
WHERE o.organization_id = 1
  AND lower(o.name) IN ('pachino', 'napoli')
  AND NOT EXISTS (
      SELECT 1 FROM economic_rules er
      WHERE er.organization_id = 1
        AND er.office_id = o.id
        AND er.rule_key = 'operational_office_percentage'
  );

INSERT INTO economic_rules (
    organization_id, office_id, rule_key, rule_name, rule_type, percentage,
    applies_to, mediation_outcome, active, valid_from
)
SELECT 1, o.id, 'main_office_percentage', 'Quota Mediacon su sede operativa', 'mediacon_retention', 30,
       'practice_net', NULL, TRUE, CURRENT_DATE
FROM offices o
WHERE o.organization_id = 1
  AND lower(o.name) IN ('pachino', 'napoli')
  AND NOT EXISTS (
      SELECT 1 FROM economic_rules er
      WHERE er.organization_id = 1
        AND er.office_id = o.id
        AND er.rule_key = 'main_office_percentage'
  );
