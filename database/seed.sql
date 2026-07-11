INSERT INTO organizations (name, vat_number, fiscal_code)
VALUES ('Mediacon S.r.l.', NULL, NULL);

INSERT INTO roles (name, description) VALUES
('Amministratore', 'Accesso completo al sistema'),
('Responsabile Organismo', 'Gestione organismo mediazione'),
('Coordinamento', 'Coordinamento operativo'),
('Mediatore', 'Gestione pratiche assegnate'),
('Segreteria', 'Gestione operativa e documentale'),
('Gestore Crisi', 'Gestione OCC e crisi'),
('Orientatore', 'Gestione polo orientamento');

INSERT INTO offices (organization_id, name, address, city, province, email, pec, phone)
VALUES
(1, 'Casarano', 'Via Bruno Buozzi 10', 'Casarano', 'LE', 'info@mediacon.org', 'mediacon@arubapec.it', '0833513189'),
(1, 'Pachino', 'Via Fratelli Bandiera 82', 'Pachino', 'SR', NULL, NULL, NULL),
(1, 'Napoli', 'Via Enrico Pessina 66', 'Napoli', 'NA', NULL, NULL, NULL);

INSERT INTO users (organization_id, office_id, first_name, last_name, email, password_hash)
VALUES (1, 1, 'Admin', 'Mediacon', 'admin@mediacon.local', 'dev-password');

INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);

INSERT INTO contacts (
    organization_id, contact_type, first_name, last_name, company_name, email, pec,
    phone, mobile, city, province, notes, is_lawyer, is_mediator, is_trainer,
    is_student, is_debtor, is_creditor, is_client, is_company
)
VALUES
(1, 'persona', 'Gabriele', 'Petracca', NULL, 'gabriele.petracca@mediacon.local', NULL, NULL, NULL, 'Casarano', 'LE', 'Dott. Gabriele Petracca - demo Master Anagrafica', FALSE, TRUE, TRUE, FALSE, FALSE, FALSE, TRUE, FALSE),
(1, 'persona', 'Laura', 'Francioso', NULL, 'laura.francioso@mediacon.local', NULL, NULL, NULL, 'Casarano', 'LE', 'Dott.ssa Laura Francioso - demo Master Anagrafica', FALSE, TRUE, TRUE, FALSE, FALSE, FALSE, TRUE, FALSE),
(1, 'persona', 'Federico', 'Petracca', NULL, 'federico.petracca@mediacon.local', NULL, NULL, NULL, 'Casarano', 'LE', 'Avv. Federico Petracca - demo Master Anagrafica', TRUE, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE, FALSE),
(1, 'azienda', NULL, NULL, 'Mediacon S.r.l.', 'info@mediacon.org', 'mediacon@arubapec.it', '0833513189', NULL, 'Casarano', 'LE', 'Mediacon S.r.l. - demo Master Anagrafica', FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE, TRUE);

INSERT INTO mediators (contact_id, office_id, compensation_percentage)
VALUES (2, 1, 35.00);

INSERT INTO mediations (organization_id, office_id, title, internal_number, dgstat_number, year, quarter, subject, matter, dispute_value, mediation_type, status, mediator_id, sessions_count, notes)
VALUES
(1, 1, 'Mediazione De Santis / Rizzo', 'MED-2026-001', 'DG-001', 2026, 3, 'Controversia contrattuale', 'Contratti', 15000.00, 'volontaria', 'depositata', 1, 1, 'Pratica demo pronta per lavorazione'),
(1, 2, 'Mediazione Condominio Aurora', 'MED-2026-002', NULL, 2026, 3, 'Ripartizione spese condominiali', 'Condominio', 8200.00, 'obbligatoria', 'in corso', NULL, 0, 'Seconda pratica demo');

