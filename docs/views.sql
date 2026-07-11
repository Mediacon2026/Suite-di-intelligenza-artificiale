-- Views iniziali
CREATE VIEW v_mediations_summary AS
SELECT m.id, m.internal_number, m.year, m.quarter, o.name AS office_name,
       m.deposit_date, m.status, m.outcome, m.subject
FROM mediations m
JOIN offices o ON o.id = m.office_id;
