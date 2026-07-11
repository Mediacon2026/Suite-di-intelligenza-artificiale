INSERT INTO organizations (name, vat_number) VALUES ('Mediacon S.r.l.', NULL);

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