INSERT INTO trainings (organization_id, title, course_code, category, start_date, end_date, location, participants_count, status, notes)
VALUES
(1, 'Corso aggiornamento mediatori', 'FORM-2026-001', 'Mediazione', CURRENT_DATE + INTERVAL '14 days', CURRENT_DATE + INTERVAL '16 days', 'Casarano', 18, 'programmato', 'Seed corso Mediacon'),
(1, 'Laboratorio orientamento studenti', 'FORM-2026-002', 'Orientamento', CURRENT_DATE + INTERVAL '21 days', CURRENT_DATE + INTERVAL '21 days', 'Napoli', 25, 'bozza', 'Attivita formativa demo');

INSERT INTO orientations (organization_id, title, contact_id, service_type, appointment_date, operator_name, status, notes)
VALUES
(1, 'Colloquio orientamento Laura De Santis', 1, 'Orientamento professionale', CURRENT_DATE + INTERVAL '3 days', 'Segreteria Mediacon', 'aperto', 'Primo appuntamento demo');

INSERT INTO occ_cases (organization_id, title, contact_id, procedure_type, protocol_number, filing_date, manager_name, debt_amount, status, notes)
VALUES
(1, 'Pratica OCC De Santis', 1, 'Ristrutturazione debiti del consumatore', 'OCC-2026-001', CURRENT_DATE, 'Gestore demo', 42000.00, 'istruttoria', 'Pratica seed OCC');

INSERT INTO crisis_cases (organization_id, title, company_name, advisor_name, service_type, start_date, risk_level, status, notes)
VALUES
(1, 'Advisor Studio Mediterraneo', 'Studio Advisor Mediterraneo', 'Advisor Mediacon', 'Analisi crisi', CURRENT_DATE, 'medio', 'analisi', 'Pratica demo crisi impresa');

INSERT INTO documents (organization_id, title, area, reference_id, document_type, file_name, status, notes)
VALUES
(1, 'Istanza mediazione MED-2026-001', 'mediazioni', 1, 'istanza', 'istanza-med-2026-001.pdf', 'archiviato', 'Documento demo senza file allegato'),
(1, 'Programma corso mediatori', 'formazione', 1, 'programma', 'programma-corso-mediatori.pdf', 'bozza', 'Documento demo');

INSERT INTO payments (organization_id, title, area, reference_id, contact_id, amount, vat, total, method, due_date, status, reason)
VALUES
(1, 'Diritti segreteria MED-2026-001', 'mediazioni', 1, 1, 80.00, 17.60, 97.60, 'bonifico', CURRENT_DATE + INTERVAL '7 days', 'da_pagare', 'Diritti di avvio'),
(1, 'Quota corso aggiornamento mediatori', 'formazione', 1, NULL, 250.00, 55.00, 305.00, 'pos', CURRENT_DATE + INTERVAL '20 days', 'pagato', 'Quota iscrizione');

INSERT INTO deadlines (organization_id, title, area, reference_id, due_date, priority, assignee_name, status, notes)
VALUES
(1, 'Convocare primo incontro MED-2026-001', 'mediazioni', 1, CURRENT_DATE + INTERVAL '5 days', 'alta', 'Segreteria', 'aperta', 'Scadenza demo'),
(1, 'Confermare aula corso mediatori', 'formazione', 1, CURRENT_DATE + INTERVAL '10 days', 'media', 'Coordinamento', 'aperta', 'Scadenza demo');

INSERT INTO app_settings (organization_id, title, setting_key, setting_value, category, notes)
VALUES
(1, 'Nome piattaforma', 'app.name', 'Mediacon Hub ERP', 'generale', 'Configurazione base'),
(1, 'Email notifiche', 'notifications.email_from', 'info@mediacon.org', 'notifiche', 'Pronta per integrazioni future');

INSERT INTO integration_placeholders (organization_id, name, integration_type, enabled, notes)
VALUES
(1, 'AI Assistant', 'ai', FALSE, 'Struttura predisposta, non attiva in MVP'),
(1, 'PEC', 'pec', FALSE, 'Struttura predisposta, non attiva in MVP'),
(1, 'Firma digitale', 'signature', FALSE, 'Struttura predisposta, non attiva in MVP'),
(1, 'Generazione PDF avanzata', 'pdf', FALSE, 'Struttura predisposta, non attiva in MVP');
