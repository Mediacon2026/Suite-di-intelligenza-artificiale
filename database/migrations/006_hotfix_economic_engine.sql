-- Hotfix Economic Engine 001
-- Corrects Mediacon economic rules: fixed percentages, variable matured net base.

ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS calculation_base VARCHAR(120) DEFAULT 'netto_maturato';
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS includes_startup_expenses BOOLEAN DEFAULT TRUE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS includes_first_meeting_expenses BOOLEAN DEFAULT TRUE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS includes_further_expenses BOOLEAN DEFAULT TRUE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS applies_to_main_office BOOLEAN DEFAULT FALSE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS applies_to_operational_office BOOLEAN DEFAULT FALSE;
ALTER TABLE economic_rules ADD COLUMN IF NOT EXISTS notes TEXT;

ALTER TABLE mediations ADD COLUMN IF NOT EXISTS netto_maturato_calcolato NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quota_mediatore_calcolata NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quota_sede_operativa_calcolata NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quota_mediacon_calcolata NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS regola_economica_applicata_id INTEGER REFERENCES economic_rules(id) ON DELETE SET NULL;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS data_calcolo TIMESTAMP;

UPDATE economic_rules
SET active = FALSE,
    updated_at = CURRENT_TIMESTAMP,
    notes = coalesce(notes, '') || ' | Disattivata da hotfix economic engine: percentuale non distinta per esito.'
WHERE organization_id = 1
  AND rule_key = 'mediator_first_meeting_net_percentage'
  AND mediation_outcome IS NOT NULL;

INSERT INTO economic_rules (
    organization_id, office_id, rule_key, rule_name, rule_type, percentage,
    applies_to, mediation_outcome, calculation_base,
    includes_startup_expenses, includes_first_meeting_expenses, includes_further_expenses,
    applies_to_main_office, applies_to_operational_office, active, valid_from, notes
)
SELECT 1, o.id, 'mediator_net_matured_percentage', 'Compenso mediatore sede principale', 'mediator_compensation', 50,
       'netto_maturato', NULL, 'netto_maturato',
       TRUE, TRUE, TRUE, TRUE, FALSE, TRUE, CURRENT_DATE,
       'Il mediatore percepisce il 50% del netto maturato nella pratica. La percentuale non cambia in base all’esito.'
FROM offices o
WHERE o.organization_id = 1
  AND lower(o.name) = 'casarano'
  AND NOT EXISTS (
      SELECT 1 FROM economic_rules er
      WHERE er.organization_id = 1
        AND er.office_id = o.id
        AND er.rule_key = 'mediator_net_matured_percentage'
  );

UPDATE economic_rules
SET calculation_base = 'netto_maturato',
    includes_startup_expenses = TRUE,
    includes_first_meeting_expenses = TRUE,
    includes_further_expenses = TRUE,
    applies_to_main_office = FALSE,
    applies_to_operational_office = TRUE,
    applies_to = 'netto_maturato',
    notes = coalesce(notes, '') || ' | Base corretta: netto maturato.'
WHERE organization_id = 1
  AND rule_key IN ('operational_office_percentage', 'main_office_percentage');
