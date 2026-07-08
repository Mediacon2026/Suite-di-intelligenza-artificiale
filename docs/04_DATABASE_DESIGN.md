# 04 - Database Design

## Principio base
La tabella `contacts` è il cuore del sistema. Ogni soggetto è registrato una sola volta e può assumere più ruoli.

## Domini MVP
### Core
organizations, offices, users, roles, permissions, user_roles, audit_logs, settings

### CRM
contacts, contact_roles, addresses, emails, phones, notes, tags

### Mediazione
mediations, mediation_parties, mediation_lawyers, mediation_sessions, mediation_fees, mediation_payments, mediation_documents, mediation_outcomes, dgstat_reports

### Documentale
documents, document_versions, templates, generated_documents

### Contabilità
payments, invoices, compensation, office_retrocessions
