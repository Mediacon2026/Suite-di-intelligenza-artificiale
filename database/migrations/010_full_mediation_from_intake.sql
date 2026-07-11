CREATE TABLE IF NOT EXISTS document_templates (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    template_key VARCHAR(120) NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    file_path TEXT NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_document_templates_org_key
ON document_templates(organization_id, template_key);

CREATE TABLE IF NOT EXISTS generated_documents (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    template_id INTEGER REFERENCES document_templates(id) ON DELETE SET NULL,
    document_type VARCHAR(120) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO document_templates (organization_id, template_key, template_name, file_path, active)
SELECT 1, seed.template_key, seed.template_name, seed.file_path, TRUE
FROM (
    VALUES
    ('assunzione_incarico', 'Assunzione incarico mediatore', 'templates/mediation/Assunzione incarico.ODT'),
    ('dichiarazione_imparzialita', 'Dichiarazione imparzialita e riservatezza', 'templates/mediation/Dichiarazione imparzialità.ODT'),
    ('convocazione_primo_incontro', 'Lettera convocazione primo incontro', 'templates/mediation/Mediazione 070.2026 - Lettera_Primo_Incontro_Aggiornata-----per il 31.07 alle 11.15.docx'),
    ('modulo_adesione', 'Modulo adesione', 'templates/mediation/Modulo adesione.docx'),
    ('verbale_primo_incontro', 'Verbale primo incontro', 'templates/mediation/Verbale primo incontro.docx'),
    ('verbale_negativo', 'Verbale negativo', 'templates/mediation/Verbale negativo.docx'),
    ('verbale_accordo', 'Verbale accordo', 'templates/mediation/Verbale accordo.docx'),
    ('verbale_rinvio', 'Verbale rinvio', 'templates/mediation/Verbale rinvio.docx'),
    ('chiusura_pratica', 'Chiusura pratica', 'templates/mediation/Chiusura pratica.docx')
) AS seed(template_key, template_name, file_path)
WHERE NOT EXISTS (
    SELECT 1 FROM document_templates dt
    WHERE dt.organization_id = 1 AND dt.template_key = seed.template_key
);
