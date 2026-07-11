import json
import re
import zipfile
from datetime import datetime
from pathlib import Path
from typing import Any
from uuid import uuid4
from xml.sax.saxutils import escape as xml_escape

from fastapi import Depends, FastAPI, File, HTTPException, Query, Request, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from sqlalchemy import text
from sqlalchemy.orm import Session

from .ai_extraction_service import extract_mediation_data_from_pdf
from .ai.assistant_service import answer_question
from .ai.checklist_service import build_checklist, suggestions_from_checklist
from .ai.document_parser import parse_document
from .database import SessionLocal, get_db
from .document_classifier import classified_file
from .kernel.automation import AutomationEngine
from .kernel.event_engine import handle_event as handle_kernel_event
from .kernel.event_engine import publish_event as publish_kernel_event
from .kernel.errors import KernelError, ValidationError
from .kernel.kernel_service import get_kernel_status
from .kernel.next_action_engine import calculate_next_action, complete_action as complete_kernel_action
from .parsers.document_classifier import classify_document as classify_parser_document
from .parsers.mediation_parser import MediationParser
from .parsers.parser_registry import get_parser as get_registered_parser
from .services.case_engine import analyze_case_document


PROJECT_ROOT = Path(__file__).resolve().parents[2]
UPLOAD_ROOT = PROJECT_ROOT / "uploads"
AUTOMATION_ENGINE = AutomationEngine()


class RecordPayload(BaseModel):
    data: dict[str, Any] = Field(default_factory=dict)


class ParserTestPayload(BaseModel):
    text: str = ""
    filename: str = ""


class KernelEventTestPayload(BaseModel):
    event_type: str = "case.created"
    case_id: int | None = None
    payload: dict[str, Any] = Field(default_factory=dict)


class ContactPayload(BaseModel):
    contact_type: str = "persona"
    first_name: str | None = None
    last_name: str | None = None
    company_name: str | None = None
    fiscal_code: str | None = None
    vat_number: str | None = None
    email: str | None = None
    pec: str | None = None
    phone: str | None = None
    mobile: str | None = None
    address: str | None = None
    city: str | None = None
    province: str | None = None
    zip_code: str | None = None
    notes: str | None = None
    is_lawyer: bool = False
    is_mediator: bool = False
    is_trainer: bool = False
    is_student: bool = False
    is_debtor: bool = False
    is_creditor: bool = False
    is_client: bool = False
    is_company: bool = False


class MediationPayload(BaseModel):
    organization_id: int = 1
    office_id: int | None = None
    internal_number: str | None = None
    dgstat_number: str | None = None
    year: int | None = None
    quarter: int | None = None
    deposit_date: str | None = None
    first_meeting_date: str | None = None
    closing_date: str | None = None
    status: str = "depositata"
    outcome: str | None = None
    mediation_type: str = "obbligatoria"
    matter: str | None = None
    submatter: str | None = None
    claim_value: float | None = 0
    object: str | None = None
    reasons: str | None = None
    mediator_id: int | None = None
    tariff_id: int | None = None
    notes: str | None = None


class MediationTariffPayload(BaseModel):
    organization_id: int = 1
    name: str
    description: str | None = None
    tariff_type: str = "DM_150_2023"
    valid_from: str | None = None
    valid_to: str | None = None
    active: bool = True


class MediationTariffRowPayload(BaseModel):
    value_min: float = 0
    value_max: float | None = None
    initial_expense: float = 0
    first_meeting_negative: float = 0
    first_meeting_agreement: float = 0
    further_meetings_agreement: float = 0
    further_meetings_negative: float = 0
    is_mandatory: bool = True
    vat_rate: float = 22
    notes: str | None = None


class MediationSessionPayload(BaseModel):
    session_number: int = 1
    session_date: str | None = None
    start_time: str | None = None
    end_time: str | None = None
    mode: str = "presenza"
    location_or_link: str | None = None
    outcome: str | None = None
    notes: str | None = None


class EconomicRulePayload(BaseModel):
    organization_id: int = 1
    office_id: int | None = None
    rule_key: str
    rule_name: str
    rule_type: str
    percentage: float = 0
    applies_to: str | None = None
    mediation_outcome: str | None = None
    calculation_base: str = "netto_maturato"
    includes_startup_expenses: bool = True
    includes_first_meeting_expenses: bool = True
    includes_further_expenses: bool = True
    applies_to_main_office: bool = False
    applies_to_operational_office: bool = False
    active: bool = True
    valid_from: str | None = None
    valid_to: str | None = None
    notes: str | None = None


class IntakeCreatePayload(BaseModel):
    organization_id: int = 1
    office_id: int | None = None
    pdf_intake_id: int | None = None
    zip_intake_id: int | None = None
    extracted_data: dict[str, Any] = Field(default_factory=dict)
    documents: list[dict[str, Any]] = Field(default_factory=list)
    mediator_id: int | None = None
    mediator_name: str | None = None
    first_meeting_date: str | None = None
    first_meeting_time: str | None = None
    first_meeting_location: str | None = None
    first_meeting_mode: str = "presenza"


class IntakeReviewPayload(BaseModel):
    organization_id: int = 1
    office_id: int | None = None
    pdf_intake_id: int | None = None
    zip_intake_id: int | None = None
    extracted_data: dict[str, Any] = Field(default_factory=dict)
    documents: list[dict[str, Any]] = Field(default_factory=list)


class IntakeSessionCreatePayload(BaseModel):
    extracted_data: dict[str, Any] = Field(default_factory=dict)


class CasePayload(BaseModel):
    organization_id: int = 1
    office_id: int | None = None
    case_type: str = "mediation"
    case_number: str | None = None
    title: str
    status: str = "aperto"
    priority: str = "media"
    opened_at: str | None = None
    closed_at: str | None = None
    related_entity_id: int | None = None
    related_entity_type: str | None = None
    notes: str | None = None


class CaseTaskPayload(BaseModel):
    title: str
    description: str | None = None
    assigned_to: str | None = None
    due_date: str | None = None
    status: str = "aperta"
    priority: str = "media"


class CaseDeadlinePayload(BaseModel):
    title: str
    deadline_date: str
    deadline_type: str | None = None
    completed: bool = False
    notes: str | None = None


class CaseContactPayload(BaseModel):
    contact_id: int
    role: str
    notes: str | None = None


class AssistantQuestionPayload(BaseModel):
    question: str


class ConfigPayload(BaseModel):
    data: dict[str, Any] = Field(default_factory=dict)


CONTACT_FIELDS = [
    "contact_type",
    "first_name",
    "last_name",
    "company_name",
    "fiscal_code",
    "vat_number",
    "email",
    "pec",
    "phone",
    "mobile",
    "address",
    "city",
    "province",
    "zip_code",
    "notes",
    "is_lawyer",
    "is_mediator",
    "is_trainer",
    "is_student",
    "is_debtor",
    "is_creditor",
    "is_client",
    "is_company",
]

MEDIATION_FIELDS = [
    "organization_id",
    "office_id",
    "internal_number",
    "dgstat_number",
    "year",
    "quarter",
    "deposit_date",
    "first_meeting_date",
    "closing_date",
    "status",
    "outcome",
    "mediation_type",
    "matter",
    "submatter",
    "claim_value",
    "object",
    "reasons",
    "mediator_id",
    "tariff_id",
    "tariff_row_id",
    "calculated_initial_expense",
    "calculated_first_meeting_fee",
    "calculated_further_fee",
    "calculated_vat",
    "calculated_total",
    "notes",
]

CONFIG_TABLES = {
    "modules": {
        "table": "config_modules",
        "fields": ["organization_id", "module_key", "module_name", "enabled"],
        "defaults": {"organization_id": 1, "enabled": True},
        "order": "module_name ASC",
    },
    "numbering-rules": {
        "table": "config_numbering_rules",
        "fields": ["organization_id", "module_key", "format_pattern", "current_year", "current_sequence", "reset_policy", "active"],
        "defaults": {"organization_id": 1, "active": True},
        "order": "module_key ASC",
    },
    "matters": {
        "table": "config_mediation_matters",
        "fields": ["organization_id", "name", "ministerial_code", "active"],
        "defaults": {"organization_id": 1, "active": True},
        "order": "name ASC",
    },
    "workflows": {
        "table": "config_workflows",
        "fields": ["organization_id", "module_key", "name", "active"],
        "defaults": {"organization_id": 1, "active": True},
        "order": "module_key ASC, name ASC",
    },
    "workflow-steps": {
        "table": "config_workflow_steps",
        "fields": ["workflow_id", "step_order", "step_key", "step_name", "required"],
        "defaults": {"required": False},
        "order": "step_order ASC, id ASC",
    },
    "roles": {
        "table": "config_roles",
        "fields": ["organization_id", "role_key", "role_name", "description", "active"],
        "defaults": {"organization_id": 1, "active": True},
        "order": "role_name ASC",
    },
    "economic-parameters": {
        "table": "config_economic_parameters",
        "fields": ["organization_id", "parameter_key", "parameter_value", "valid_from", "valid_to", "active"],
        "defaults": {"organization_id": 1, "active": True},
        "order": "parameter_key ASC, valid_from DESC NULLS LAST",
    },
}


APP_MODULES = [
    "Dashboard",
    "CRM",
    "Mediazioni",
    "Formazione",
    "Orientamento",
    "OCC",
    "Crisi Impresa",
    "Documenti",
    "Pagamenti",
    "Scadenze",
    "Impostazioni",
]


MODULES = {
    "dashboard": {
        "label": "Dashboard",
        "table": None,
        "fields": [],
        "required_defaults": {},
        "search": [],
    },
    "contacts": {
        "label": "CRM",
        "table": "contacts",
        "fields": CONTACT_FIELDS,
        "required_defaults": {"organization_id": 1},
        "search": ["first_name", "last_name", "company_name", "email", "pec", "fiscal_code", "vat_number"],
    },
    "mediations": {
        "label": "Mediazioni",
        "table": "mediations",
        "fields": MEDIATION_FIELDS,
        "required_defaults": {"organization_id": 1, "office_id": 1},
        "search": ["internal_number", "dgstat_number", "object", "matter", "status", "outcome"],
    },
    "trainings": {
        "label": "Formazione",
        "table": "trainings",
        "fields": [
            "title", "course_code", "category", "start_date", "end_date", "location",
            "participants_count", "status", "notes",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "course_code", "category", "location", "status"],
    },
    "orientations": {
        "label": "Orientamento",
        "table": "orientations",
        "fields": [
            "title", "contact_id", "service_type", "appointment_date", "operator_name",
            "status", "outcome", "notes",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "service_type", "operator_name", "status", "outcome"],
    },
    "occ-cases": {
        "label": "OCC / Sovraindebitamento",
        "table": "occ_cases",
        "fields": [
            "title", "contact_id", "procedure_type", "protocol_number", "filing_date",
            "manager_name", "debt_amount", "status", "notes",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "procedure_type", "protocol_number", "manager_name", "status"],
    },
    "crisis-cases": {
        "label": "Crisi Impresa",
        "table": "crisis_cases",
        "fields": [
            "title", "company_name", "advisor_name", "service_type", "start_date",
            "risk_level", "status", "notes",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "company_name", "advisor_name", "service_type", "risk_level", "status"],
    },
    "documents": {
        "label": "Documenti",
        "table": "documents",
        "fields": [
            "title", "area", "reference_id", "document_type", "file_name", "file_path",
            "status", "notes",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "area", "document_type", "file_name", "status"],
    },
    "payments": {
        "label": "Pagamenti",
        "table": "payments",
        "fields": [
            "title", "area", "reference_id", "contact_id", "amount", "vat", "total",
            "method", "payment_date", "due_date", "status", "reason",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "area", "method", "status", "reason"],
    },
    "deadlines": {
        "label": "Scadenze",
        "table": "deadlines",
        "fields": [
            "title", "area", "reference_id", "due_date", "priority", "assignee_name",
            "status", "notes",
        ],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "area", "priority", "assignee_name", "status", "notes"],
    },
    "settings": {
        "label": "Impostazioni",
        "table": "app_settings",
        "fields": ["title", "setting_key", "setting_value", "category", "notes"],
        "required_defaults": {"organization_id": 1},
        "search": ["title", "setting_key", "setting_value", "category", "notes"],
    },
}


app = FastAPI(title="Mediacon Hub ERP API", version="0.1.1-alpha-recovery")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.exception_handler(KernelError)
async def kernel_error_handler(_request: Request, exc: KernelError):
    return JSONResponse(status_code=400, content=exc.to_dict())


CONTACT_BOOTSTRAP_SQL = """
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
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS contact_type VARCHAR(50) NOT NULL DEFAULT 'persona';
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS first_name VARCHAR(100);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS last_name VARCHAR(100);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS company_name VARCHAR(255);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS fiscal_code VARCHAR(50);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS vat_number VARCHAR(50);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS email VARCHAR(255);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS pec VARCHAR(255);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS phone VARCHAR(50);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS mobile VARCHAR(50);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS address TEXT;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS city VARCHAR(100);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS province VARCHAR(10);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS zip_code VARCHAR(20);
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS notes TEXT;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_lawyer BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_mediator BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_trainer BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_student BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_debtor BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_creditor BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_client BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS is_company BOOLEAN DEFAULT FALSE;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE contacts ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE INDEX IF NOT EXISTS idx_contacts_master_search ON contacts (
    lower(coalesce(first_name, '')),
    lower(coalesce(last_name, '')),
    lower(coalesce(company_name, '')),
    lower(coalesce(email, '')),
    lower(coalesce(pec, '')),
    lower(coalesce(fiscal_code, '')),
    lower(coalesce(vat_number, ''))
);
"""


CONTACT_SEED_SQL = """
INSERT INTO contacts (
    organization_id, contact_type, first_name, last_name, company_name, fiscal_code,
    vat_number, email, pec, phone, mobile, city, province, notes,
    is_lawyer, is_mediator, is_trainer, is_student, is_client, is_company
)
SELECT 1, 'persona', 'Gabriele', 'Petracca', NULL, NULL, NULL,
       'gabriele.petracca@mediacon.local', NULL, NULL, NULL, 'Casarano', 'LE',
       'Contatto demo Master Anagrafica', FALSE, TRUE, TRUE, FALSE, TRUE, FALSE
WHERE NOT EXISTS (
    SELECT 1 FROM contacts
    WHERE lower(coalesce(first_name, '')) = 'gabriele'
      AND lower(coalesce(last_name, '')) = 'petracca'
);

INSERT INTO contacts (
    organization_id, contact_type, first_name, last_name, company_name, fiscal_code,
    vat_number, email, pec, phone, mobile, city, province, notes,
    is_lawyer, is_mediator, is_trainer, is_student, is_client, is_company
)
SELECT 1, 'persona', 'Laura', 'Francioso', NULL, NULL, NULL,
       'laura.francioso@mediacon.local', NULL, NULL, NULL, 'Casarano', 'LE',
       'Contatto demo Master Anagrafica', FALSE, TRUE, TRUE, FALSE, TRUE, FALSE
WHERE NOT EXISTS (
    SELECT 1 FROM contacts
    WHERE lower(coalesce(first_name, '')) = 'laura'
      AND lower(coalesce(last_name, '')) = 'francioso'
);

INSERT INTO contacts (
    organization_id, contact_type, first_name, last_name, company_name, fiscal_code,
    vat_number, email, pec, phone, mobile, city, province, notes,
    is_lawyer, is_mediator, is_trainer, is_student, is_client, is_company
)
SELECT 1, 'persona', 'Federico', 'Petracca', NULL, NULL, NULL,
       'federico.petracca@mediacon.local', NULL, NULL, NULL, 'Casarano', 'LE',
       'Contatto demo Master Anagrafica', TRUE, FALSE, FALSE, FALSE, TRUE, FALSE
WHERE NOT EXISTS (
    SELECT 1 FROM contacts
    WHERE lower(coalesce(first_name, '')) = 'federico'
      AND lower(coalesce(last_name, '')) = 'petracca'
);

INSERT INTO contacts (
    organization_id, contact_type, first_name, last_name, company_name, fiscal_code,
    vat_number, email, pec, phone, mobile, city, province, notes,
    is_lawyer, is_mediator, is_trainer, is_student, is_client, is_company
)
SELECT 1, 'azienda', NULL, NULL, 'Mediacon S.r.l.', NULL, NULL,
       'info@mediacon.org', 'mediacon@arubapec.it', '0833513189', NULL,
       'Casarano', 'LE', 'Societa demo Master Anagrafica',
       FALSE, FALSE, FALSE, FALSE, TRUE, TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM contacts
    WHERE lower(coalesce(company_name, '')) = 'mediacon s.r.l.'
);
"""


MEDIATION_BOOTSTRAP_SQL = """
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
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS notes TEXT;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS title VARCHAR(255);
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS subject TEXT;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS dispute_value NUMERIC(14,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS first_session_date DATE;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS closure_date DATE;
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

CREATE INDEX IF NOT EXISTS idx_mediations_org ON mediations(organization_id);
CREATE INDEX IF NOT EXISTS idx_mediations_office ON mediations(office_id);
CREATE INDEX IF NOT EXISTS idx_mediation_tariffs_org ON mediation_tariffs(organization_id);
"""


MEDIATION_SEED_SQL = """
UPDATE organizations
SET name = 'Mediacon S.r.l.',
    registration_number = '707',
    email = coalesce(email, 'info@mediacon.org'),
    pec = coalesce(pec, 'mediacon@arubapec.it'),
    phone = coalesce(phone, '0833513189'),
    city = coalesce(city, 'Casarano'),
    province = coalesce(province, 'LE'),
    active = TRUE,
    updated_at = CURRENT_TIMESTAMP
WHERE id = 1;

INSERT INTO offices (organization_id, name, office_type, address, city, province, zip_code, email, pec, phone, manager_name, retrocession_percentage, active)
SELECT 1, 'Casarano', 'principale', 'Via Bruno Buozzi 10', 'Casarano', 'LE', NULL, 'info@mediacon.org', 'mediacon@arubapec.it', '0833513189', 'Direzione Mediacon', 0, TRUE
WHERE NOT EXISTS (SELECT 1 FROM offices WHERE organization_id = 1 AND lower(name) = 'casarano');

INSERT INTO offices (organization_id, name, office_type, address, city, province, active)
SELECT 1, 'Pachino', 'secondaria', 'Via Fratelli Bandiera 82', 'Pachino', 'SR', TRUE
WHERE NOT EXISTS (SELECT 1 FROM offices WHERE organization_id = 1 AND lower(name) = 'pachino');

INSERT INTO offices (organization_id, name, office_type, address, city, province, active)
SELECT 1, 'Napoli', 'secondaria', 'Via Enrico Pessina 66', 'Napoli', 'NA', TRUE
WHERE NOT EXISTS (SELECT 1 FROM offices WHERE organization_id = 1 AND lower(name) = 'napoli');

UPDATE offices
SET email = 'pachino@mediacon.org',
    pec = 'mediaconpachino@arubapec.it',
    address = 'Via Fratelli Bandiera 82',
    city = 'Pachino',
    province = 'SR'
WHERE organization_id = 1 AND lower(name) = 'pachino';

UPDATE offices
SET email = 'napoli@mediacon.org',
    pec = 'mediaconnapoli@arubapec.it',
    address = 'Via Enrico Pessina 66',
    city = 'Napoli',
    province = 'NA'
WHERE organization_id = 1 AND lower(name) = 'napoli';

INSERT INTO mediation_tariffs (organization_id, name, description, tariff_type, valid_from, active)
SELECT 1, 'DM 150/2023 Mediacon', 'Tariffario iniziale DM 150/2023 per Mediacon', 'DM_150_2023', DATE '2023-11-15', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM mediation_tariffs
    WHERE organization_id = 1 AND name = 'DM 150/2023 Mediacon'
);

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
"""


CONFIG_BOOTSTRAP_SQL = """
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

ALTER TABLE mediations ADD COLUMN IF NOT EXISTS netto_maturato_calcolato NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quota_mediatore_calcolata NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quota_sede_operativa_calcolata NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS quota_mediacon_calcolata NUMERIC(12,2) DEFAULT 0;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS regola_economica_applicata_id INTEGER REFERENCES economic_rules(id) ON DELETE SET NULL;
ALTER TABLE mediations ADD COLUMN IF NOT EXISTS data_calcolo TIMESTAMP;

CREATE TABLE IF NOT EXISTS document_intakes (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    intake_type VARCHAR(40) NOT NULL,
    source_filename VARCHAR(255) NOT NULL,
    extracted_json JSONB,
    status VARCHAR(80) DEFAULT 'extracted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS intake_sessions (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    user_id INTEGER,
    status VARCHAR(80) DEFAULT 'in_review',
    review_status VARCHAR(80) DEFAULT 'pending',
    extracted_json JSONB DEFAULT '{}'::jsonb,
    parser_results JSONB DEFAULT '[]'::jsonb,
    created_case_id INTEGER,
    created_mediation_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS intake_session_documents (
    id SERIAL PRIMARY KEY,
    intake_session_id INTEGER NOT NULL REFERENCES intake_sessions(id) ON DELETE CASCADE,
    document_type VARCHAR(120),
    module VARCHAR(80),
    confidence NUMERIC(5,2) DEFAULT 0,
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    mime_type VARCHAR(160),
    parser_result JSONB DEFAULT '{}'::jsonb,
    extracted_json JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mediation_documents (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    document_type VARCHAR(100) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);

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

ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS organization_id INTEGER REFERENCES organizations(id) ON DELETE CASCADE;
ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS template_key VARCHAR(120);
ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS template_name VARCHAR(255);
ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS file_path TEXT;
ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS active BOOLEAN DEFAULT TRUE;
ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE document_templates ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
CREATE UNIQUE INDEX IF NOT EXISTS idx_document_templates_org_key ON document_templates(organization_id, template_key);

CREATE TABLE IF NOT EXISTS generated_documents (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    template_id INTEGER REFERENCES document_templates(id) ON DELETE SET NULL,
    document_type VARCHAR(120) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE generated_documents ADD COLUMN IF NOT EXISTS mediation_id INTEGER REFERENCES mediations(id) ON DELETE CASCADE;
ALTER TABLE generated_documents ADD COLUMN IF NOT EXISTS template_id INTEGER REFERENCES document_templates(id) ON DELETE SET NULL;
ALTER TABLE generated_documents ADD COLUMN IF NOT EXISTS document_type VARCHAR(120);
ALTER TABLE generated_documents ADD COLUMN IF NOT EXISTS filename VARCHAR(255);
ALTER TABLE generated_documents ADD COLUMN IF NOT EXISTS file_path TEXT;
ALTER TABLE generated_documents ADD COLUMN IF NOT EXISTS generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS mediator_assignments (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER NOT NULL REFERENCES mediations(id) ON DELETE CASCADE,
    mediator_contact_id INTEGER,
    mediator_name VARCHAR(255),
    assignment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acceptance_deadline TIMESTAMP,
    email_sent_at TIMESTAMP,
    accepted_at TIMESTAMP,
    refused_at TIMESTAMP,
    reminder_sent_at TIMESTAMP,
    status VARCHAR(80) DEFAULT 'draft',
    generated_documents JSONB DEFAULT '[]'::jsonb,
    signed_documents JSONB DEFAULT '[]'::jsonb,
    signature_link TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE mediator_assignments ADD COLUMN IF NOT EXISTS mediator_name VARCHAR(255);
ALTER TABLE mediator_assignments ADD COLUMN IF NOT EXISTS reminder_sent_at TIMESTAMP;
ALTER TABLE mediator_assignments ADD COLUMN IF NOT EXISTS signature_link TEXT;
ALTER TABLE mediator_assignments ADD COLUMN IF NOT EXISTS generated_documents JSONB DEFAULT '[]'::jsonb;
ALTER TABLE mediator_assignments ADD COLUMN IF NOT EXISTS signed_documents JSONB DEFAULT '[]'::jsonb;

CREATE TABLE IF NOT EXISTS email_messages (
    id SERIAL PRIMARY KEY,
    mediation_id INTEGER REFERENCES mediations(id) ON DELETE CASCADE,
    assignment_id INTEGER REFERENCES mediator_assignments(id) ON DELETE SET NULL,
    recipient_email VARCHAR(255),
    subject TEXT,
    body_text TEXT,
    status VARCHAR(80) DEFAULT 'draft',
    sent_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cases (
    id SERIAL PRIMARY KEY,
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    office_id INTEGER REFERENCES offices(id) ON DELETE SET NULL,
    case_type VARCHAR(40) NOT NULL,
    case_number VARCHAR(80),
    title VARCHAR(255) NOT NULL,
    status VARCHAR(100) DEFAULT 'aperto',
    priority VARCHAR(60) DEFAULT 'media',
    opened_at DATE,
    closed_at DATE,
    related_entity_id INTEGER,
    related_entity_type VARCHAR(80),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_documents (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    document_type VARCHAR(100),
    filename VARCHAR(255) NOT NULL,
    file_path TEXT,
    original_filename VARCHAR(255),
    file_size BIGINT,
    mime_type VARCHAR(160),
    classification_status VARCHAR(80),
    extracted_text TEXT,
    extracted_json JSONB,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_timeline (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by VARCHAR(160),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_tasks (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to VARCHAR(160),
    due_date DATE,
    status VARCHAR(100) DEFAULT 'aperta',
    priority VARCHAR(60) DEFAULT 'media',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_deadlines (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    deadline_date DATE NOT NULL,
    deadline_type VARCHAR(100),
    completed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS case_contacts (
    id SERIAL PRIMARY KEY,
    case_id INTEGER NOT NULL REFERENCES cases(id) ON DELETE CASCADE,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    role VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_economic_rules_org_office ON economic_rules(organization_id, office_id, active);
"""


CONFIG_SEED_SQL = """
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
WHERE NOT EXISTS (
    SELECT 1 FROM config_modules cm
    WHERE cm.organization_id = 1 AND cm.module_key = seed.module_key
);

INSERT INTO config_numbering_rules (organization_id, module_key, format_pattern, current_year, current_sequence, reset_policy, active)
SELECT 1, 'mediazioni', '{sequence}/2026', 2026, 0, 'yearly', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM config_numbering_rules
    WHERE organization_id = 1 AND module_key = 'mediazioni'
);

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
WHERE NOT EXISTS (
    SELECT 1 FROM config_mediation_matters cmm
    WHERE cmm.organization_id = 1 AND lower(cmm.name) = lower(seed.name)
);

INSERT INTO config_workflows (organization_id, module_key, name, active)
SELECT 1, 'mediazioni', 'Mediazione Base', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM config_workflows
    WHERE organization_id = 1 AND module_key = 'mediazioni' AND name = 'Mediazione Base'
);

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
  AND NOT EXISTS (
      SELECT 1 FROM config_workflow_steps s
      WHERE s.workflow_id = wf.id AND s.step_key = seed.step_key
  );

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
WHERE NOT EXISTS (
    SELECT 1 FROM config_roles cr
    WHERE cr.organization_id = 1 AND cr.role_key = seed.role_key
);

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
WHERE NOT EXISTS (
    SELECT 1 FROM config_permissions cp
    WHERE cp.permission_key = seed.permission_key
);

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

INSERT INTO economic_rules (
    organization_id, office_id, rule_key, rule_name, rule_type, percentage,
    applies_to, mediation_outcome, calculation_base,
    includes_startup_expenses, includes_first_meeting_expenses, includes_further_expenses,
    applies_to_main_office, applies_to_operational_office, active, valid_from, notes
)
SELECT 1, o.id, 'operational_office_percentage', 'Quota sede operativa', 'office_retrocession', 70,
       'netto_maturato', NULL, 'netto_maturato',
       TRUE, TRUE, TRUE, FALSE, TRUE, TRUE, CURRENT_DATE,
       'La sede operativa percepisce il 70% del netto maturato e gestisce autonomamente i propri mediatori.'
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
    applies_to, mediation_outcome, calculation_base,
    includes_startup_expenses, includes_first_meeting_expenses, includes_further_expenses,
    applies_to_main_office, applies_to_operational_office, active, valid_from, notes
)
SELECT 1, o.id, 'main_office_percentage', 'Quota Mediacon su sede operativa', 'mediacon_retention', 30,
       'netto_maturato', NULL, 'netto_maturato',
       TRUE, TRUE, TRUE, FALSE, TRUE, TRUE, CURRENT_DATE,
       'Mediacon trattiene il 30% del netto maturato per le sedi operative.'
FROM offices o
WHERE o.organization_id = 1
  AND lower(o.name) IN ('pachino', 'napoli')
  AND NOT EXISTS (
      SELECT 1 FROM economic_rules er
      WHERE er.organization_id = 1
        AND er.office_id = o.id
        AND er.rule_key = 'main_office_percentage'
  );

INSERT INTO document_templates (organization_id, template_key, template_name, file_path, active)
SELECT 1, seed.template_key, seed.template_name, seed.file_path, TRUE
FROM (
    VALUES
    ('assunzione_incarico', 'Assunzione incarico mediatore', 'templates/mediation/Assunzione incarico.ODT'),
    ('dichiarazione_imparzialita', 'Dichiarazione imparzialità e riservatezza', 'templates/mediation/Dichiarazione imparzialità.ODT'),
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
"""


@app.on_event("startup")
def bootstrap_contacts_schema():
    db = SessionLocal()
    try:
        ensure_template_files()
        for statement in (CONTACT_BOOTSTRAP_SQL + MEDIATION_BOOTSTRAP_SQL + CONFIG_BOOTSTRAP_SQL).split(";"):
            if statement.strip():
                db.execute(text(statement))
        for statement in (CONTACT_SEED_SQL + MEDIATION_SEED_SQL + CONFIG_SEED_SQL).split(";"):
            if statement.strip():
                db.execute(text(statement))
        db.commit()
    finally:
        db.close()


def module_config(module: str) -> dict[str, Any]:
    config = MODULES.get(module)
    if not config:
        raise HTTPException(status_code=404, detail="Modulo non trovato")
    return config


def clean_payload(config: dict[str, Any], payload: dict[str, Any], include_defaults: bool) -> dict[str, Any]:
    allowed = set(config["fields"])
    data = {key: value for key, value in payload.items() if key in allowed}
    if include_defaults:
        data = {**config["required_defaults"], **data}
    return {key: value for key, value in data.items() if value != ""}


def row_to_dict(row: Any) -> dict[str, Any]:
    return dict(row._mapping)


def normalize_contact(payload: ContactPayload) -> dict[str, Any]:
    data = payload.model_dump()
    for key, value in list(data.items()):
        if isinstance(value, str):
            value = value.strip()
            data[key] = value or None
    if data.get("contact_type") not in {"persona", "azienda"}:
        data["contact_type"] = "azienda" if data.get("is_company") else "persona"
    if data.get("contact_type") == "azienda":
        data["is_company"] = True
    return data


def find_duplicate_contact(db: Session, data: dict[str, Any], exclude_id: int | None = None) -> dict[str, Any] | None:
    checks = {
        "fiscal_code": data.get("fiscal_code"),
        "vat_number": data.get("vat_number"),
        "email": data.get("email"),
        "pec": data.get("pec"),
    }
    clauses = []
    params: dict[str, Any] = {}
    joiner = " OR "
    for field, value in checks.items():
        if value:
            clauses.append(f"lower({field}) = lower(:{field})")
            params[field] = value
    if not clauses:
        if data.get("company_name"):
            clauses.append("lower(company_name) = lower(:company_name)")
            params["company_name"] = data["company_name"]
        elif data.get("first_name") and data.get("last_name"):
            clauses.append("lower(first_name) = lower(:first_name)")
            clauses.append("lower(last_name) = lower(:last_name)")
            params["first_name"] = data["first_name"]
            params["last_name"] = data["last_name"]
            joiner = " AND "
        else:
            return None
    where = joiner.join(clauses)
    if exclude_id:
        where = f"({where}) AND id <> :exclude_id"
        params["exclude_id"] = exclude_id
    row = db.execute(text(f"SELECT * FROM contacts WHERE {where} LIMIT 1"), params).fetchone()
    return row_to_dict(row) if row else None


def clean_model_data(payload: BaseModel) -> dict[str, Any]:
    data = payload.model_dump()
    for key, value in list(data.items()):
        if isinstance(value, str):
            value = value.strip()
            data[key] = value or None
    return data


def config_clean(config_key: str, payload: ConfigPayload, include_defaults: bool = False) -> dict[str, Any]:
    config = CONFIG_TABLES[config_key]
    data = {key: value for key, value in payload.data.items() if key in config["fields"]}
    for key, value in list(data.items()):
        if isinstance(value, str):
            value = value.strip()
            data[key] = value or None
    if include_defaults:
        data = {**config["defaults"], **data}
    return data


def audit(db: Session, entity_name: str, entity_id: int | None, action: str, new_value: Any = None, old_value: Any = None, organization_id: int = 1):
    db.execute(
        text(
            """
            INSERT INTO audit_logs (organization_id, entity_name, entity_id, action, old_value, new_value)
            VALUES (:organization_id, :entity_name, :entity_id, :action, :old_value, :new_value)
            """
        ),
        {
            "organization_id": organization_id,
            "entity_name": entity_name,
            "entity_id": entity_id,
            "action": action,
            "old_value": str(old_value) if old_value is not None else None,
            "new_value": str(new_value) if new_value is not None else None,
        },
    )


def config_list(config_key: str, db: Session, organization_id: int = 1) -> list[dict[str, Any]]:
    config = CONFIG_TABLES[config_key]
    rows = db.execute(
        text(
            f"""
            SELECT *
            FROM {config['table']}
            WHERE organization_id = :organization_id
            ORDER BY {config['order']}
            """
        ),
        {"organization_id": organization_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


def config_create(config_key: str, payload: ConfigPayload, db: Session) -> dict[str, Any]:
    config = CONFIG_TABLES[config_key]
    data = config_clean(config_key, payload, include_defaults=True)
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(
        text(f"INSERT INTO {config['table']} ({columns}) VALUES ({placeholders}) RETURNING *"),
        data,
    ).fetchone()
    result = row_to_dict(row)
    audit(db, config["table"], result["id"], "create", result, organization_id=result.get("organization_id", 1))
    db.commit()
    return result


def config_update(config_key: str, record_id: int, payload: ConfigPayload, db: Session) -> dict[str, Any]:
    config = CONFIG_TABLES[config_key]
    old = db.execute(text(f"SELECT * FROM {config['table']} WHERE id = :id"), {"id": record_id}).fetchone()
    if not old:
        raise HTTPException(status_code=404, detail="Configurazione non trovata")
    data = config_clean(config_key, payload, include_defaults=False)
    if not data:
        raise HTTPException(status_code=400, detail="Nessun campo valido")
    data["id"] = record_id
    assignments = ", ".join([f"{key} = :{key}" for key in data.keys() if key != "id"])
    row = db.execute(
        text(f"UPDATE {config['table']} SET {assignments}, updated_at = CURRENT_TIMESTAMP WHERE id = :id RETURNING *"),
        data,
    ).fetchone()
    result = row_to_dict(row)
    audit(db, config["table"], record_id, "update", result, row_to_dict(old), result.get("organization_id", 1))
    db.commit()
    return result


def get_active_tariff_id(db: Session, organization_id: int) -> int | None:
    return db.execute(
        text(
            """
            SELECT id
            FROM mediation_tariffs
            WHERE organization_id = :organization_id
              AND active = TRUE
              AND (valid_from IS NULL OR valid_from <= CURRENT_DATE)
              AND (valid_to IS NULL OR valid_to >= CURRENT_DATE)
            ORDER BY valid_from DESC NULLS LAST, id DESC
            LIMIT 1
            """
        ),
        {"organization_id": organization_id},
    ).scalar_one_or_none()


def calculate_mediation_fees(db: Session, organization_id: int, tariff_id: int | None, claim_value: float | None) -> dict[str, Any]:
    value = claim_value or 0
    selected_tariff_id = tariff_id or get_active_tariff_id(db, organization_id)
    if not selected_tariff_id:
        return {
            "tariff_id": None,
            "tariff_row_id": None,
            "calculated_initial_expense": 0,
            "calculated_first_meeting_fee": 0,
            "calculated_further_fee": 0,
            "calculated_vat": 0,
            "calculated_total": 0,
        }

    row = db.execute(
        text(
            """
            SELECT *
            FROM mediation_tariff_rows
            WHERE tariff_id = :tariff_id
              AND :claim_value >= value_min
              AND (:claim_value <= value_max OR value_max IS NULL)
            ORDER BY value_min ASC
            LIMIT 1
            """
        ),
        {"tariff_id": selected_tariff_id, "claim_value": value},
    ).fetchone()

    if not row:
        return {
            "tariff_id": selected_tariff_id,
            "tariff_row_id": None,
            "calculated_initial_expense": 0,
            "calculated_first_meeting_fee": 0,
            "calculated_further_fee": 0,
            "calculated_vat": 0,
            "calculated_total": 0,
        }

    tariff_row = row_to_dict(row)
    initial = float(tariff_row["initial_expense"] or 0)
    first = float(tariff_row["first_meeting_negative"] or 0)
    further = 0.0
    vat_rate = float(tariff_row["vat_rate"] or 0)
    taxable = initial + first + further
    vat = round(taxable * vat_rate / 100, 2)
    total = round(taxable + vat, 2)
    return {
        "tariff_id": selected_tariff_id,
        "tariff_row_id": tariff_row["id"],
        "calculated_initial_expense": initial,
        "calculated_first_meeting_fee": first,
        "calculated_further_fee": further,
        "calculated_vat": vat,
        "calculated_total": total,
    }


def normalize_city(value: str | None) -> str:
    return (value or "").strip().lower()


def resolve_office_id_from_data(db: Session, organization_id: int, data: dict[str, Any]) -> int | None:
    if data.get("office_id"):
        return int(data["office_id"])
    office_city = normalize_city(data.get("office_city") or data.get("office_name"))
    if not office_city:
        return None
    row = db.execute(
        text(
            """
            SELECT id
            FROM offices
            WHERE organization_id = :organization_id
              AND active = TRUE
              AND (lower(city) = :office_city OR lower(name) = :office_city)
            ORDER BY id
            LIMIT 1
            """
        ),
        {"organization_id": organization_id, "office_city": office_city},
    ).fetchone()
    return row.id if row else None


def next_internal_number(db: Session, organization_id: int, year: int) -> str:
    rule = db.execute(
        text(
            """
            UPDATE config_numbering_rules
            SET current_sequence = current_sequence + 1
            WHERE id = (
                SELECT id
                FROM config_numbering_rules
                WHERE organization_id = :organization_id
                  AND module_key = 'mediazioni'
                  AND active = TRUE
                  AND (current_year = :year OR reset_policy = 'yearly')
                ORDER BY id
                LIMIT 1
            )
            RETURNING format_pattern, current_sequence
            """
        ),
        {"organization_id": organization_id, "year": year},
    ).fetchone()
    if rule:
        pattern = rule.format_pattern or "{sequence}/{year}"
        return pattern.replace("{sequence}", f"{int(rule.current_sequence):03d}").replace("{year}", str(year))
    count = db.execute(
        text("SELECT COUNT(*) FROM mediations WHERE organization_id = :organization_id AND year = :year"),
        {"organization_id": organization_id, "year": year},
    ).scalar_one()
    return f"MED-{year}-{count + 1:03d}"


def next_action_context_for_mediation(mediation: dict[str, Any]) -> dict[str, Any]:
    timeline_types = {item.get("event_type") for item in mediation.get("timeline", [])}
    assignment = mediation.get("mediator_assignment") or {}
    sessions = mediation.get("sessions") or []
    generated_types = {item.get("document_type") for item in mediation.get("generated_documents", [])}
    completed_sessions = [item for item in sessions if item.get("outcome") or item.get("status") == "completed"]
    has_outcome = bool(mediation.get("outcome") or any(item.get("outcome") for item in sessions))
    verbal_generated = bool(
        generated_types.intersection({"verbale_primo_incontro", "verbale_negativo", "verbale_accordo", "verbale_rinvio", "verbale_mancata_adesione"})
        or "verbal.generated" in timeline_types
    )
    return {
        "mediator_assigned": bool(mediation.get("mediator_id") or assignment.get("mediator_contact_id") or assignment.get("mediator_name")),
        "mediator_assignment_sent": bool(assignment.get("email_sent_at") or assignment.get("status") in {"sent_waiting_signature", "accepted"}),
        "mediator_accepted": bool(assignment.get("accepted_at") or assignment.get("status") == "accepted"),
        "first_meeting_scheduled": bool(sessions or mediation.get("first_meeting_date")),
        "first_meeting_notice_generated": bool("convocazione_primo_incontro" in generated_types or "first_meeting_notice_generated" in timeline_types or "notice.generated" in timeline_types),
        "first_meeting_notice_sent": bool("first_meeting_notice_sent" in timeline_types or "notice.sent" in timeline_types),
        "first_meeting_completed": bool(completed_sessions or "meeting.completed" in timeline_types),
        "first_meeting_outcome_recorded": has_outcome,
        "verbal_generated": verbal_generated,
        "dgstat_completed": bool(mediation.get("dgstat_number") or "dgstat.completed" in timeline_types),
        "missing_required_data": not mediation.get("office_id"),
    }


def mediation_detail(db: Session, mediation_id: int) -> dict[str, Any]:
    row = db.execute(
        text(
            """
            SELECT m.*, o.name AS office_name, org.name AS organization_name,
                   t.name AS tariff_name,
                   tr.value_min AS tariff_value_min,
                   tr.value_max AS tariff_value_max
            FROM mediations m
            LEFT JOIN offices o ON o.id = m.office_id
            LEFT JOIN organizations org ON org.id = m.organization_id
            LEFT JOIN mediation_tariffs t ON t.id = m.tariff_id
            LEFT JOIN mediation_tariff_rows tr ON tr.id = m.tariff_row_id
            WHERE m.id = :id
            """
        ),
        {"id": mediation_id},
    ).fetchone()
    if not row:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")

    detail = row_to_dict(row)
    detail["parties"] = [
        row_to_dict(item)
        for item in db.execute(
            text(
                """
                SELECT p.*, c.first_name, c.last_name, c.company_name
                FROM mediation_parties p
                LEFT JOIN contacts c ON c.id = p.contact_id
                WHERE p.mediation_id = :id
                ORDER BY p.id
                """
            ),
            {"id": mediation_id},
        ).fetchall()
    ]
    detail["lawyers"] = [
        row_to_dict(item)
        for item in db.execute(
            text(
                """
                SELECT l.*, c.first_name, c.last_name, c.company_name
                FROM mediation_lawyers l
                LEFT JOIN contacts c ON c.id = l.lawyer_contact_id
                WHERE l.mediation_id = :id
                ORDER BY l.id
                """
            ),
            {"id": mediation_id},
        ).fetchall()
    ]
    detail["sessions"] = [
        row_to_dict(item)
        for item in db.execute(
            text("SELECT * FROM mediation_sessions WHERE mediation_id = :id ORDER BY session_number, session_date"),
            {"id": mediation_id},
        ).fetchall()
    ]
    detail["documents"] = [
        row_to_dict(item)
        for item in db.execute(
            text("SELECT * FROM mediation_documents WHERE mediation_id = :id ORDER BY uploaded_at DESC, id DESC"),
            {"id": mediation_id},
        ).fetchall()
    ]
    detail["generated_documents"] = [
        row_to_dict(item)
        for item in db.execute(
            text(
                """
                SELECT gd.*, dt.template_name
                FROM generated_documents gd
                LEFT JOIN document_templates dt ON dt.id = gd.template_id
                WHERE gd.mediation_id = :id
                ORDER BY gd.generated_at DESC, gd.id DESC
                """
            ),
            {"id": mediation_id},
        ).fetchall()
    ]
    assignment = db.execute(
        text("SELECT * FROM mediator_assignments WHERE mediation_id = :id ORDER BY id DESC LIMIT 1"),
        {"id": mediation_id},
    ).fetchone()
    detail["mediator_assignment"] = row_to_dict(assignment) if assignment else None
    timeline_rows = db.execute(
        text(
            """
            SELECT t.*
            FROM case_timeline t
            JOIN cases c ON c.id = t.case_id
            WHERE c.related_entity_type = 'mediation' AND c.related_entity_id = :id
            ORDER BY t.created_at ASC, t.id ASC
            """
        ),
        {"id": mediation_id},
    ).fetchall()
    detail["timeline"] = [row_to_dict(item) for item in timeline_rows]
    case_row = db.execute(
        text("SELECT id FROM cases WHERE related_entity_type = 'mediation' AND related_entity_id = :id LIMIT 1"),
        {"id": mediation_id},
    ).fetchone()
    detail["case_id"] = case_row.id if case_row else None
    detail["next_action"] = calculate_next_action(detail["case_id"] or 0, next_action_context_for_mediation(detail))
    return detail


def economic_rule_data(payload: EconomicRulePayload) -> dict[str, Any]:
    data = clean_model_data(payload)
    data["percentage"] = float(data.get("percentage") or 0)
    return data


def calculate_economic_split_data(db: Session, mediation_id: int) -> dict[str, Any]:
    row = db.execute(
        text(
            """
            SELECT m.*, o.name AS office_name, o.office_type
            FROM mediations m
            LEFT JOIN offices o ON o.id = m.office_id
            WHERE m.id = :id
            """
        ),
        {"id": mediation_id},
    ).fetchone()
    if not row:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")

    mediation = row_to_dict(row)
    organization_id = mediation["organization_id"] or 1
    office_id = mediation["office_id"]
    outcome = mediation.get("outcome") or mediation.get("status") or "da_definire"
    total_collected = float(mediation.get("calculated_total") or 0)
    vat_excluded = float(mediation.get("calculated_vat") or 0)
    startup_expenses_net = float(mediation.get("calculated_initial_expense") or 0)
    first_meeting_net = float(mediation.get("calculated_first_meeting_fee") or 0)
    further_net = float(mediation.get("calculated_further_fee") or 0)
    matured_net = startup_expenses_net + first_meeting_net + further_net

    rules = [
        row_to_dict(item)
        for item in db.execute(
            text(
                """
                SELECT *
                FROM economic_rules
                WHERE organization_id = :organization_id
                  AND active = TRUE
                  AND (office_id = :office_id OR office_id IS NULL)
                  AND (valid_from IS NULL OR valid_from <= CURRENT_DATE)
                  AND (valid_to IS NULL OR valid_to >= CURRENT_DATE)
                ORDER BY office_id NULLS LAST, id
                """
            ),
            {"organization_id": organization_id, "office_id": office_id},
        ).fetchall()
    ]

    mediator_share = 0.0
    operational_office_share = 0.0
    mediacon_share = 0.0
    applied_rules = []
    applied_rule_id = None

    office_name = (mediation.get("office_name") or "").lower()
    is_main_office = mediation.get("office_type") == "principale" or office_name == "casarano"

    if is_main_office:
        rule = next(
            (
                item for item in rules
                if item["rule_key"] == "mediator_net_matured_percentage"
            ),
            next((item for item in rules if item["rule_key"] == "mediator_first_meeting_net_percentage" and not item.get("mediation_outcome")), None),
        )
        if rule:
            mediator_share = round(matured_net * float(rule["percentage"] or 0) / 100, 2)
            mediacon_share = round(max(matured_net - mediator_share, 0), 2)
            applied_rule_id = rule["id"]
            applied_rules.append(rule)
    else:
        office_rule = next((item for item in rules if item["rule_key"] == "operational_office_percentage"), None)
        mediacon_rule = next((item for item in rules if item["rule_key"] == "main_office_percentage"), None)
        if office_rule:
            operational_office_share = round(matured_net * float(office_rule["percentage"] or 0) / 100, 2)
            applied_rule_id = office_rule["id"]
            applied_rules.append(office_rule)
        if mediacon_rule:
            mediacon_share = round(matured_net * float(mediacon_rule["percentage"] or 0) / 100, 2)
            applied_rules.append(mediacon_rule)
        elif office_rule:
            mediacon_share = round(max(matured_net - operational_office_share, 0), 2)

    return {
        "mediation_id": mediation_id,
        "office_id": office_id,
        "office_name": mediation.get("office_name"),
        "mediation_outcome": outcome,
        "total_collected": round(total_collected, 2),
        "net_taxable": round(matured_net, 2),
        "netto_maturato": round(matured_net, 2),
        "vat_excluded": round(vat_excluded, 2),
        "living_expenses_excluded": 0,
        "startup_expenses_net": round(startup_expenses_net, 2),
        "first_meeting_net": round(first_meeting_net, 2),
        "further_expenses_net": round(further_net, 2),
        "mediator_share": round(mediator_share, 2),
        "operational_office_share": round(operational_office_share, 2),
        "mediacon_share": round(mediacon_share, 2),
        "applied_rule_id": applied_rule_id,
        "applied_rules": applied_rules,
        "note": "Le regole economiche sono configurabili e storicizzate.",
    }


def safe_filename(filename: str) -> str:
    name = Path(filename or "file").name
    return re.sub(r"[^A-Za-z0-9._-]+", "_", name)


async def save_upload(file: UploadFile, target_dir: Path) -> Path:
    target_dir.mkdir(parents=True, exist_ok=True)
    target_path = target_dir / f"{uuid4().hex}_{safe_filename(file.filename)}"
    target_path.write_bytes(await file.read())
    return target_path


def create_document_intake(
    db: Session,
    organization_id: int,
    office_id: int | None,
    intake_type: str,
    source_filename: str,
    extracted: dict[str, Any],
) -> dict[str, Any]:
    row = db.execute(
        text(
            """
            INSERT INTO document_intakes (
                organization_id, office_id, intake_type, source_filename, extracted_json, status
            )
            VALUES (:organization_id, :office_id, :intake_type, :source_filename, CAST(:extracted_json AS jsonb), 'extracted')
            RETURNING *
            """
        ),
        {
            "organization_id": organization_id,
            "office_id": office_id,
            "intake_type": intake_type,
            "source_filename": source_filename,
            "extracted_json": json.dumps(extracted, ensure_ascii=False),
        },
    ).fetchone()
    db.commit()
    return row_to_dict(row)


def read_intake_file_text(path: Path) -> str:
    suffix = path.suffix.lower()
    if suffix == ".pdf":
        try:
            from .pdf_parser import extract_pdf_text

            return extract_pdf_text(path)
        except Exception:
            return ""
    if suffix in {".txt", ".csv", ".eml", ".msg", ".docx", ".odt"}:
        return path.read_text(encoding="utf-8", errors="ignore")
    return ""


def flatten_parser_fields(parser_result: dict[str, Any]) -> dict[str, Any]:
    fields = parser_result.get("extracted_fields") or {}
    return {key: value for key, value in fields.items() if value not in ("", [], None)}


def intake_session_detail(db: Session, session_id: int) -> dict[str, Any]:
    session = db.execute(text("SELECT * FROM intake_sessions WHERE id = :id"), {"id": session_id}).fetchone()
    if not session:
        raise HTTPException(status_code=404, detail="Intake session non trovata")
    data = row_to_dict(session)
    documents = [
        row_to_dict(row)
        for row in db.execute(
            text("SELECT * FROM intake_session_documents WHERE intake_session_id = :id ORDER BY id"),
            {"id": session_id},
        ).fetchall()
    ]
    data["documents"] = documents
    data["missing_fields"] = [field for field in ["claimant", "claimant_lawyer", "invited_party", "claim_value", "matter", "object"] if not (data.get("extracted_json") or {}).get(field)]
    metadata: dict[str, Any] = {}
    for document in documents:
        parser_result = document.get("parser_result") or {}
        source = document.get("filename")
        for field, confidence in (parser_result.get("confidence_by_field") or {}).items():
            if field not in metadata or confidence > metadata[field].get("confidence", 0):
                metadata[field] = {
                    "confidence": confidence,
                    "source_document": source,
                    "status": "confermato" if confidence >= 0.75 and (data.get("extracted_json") or {}).get(field) else "da_verificare",
                }
    data["field_metadata"] = metadata
    return data


def recompute_intake_session(db: Session, session_id: int) -> dict[str, Any]:
    documents = [
        row_to_dict(row)
        for row in db.execute(
            text("SELECT * FROM intake_session_documents WHERE intake_session_id = :id ORDER BY id"),
            {"id": session_id},
        ).fetchall()
    ]
    extracted: dict[str, Any] = {}
    parser_results = []
    for document in documents:
        parser_result = document.get("parser_result") or {}
        parser_results.append(parser_result)
        extracted.update(flatten_parser_fields(parser_result))
    missing = [field for field in ["claimant", "claimant_lawyer", "invited_party", "claim_value", "matter", "object"] if not extracted.get(field)]
    review_status = "ready" if documents and len(missing) <= 3 else "pending"
    status = "ready" if review_status == "ready" else "in_review"
    db.execute(
        text(
            """
            UPDATE intake_sessions
            SET extracted_json = CAST(:extracted_json AS jsonb),
                parser_results = CAST(:parser_results AS jsonb),
                review_status = :review_status,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            """
        ),
        {
            "id": session_id,
            "extracted_json": json.dumps(extracted, ensure_ascii=False),
            "parser_results": json.dumps(parser_results, ensure_ascii=False),
            "review_status": review_status,
            "status": status,
        },
    )
    db.commit()
    return intake_session_detail(db, session_id)


def store_intake_document(db: Session, session_id: int, path: Path, original_filename: str) -> dict[str, Any]:
    text_content = read_intake_file_text(path)
    parser_result = get_registered_parser("mediation", text_content, original_filename).parse(text_content, original_filename)
    row = db.execute(
        text(
            """
            INSERT INTO intake_session_documents (
                intake_session_id, document_type, module, confidence, filename, file_path,
                mime_type, parser_result, extracted_json
            )
            VALUES (
                :intake_session_id, :document_type, :module, :confidence, :filename, :file_path,
                :mime_type, CAST(:parser_result AS jsonb), CAST(:extracted_json AS jsonb)
            )
            RETURNING *
            """
        ),
        {
            "intake_session_id": session_id,
            "document_type": parser_result.get("document_type") or "allegato_generico",
            "module": parser_result.get("module") or "",
            "confidence": float(parser_result.get("confidence_score") or 0),
            "filename": original_filename,
            "file_path": str(path),
            "mime_type": "application/octet-stream",
            "parser_result": json.dumps(parser_result, ensure_ascii=False),
            "extracted_json": json.dumps(parser_result.get("extracted_fields") or {}, ensure_ascii=False),
        },
    ).fetchone()
    return row_to_dict(row)


def next_case_number(db: Session, organization_id: int, case_type: str) -> str:
    year = db.execute(text("SELECT EXTRACT(YEAR FROM CURRENT_DATE)::int")).scalar_one()
    count = db.execute(
        text("SELECT COUNT(*) FROM cases WHERE organization_id = :organization_id AND case_type = :case_type AND EXTRACT(YEAR FROM created_at) = :year"),
        {"organization_id": organization_id, "case_type": case_type, "year": year},
    ).scalar_one()
    return f"CASE-{case_type.upper()}-{year}-{count + 1:04d}"


def add_case_timeline(db: Session, case_id: int, event_type: str, title: str, description: str | None = None):
    db.execute(
        text(
            """
            INSERT INTO case_timeline (case_id, event_type, title, description, created_by)
            VALUES (:case_id, :event_type, :title, :description, 'system')
            """
        ),
        {"case_id": case_id, "event_type": event_type, "title": title, "description": description},
    )


def create_case_record(db: Session, data: dict[str, Any]) -> dict[str, Any]:
    clean = {key: value for key, value in data.items() if value != ""}
    clean["organization_id"] = clean.get("organization_id") or 1
    clean["case_number"] = clean.get("case_number") or next_case_number(db, clean["organization_id"], clean.get("case_type") or "case")
    columns = ", ".join(clean.keys())
    placeholders = ", ".join([f":{key}" for key in clean.keys()])
    row = db.execute(text(f"INSERT INTO cases ({columns}) VALUES ({placeholders}) RETURNING *"), clean).fetchone()
    case = row_to_dict(row)
    add_case_timeline(db, case["id"], "case_created", "Fascicolo creato", case["title"])
    return case


def ensure_mediation_case(db: Session, mediation: dict[str, Any]) -> dict[str, Any]:
    existing = db.execute(
        text("SELECT * FROM cases WHERE related_entity_type = 'mediation' AND related_entity_id = :id LIMIT 1"),
        {"id": mediation["id"]},
    ).fetchone()
    if existing:
        return row_to_dict(existing)
    return create_case_record(
        db,
        {
            "organization_id": mediation.get("organization_id") or 1,
            "office_id": mediation.get("office_id"),
            "case_type": "mediation",
            "title": mediation.get("internal_number") or mediation.get("object") or f"Mediazione #{mediation['id']}",
            "status": mediation.get("status") or "aperto",
            "priority": "media",
            "opened_at": mediation.get("deposit_date"),
            "related_entity_id": mediation["id"],
            "related_entity_type": "mediation",
            "notes": "Fascicolo creato automaticamente dalla mediazione",
        },
    )


def insert_case_document(db: Session, case_id: int, metadata: dict[str, Any]) -> dict[str, Any]:
    row = db.execute(
        text(
            """
            INSERT INTO case_documents (
                case_id, document_type, filename, file_path, original_filename, file_size,
                mime_type, classification_status, extracted_text, extracted_json
            )
            VALUES (
                :case_id, :document_type, :filename, :file_path, :original_filename, :file_size,
                :mime_type, :classification_status, :extracted_text, CAST(:extracted_json AS jsonb)
            )
            RETURNING *
            """
        ),
        {
            "case_id": case_id,
            "document_type": metadata.get("document_type"),
            "filename": metadata.get("filename"),
            "file_path": metadata.get("file_path"),
            "original_filename": metadata.get("original_filename"),
            "file_size": metadata.get("file_size"),
            "mime_type": metadata.get("mime_type"),
            "classification_status": metadata.get("classification_status"),
            "extracted_text": metadata.get("extracted_text"),
            "extracted_json": json.dumps(metadata.get("extracted_json") or {}, ensure_ascii=False),
        },
    ).fetchone()
    return row_to_dict(row)


TEMPLATE_DEFAULTS = {
    "assunzione_incarico": (
        "Assunzione incarico mediatore",
        "templates/mediation/Assunzione incarico.ODT",
        "ASSUNZIONE INCARICO MEDIATORE\n\n"
        "Organismo: {{organization_name}} n. {{organization_registration_number}}\n"
        "Procedura: {{mediation_number}}\n"
        "Mediatore: {{mediator_name}}\n"
        "Primo incontro: {{first_meeting_date}} ore {{first_meeting_time}}\n"
        "Sede/modalita: {{first_meeting_location}}\n"
        "Materia: {{matter}}\nValore: {{claim_value}}\n",
    ),
    "dichiarazione_imparzialita": (
        "Dichiarazione imparzialita e riservatezza",
        "templates/mediation/Dichiarazione imparzialità.ODT",
        "DICHIARAZIONE DI IMPARZIALITA E RISERVATEZZA\n\n"
        "Organismo: {{organization_name}}\n"
        "Mediazione: {{mediation_number}}\n"
        "Parti: {{claimant}} / {{invited_party}}\n"
        "Mediatore: {{mediator_name}}\n",
    ),
    "convocazione_primo_incontro": (
        "Lettera convocazione primo incontro",
        "templates/mediation/Mediazione 070.2026 - Lettera_Primo_Incontro_Aggiornata-----per il 31.07 alle 11.15.docx",
        "LETTERA DI CONVOCAZIONE PRIMO INCONTRO\n\n"
        "Organismo: {{organization_name}} - {{office_address}}\n"
        "Procedura: {{mediation_number}} depositata il {{deposit_date}}\n"
        "Istante: {{claimant}} - Avv. {{claimant_lawyer}}\n"
        "Invitato: {{invited_party}} - Avv. {{invited_party_lawyer}}\n"
        "PEC: {{pec}}\n"
        "Oggetto: {{object}}\n"
        "Materia: {{matter}} - Valore: {{claim_value}}\n"
        "Primo incontro: {{first_meeting_date}} ore {{first_meeting_time}} presso {{first_meeting_location}}\n",
    ),
    "modulo_adesione": ("Modulo adesione", "templates/mediation/Modulo adesione.docx", "MODULO ADESIONE\n{{mediation_number}}\n"),
    "verbale_primo_incontro": ("Verbale primo incontro", "templates/mediation/Verbale primo incontro.docx", "VERBALE PRIMO INCONTRO\n{{mediation_number}}\n"),
    "verbale_negativo": ("Verbale negativo", "templates/mediation/Verbale negativo.docx", "VERBALE NEGATIVO\n{{mediation_number}}\n"),
    "verbale_accordo": ("Verbale accordo", "templates/mediation/Verbale accordo.docx", "VERBALE ACCORDO\n{{mediation_number}}\n"),
    "verbale_rinvio": ("Verbale rinvio", "templates/mediation/Verbale rinvio.docx", "VERBALE RINVIO\n{{mediation_number}}\n"),
    "chiusura_pratica": ("Chiusura pratica", "templates/mediation/Chiusura pratica.docx", "CHIUSURA PRATICA\n{{mediation_number}}\n"),
}


def ensure_template_files():
    for _, file_path, content in TEMPLATE_DEFAULTS.values():
        path = PROJECT_ROOT / file_path
        path.parent.mkdir(parents=True, exist_ok=True)
        if not path.exists():
            path.write_text(content, encoding="utf-8")


def split_person_name(name: str) -> tuple[str | None, str | None]:
    clean = re.sub(r"\b(dott\.?|dott\.ssa|avv\.?|sig\.?|sig\.ra)\b", "", name, flags=re.IGNORECASE).strip()
    parts = [part for part in clean.split() if part]
    if not parts:
        return None, None
    if len(parts) == 1:
        return parts[0], None
    return " ".join(parts[:-1]), parts[-1]


def get_or_create_contact(
    db: Session,
    organization_id: int,
    name: str | None,
    *,
    pec: str | None = None,
    email: str | None = None,
    is_lawyer: bool = False,
    is_mediator: bool = False,
    is_client: bool = False,
) -> int | None:
    if not name:
        return None
    clean_name = name.strip()
    if not clean_name:
        return None
    first_name, last_name = split_person_name(clean_name)
    data = {
        "organization_id": organization_id,
        "contact_type": "persona",
        "first_name": first_name,
        "last_name": last_name,
        "company_name": clean_name if not last_name else None,
        "pec": pec,
        "email": email,
        "is_lawyer": is_lawyer,
        "is_mediator": is_mediator,
        "is_client": is_client,
        "is_company": False,
        "notes": "Creato automaticamente da Document Intake",
    }
    existing = find_duplicate_contact(db, data)
    if existing:
        return existing["id"]
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(text(f"INSERT INTO contacts ({columns}) VALUES ({placeholders}) RETURNING id"), data).fetchone()
    return row.id


def create_mediation_people(db: Session, mediation_id: int, organization_id: int, data: dict[str, Any]) -> dict[str, Any]:
    created: dict[str, Any] = {"parties": [], "lawyers": []}
    claimant_id = get_or_create_contact(db, organization_id, data.get("claimant") or "Da verificare - Parte istante", pec=data.get("pec"), is_client=True)
    invited_id = get_or_create_contact(db, organization_id, data.get("invited_party") or "Da verificare - Parte invitata", is_client=True)
    party_ids: dict[str, int] = {}
    for role, contact_id in [("istante", claimant_id), ("invitata", invited_id)]:
        if not contact_id:
            continue
        row = db.execute(
            text(
                """
                INSERT INTO mediation_parties (mediation_id, contact_id, party_role, notes)
                VALUES (:mediation_id, :contact_id, :party_role, 'Creato da Document Intake')
                RETURNING *
                """
            ),
            {"mediation_id": mediation_id, "contact_id": contact_id, "party_role": role},
        ).fetchone()
        party = row_to_dict(row)
        party_ids[role] = party["id"]
        created["parties"].append(party)

    lawyer_specs = [
        (data.get("claimant_lawyer") or data.get("lawyer") or "Da verificare - Avvocato istante", party_ids.get("istante")),
        (data.get("invited_party_lawyer") or "Da verificare - Avvocato invitato", party_ids.get("invitata")),
    ]
    for lawyer_name, party_id in lawyer_specs:
        lawyer_id = get_or_create_contact(db, organization_id, lawyer_name, is_lawyer=True)
        if not lawyer_id or not party_id:
            continue
        row = db.execute(
            text(
                """
                INSERT INTO mediation_lawyers (mediation_id, party_id, lawyer_contact_id, power_of_attorney, notes)
                VALUES (:mediation_id, :party_id, :lawyer_contact_id, FALSE, 'Creato da Document Intake')
                RETURNING *
                """
            ),
            {"mediation_id": mediation_id, "party_id": party_id, "lawyer_contact_id": lawyer_id},
        ).fetchone()
        created["lawyers"].append(row_to_dict(row))
    return created


def create_first_mediation_session(db: Session, mediation_id: int, payload: IntakeCreatePayload):
    if not payload.first_meeting_date:
        return
    db.execute(
        text(
            """
            INSERT INTO mediation_sessions (
                mediation_id, session_number, session_date, start_time, mode, location_or_link, notes
            )
            VALUES (
                :mediation_id, 1, :session_date, :start_time, :mode, :location_or_link,
                'Primo incontro creato automaticamente da Document Intake'
            )
            """
        ),
        {
            "mediation_id": mediation_id,
            "session_date": payload.first_meeting_date,
            "start_time": payload.first_meeting_time or None,
            "mode": payload.first_meeting_mode or "presenza",
            "location_or_link": payload.first_meeting_location,
        },
    )


def create_mediator_assignment_draft(db: Session, mediation_id: int, mediation: dict[str, Any], generated_documents: list[dict[str, Any]] | None = None) -> dict[str, Any] | None:
    mediator_name = mediator_display_name(db, mediation) if mediation.get("mediator_id") else None
    if not mediation.get("mediator_id") and not mediator_name:
        return None
    existing = db.execute(
        text("SELECT * FROM mediator_assignments WHERE mediation_id = :mediation_id ORDER BY id DESC LIMIT 1"),
        {"mediation_id": mediation_id},
    ).fetchone()
    document_payload = json.dumps(generated_documents or [], ensure_ascii=False)
    if existing:
        row = db.execute(
            text(
                """
                UPDATE mediator_assignments
                SET mediator_contact_id = :mediator_contact_id,
                    mediator_name = :mediator_name,
                    status = CASE WHEN status IS NULL OR status = 'draft' THEN 'nomina_da_inviare' ELSE status END,
                    generated_documents = CAST(:generated_documents AS jsonb),
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                RETURNING *
                """
            ),
            {
                "id": existing.id,
                "mediator_contact_id": mediation.get("mediator_id"),
                "mediator_name": mediator_name,
                "generated_documents": document_payload,
            },
        ).fetchone()
    else:
        row = db.execute(
            text(
                """
                INSERT INTO mediator_assignments (
                    mediation_id, mediator_contact_id, mediator_name, status, generated_documents, notes
                )
                VALUES (
                    :mediation_id, :mediator_contact_id, :mediator_name, 'nomina_da_inviare',
                    CAST(:generated_documents AS jsonb), 'Nomina predisposta automaticamente da ALPHA-002'
                )
                RETURNING *
                """
            ),
            {
                "mediation_id": mediation_id,
                "mediator_contact_id": mediation.get("mediator_id"),
                "mediator_name": mediator_name,
                "generated_documents": document_payload,
            },
        ).fetchone()
    return row_to_dict(row)


def document_placeholders(db: Session, mediation_id: int, payload: IntakeCreatePayload | None = None) -> dict[str, str]:
    mediation = mediation_detail(db, mediation_id)
    organization = db.execute(
        text("SELECT * FROM organizations WHERE id = :id"),
        {"id": mediation.get("organization_id") or 1},
    ).fetchone()
    office = db.execute(text("SELECT * FROM offices WHERE id = :id"), {"id": mediation.get("office_id")}).fetchone() if mediation.get("office_id") else None
    org = row_to_dict(organization) if organization else {}
    off = row_to_dict(office) if office else {}
    parties = {party.get("party_role"): display_contact_name(party) for party in mediation.get("parties", [])}
    lawyers = [display_contact_name(lawyer) for lawyer in mediation.get("lawyers", [])]
    first_session = mediation.get("sessions", [None])[0] if mediation.get("sessions") else {}
    payload_data = payload.extracted_data if payload else {}
    meeting_mode = str((payload.first_meeting_mode if payload else None) or first_session.get("mode") or "presenza")
    meeting_location = str((payload.first_meeting_location if payload else None) or first_session.get("location_or_link") or off.get("address") or "")
    webex_link = meeting_location if meeting_mode in {"telematica", "mista"} and meeting_location.startswith("http") else ""
    return {
        "organization_name": str(org.get("name") or mediation.get("organization_name") or ""),
        "organization_registration_number": str(org.get("registration_number") or ""),
        "registration_number": str(org.get("registration_number") or ""),
        "office_name": str(off.get("name") or mediation.get("office_name") or ""),
        "office_address": ", ".join([value for value in [off.get("address"), off.get("city"), off.get("province")] if value]),
        "office_city": str(off.get("city") or ""),
        "office_phone": str(off.get("phone") or org.get("phone") or ""),
        "office_email": str(off.get("email") or org.get("email") or ""),
        "office_pec": str(off.get("pec") or org.get("pec") or ""),
        "mediation_number": str(mediation.get("internal_number") or ""),
        "deposit_date": str(mediation.get("deposit_date") or ""),
        "deposit_time": str(payload_data.get("deposit_time") or ""),
        "claimant": str(payload_data.get("claimant") or parties.get("istante") or ""),
        "claimant_lawyer": str(payload_data.get("claimant_lawyer") or payload_data.get("lawyer") or (lawyers[0] if lawyers else "")),
        "invited_party": str(payload_data.get("invited_party") or parties.get("invitata") or ""),
        "invited_party_lawyer": str(payload_data.get("invited_party_lawyer") or (lawyers[1] if len(lawyers) > 1 else "")),
        "mediator_name": str((payload.mediator_name if payload else None) or mediation.get("mediator_id") or "Da confermare"),
        "first_meeting_date": str((payload.first_meeting_date if payload else None) or first_session.get("session_date") or mediation.get("first_meeting_date") or ""),
        "first_meeting_time": str((payload.first_meeting_time if payload else None) or first_session.get("start_time") or ""),
        "first_meeting_location": meeting_location,
        "meeting_mode": meeting_mode,
        "meeting_location": meeting_location,
        "webex_link": webex_link,
        "pec": str(payload_data.get("pec") or org.get("pec") or ""),
        "claim_value": str(mediation.get("claim_value") or ""),
        "startup_expenses": str(mediation.get("calculated_initial_expense") or ""),
        "first_meeting_expenses": str(mediation.get("calculated_first_meeting_fee") or ""),
        "total_initial_expenses": str(mediation.get("calculated_total") or ""),
        "initial_expenses": str(mediation.get("calculated_total") or mediation.get("calculated_initial_expense") or ""),
        "iban": str(off.get("iban") or org.get("iban") or "Da indicare"),
        "matter": str(mediation.get("matter") or ""),
        "object": str(mediation.get("object") or ""),
        "reasons": str(mediation.get("reasons") or ""),
    }


def display_contact_name(row: dict[str, Any]) -> str:
    return row.get("company_name") or " ".join([part for part in [row.get("first_name"), row.get("last_name")] if part]).strip()


def render_template(content: str, placeholders: dict[str, str]) -> str:
    rendered = content
    for key, value in placeholders.items():
        rendered = rendered.replace("{{" + key + "}}", value or "")
    return rendered


def render_docx_template(template_path: Path, output_path: Path, placeholders: dict[str, str], fallback_text: str) -> None:
    replacements = {f"{{{{{key}}}}}": str(value or "") for key, value in placeholders.items()}
    try:
        with zipfile.ZipFile(template_path, "r") as source:
            with zipfile.ZipFile(output_path, "w", zipfile.ZIP_DEFLATED) as target:
                for item in source.infolist():
                    content = source.read(item.filename)
                    if item.filename.endswith(".xml"):
                        text_content = content.decode("utf-8", errors="ignore")
                        for token, value in replacements.items():
                            text_content = text_content.replace(token, xml_escape(value))
                        content = text_content.encode("utf-8")
                    target.writestr(item, content)
        return
    except zipfile.BadZipFile:
        pass

    document_xml = "".join(
        f"<w:p><w:r><w:t>{xml_escape(line)}</w:t></w:r></w:p>"
        for line in fallback_text.splitlines()
    )
    with zipfile.ZipFile(output_path, "w", zipfile.ZIP_DEFLATED) as target:
        target.writestr("[Content_Types].xml", '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>')
        target.writestr("_rels/.rels", '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>')
        target.writestr("word/document.xml", f'<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>{document_xml}<w:sectPr/></w:body></w:document>')


def write_simple_pdf(path: Path, lines: list[str]) -> None:
    escaped_lines = [line.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)") for line in lines]
    text_commands = ["BT", "/F1 10 Tf", "50 790 Td"]
    for index, line in enumerate(escaped_lines[:45]):
        if index:
            text_commands.append("0 -16 Td")
        text_commands.append(f"({line}) Tj")
    text_commands.append("ET")
    stream = "\n".join(text_commands).encode("latin-1", errors="replace")
    objects = [
        b"<< /Type /Catalog /Pages 2 0 R >>",
        b"<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
        b"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>",
        b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        b"<< /Length " + str(len(stream)).encode() + b" >>\nstream\n" + stream + b"\nendstream",
    ]
    content = bytearray(b"%PDF-1.4\n")
    offsets = []
    for number, obj in enumerate(objects, start=1):
        offsets.append(len(content))
        content.extend(f"{number} 0 obj\n".encode())
        content.extend(obj)
        content.extend(b"\nendobj\n")
    xref_offset = len(content)
    content.extend(f"xref\n0 {len(objects) + 1}\n0000000000 65535 f \n".encode())
    for offset in offsets:
        content.extend(f"{offset:010d} 00000 n \n".encode())
    content.extend(f"trailer << /Size {len(objects) + 1} /Root 1 0 R >>\nstartxref\n{xref_offset}\n%%EOF".encode())
    path.write_bytes(content)


def generate_first_meeting_notice(db: Session, mediation_id: int) -> dict[str, Any]:
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    placeholders = document_placeholders(db, mediation_id)
    output_dir = UPLOAD_ROOT / "generated" / "mediations" / str(mediation_id)
    output_dir.mkdir(parents=True, exist_ok=True)
    template_path = PROJECT_ROOT / TEMPLATE_DEFAULTS["convocazione_primo_incontro"][1]
    fallback_text = TEMPLATE_DEFAULTS["convocazione_primo_incontro"][2]
    rendered_text = render_template(fallback_text, placeholders)
    base_name = f"{safe_filename(mediation.get('internal_number') or str(mediation_id))}_convocazione_primo_incontro"
    docx_path = output_dir / f"{base_name}.docx"
    pdf_path = output_dir / f"{base_name}.pdf"
    render_docx_template(template_path, docx_path, placeholders, rendered_text)
    write_simple_pdf(pdf_path, rendered_text.splitlines())

    generated_docx = db.execute(
        text(
            """
            INSERT INTO generated_documents (mediation_id, template_id, document_type, filename, file_path)
            VALUES (:mediation_id, NULL, 'convocazione_primo_incontro', :filename, :file_path)
            RETURNING *
            """
        ),
        {"mediation_id": mediation_id, "filename": docx_path.name, "file_path": str(docx_path)},
    ).fetchone()
    db.execute(
        text(
            """
            INSERT INTO mediation_documents (mediation_id, document_type, filename, file_path, notes)
            VALUES (:mediation_id, 'convocazione_primo_incontro', :filename, :file_path, 'Convocazione primo incontro generata automaticamente')
            """
        ),
        {"mediation_id": mediation_id, "filename": docx_path.name, "file_path": str(docx_path)},
    )
    case_docx = insert_case_document(
        db,
        case["id"],
        {
            "document_type": "convocazione_primo_incontro",
            "filename": docx_path.name,
            "file_path": str(docx_path),
            "original_filename": docx_path.name,
            "file_size": docx_path.stat().st_size,
            "mime_type": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "classification_status": "generated",
            "extracted_text": None,
            "extracted_json": {"source": "MED-019"},
        },
    )
    case_pdf = insert_case_document(
        db,
        case["id"],
        {
            "document_type": "convocazione_primo_incontro_pdf",
            "filename": pdf_path.name,
            "file_path": str(pdf_path),
            "original_filename": pdf_path.name,
            "file_size": pdf_path.stat().st_size,
            "mime_type": "application/pdf",
            "classification_status": "generated",
            "extracted_text": None,
            "extracted_json": {"source": "MED-019"},
        },
    )
    add_case_timeline(db, case["id"], "first_meeting_notice_generated", "Convocazione primo incontro generata", docx_path.name)
    add_case_timeline(db, case["id"], "notice.generated", "Convocazione generata", docx_path.name)
    next_action = calculate_next_action(case["id"], next_action_context_for_mediation(mediation_detail(db, mediation_id)))
    add_case_timeline(db, case["id"], "next_action.updated", "Prossima azione aggiornata", next_action["title"])
    audit(db, "generated_documents", mediation_id, "generate_first_meeting_notice", {"docx": str(docx_path), "pdf": str(pdf_path)}, organization_id=mediation.get("organization_id") or 1)
    db.commit()
    return {
        "generated_document": row_to_dict(generated_docx),
        "case_documents": [case_docx, case_pdf],
        "docx_path": str(docx_path),
        "pdf_path": str(pdf_path),
        "mediation": mediation_detail(db, mediation_id),
    }


def mediator_display_name(db: Session, mediation: dict[str, Any]) -> str:
    mediator_id = mediation.get("mediator_id")
    if not mediator_id:
        return "Mediatore da confermare"
    row = db.execute(text("SELECT * FROM contacts WHERE id = :id"), {"id": mediator_id}).fetchone()
    if not row:
        return f"Mediatore #{mediator_id}"
    return display_contact_name(row_to_dict(row)) or f"Mediatore #{mediator_id}"


def create_mediator_appointment_package(db: Session, mediation_id: int, reminder: bool = False) -> dict[str, Any]:
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    mediator_name = mediator_display_name(db, mediation)
    generated = generate_initial_documents_for_mediation(
        db,
        mediation_id,
        template_keys=["assunzione_incarico", "dichiarazione_imparzialita"],
    )
    signature_link = f"https://nexus.local/sign/mediator-assignment/{mediation_id}"
    existing = db.execute(
        text("SELECT * FROM mediator_assignments WHERE mediation_id = :mediation_id ORDER BY id DESC LIMIT 1"),
        {"mediation_id": mediation_id},
    ).fetchone()
    document_payload = json.dumps(generated, ensure_ascii=False)
    if existing:
        assignment = db.execute(
            text(
                """
                UPDATE mediator_assignments
                SET mediator_contact_id = :mediator_contact_id,
                    mediator_name = :mediator_name,
                    email_sent_at = CURRENT_TIMESTAMP,
                    reminder_sent_at = CASE WHEN :reminder THEN CURRENT_TIMESTAMP ELSE reminder_sent_at END,
                    status = CASE WHEN status = 'accepted' THEN status ELSE 'sent_waiting_signature' END,
                    generated_documents = CAST(:generated_documents AS jsonb),
                    signature_link = :signature_link,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                RETURNING *
                """
            ),
            {
                "id": existing.id,
                "mediator_contact_id": mediation.get("mediator_id"),
                "mediator_name": mediator_name,
                "generated_documents": document_payload,
                "signature_link": signature_link,
                "reminder": reminder,
            },
        ).fetchone()
    else:
        assignment = db.execute(
            text(
                """
                INSERT INTO mediator_assignments (
                    mediation_id, mediator_contact_id, mediator_name, email_sent_at,
                    status, generated_documents, signature_link, notes
                )
                VALUES (
                    :mediation_id, :mediator_contact_id, :mediator_name, CURRENT_TIMESTAMP,
                    'sent_waiting_signature', CAST(:generated_documents AS jsonb), :signature_link,
                    'Pacchetto nomina generato automaticamente'
                )
                RETURNING *
                """
            ),
            {
                "mediation_id": mediation_id,
                "mediator_contact_id": mediation.get("mediator_id"),
                "mediator_name": mediator_name,
                "generated_documents": document_payload,
                "signature_link": signature_link,
            },
        ).fetchone()

    subject = f"Nomina mediatore - Procedura {mediation.get('internal_number') or mediation_id}"
    body = (
        f"Gentile {mediator_name},\n\n"
        "si trasmette il pacchetto di nomina con assunzione incarico e dichiarazione di imparzialita.\n"
        f"Link firma: {signature_link}\n"
    )
    email = db.execute(
        text(
            """
            INSERT INTO email_messages (
                mediation_id, assignment_id, recipient_email, subject, body_text, status, sent_at
            )
            VALUES (:mediation_id, :assignment_id, NULL, :subject, :body_text, 'sent_placeholder', CURRENT_TIMESTAMP)
            RETURNING *
            """
        ),
        {"mediation_id": mediation_id, "assignment_id": assignment.id, "subject": subject, "body_text": body},
    ).fetchone()
    add_case_timeline(
        db,
        case["id"],
        "mediator_assignment_sent" if not reminder else "mediator_assignment_reminder",
        "Pacchetto nomina mediatore inviato" if not reminder else "Sollecito nomina mediatore inviato",
        mediator_name,
    )
    if not reminder:
        add_case_timeline(db, case["id"], "mediator.assigned", "Mediatore nominato", mediator_name)
        add_case_timeline(db, case["id"], "email_sent", "Email inviata", subject)
        add_case_timeline(db, case["id"], "signature_requested", "Firma richiesta", signature_link)
    next_action = calculate_next_action(case["id"], next_action_context_for_mediation(mediation_detail(db, mediation_id)))
    add_case_timeline(db, case["id"], "next_action.updated", "Prossima azione aggiornata", next_action["title"])
    audit(db, "mediator_assignments", assignment.id, "send_mediator_assignment", {"reminder": reminder, "documents": generated}, organization_id=mediation.get("organization_id") or 1)
    db.commit()
    return {
        "assignment": row_to_dict(assignment),
        "email": row_to_dict(email),
        "generated_documents": generated,
        "mediation": mediation_detail(db, mediation_id),
    }


def generate_initial_documents_for_mediation(
    db: Session,
    mediation_id: int,
    payload: IntakeCreatePayload | None = None,
    template_keys: list[str] | None = None,
) -> list[dict[str, Any]]:
    ensure_template_files()
    keys = template_keys or ["assunzione_incarico", "dichiarazione_imparzialita", "convocazione_primo_incontro"]
    mediation = mediation_detail(db, mediation_id)
    placeholders = document_placeholders(db, mediation_id, payload)
    output_dir = UPLOAD_ROOT / "generated" / "mediations" / str(mediation_id)
    output_dir.mkdir(parents=True, exist_ok=True)
    generated = []

    for key in keys:
        template = db.execute(
            text(
                """
                SELECT *
                FROM document_templates
                WHERE organization_id = :organization_id AND template_key = :template_key AND active = TRUE
                LIMIT 1
                """
            ),
            {"organization_id": mediation.get("organization_id") or 1, "template_key": key},
        ).fetchone()
        if not template and key in TEMPLATE_DEFAULTS:
            template_name, file_path, _ = TEMPLATE_DEFAULTS[key]
            template = db.execute(
                text(
                    """
                    INSERT INTO document_templates (organization_id, template_key, template_name, file_path, active)
                    VALUES (:organization_id, :template_key, :template_name, :file_path, TRUE)
                    ON CONFLICT (organization_id, template_key)
                    DO UPDATE SET template_name = EXCLUDED.template_name, file_path = EXCLUDED.file_path, active = TRUE
                    RETURNING *
                    """
                ),
                {
                    "organization_id": mediation.get("organization_id") or 1,
                    "template_key": key,
                    "template_name": template_name,
                    "file_path": file_path,
                },
            ).fetchone()
        if not template:
            continue
        template_data = row_to_dict(template)
        template_path = PROJECT_ROOT / template_data["file_path"]
        if not template_path.exists() and key in TEMPLATE_DEFAULTS:
            template_path.write_text(TEMPLATE_DEFAULTS[key][2], encoding="utf-8")
        content = template_path.read_text(encoding="utf-8", errors="ignore") if template_path.exists() else ""
        rendered = render_template(content, placeholders)
        filename = f"{safe_filename(mediation.get('internal_number') or str(mediation_id))}_{key}.txt"
        output_path = output_dir / filename
        output_path.write_text(rendered, encoding="utf-8")
        row = db.execute(
            text(
                """
                INSERT INTO generated_documents (mediation_id, template_id, document_type, filename, file_path)
                VALUES (:mediation_id, :template_id, :document_type, :filename, :file_path)
                RETURNING *
                """
            ),
            {
                "mediation_id": mediation_id,
                "template_id": template_data["id"],
                "document_type": key,
                "filename": filename,
                "file_path": str(output_path),
            },
        ).fetchone()
        db.execute(
            text(
                """
                INSERT INTO mediation_documents (mediation_id, document_type, filename, file_path, notes)
                VALUES (:mediation_id, :document_type, :filename, :file_path, 'Documento generato automaticamente')
                """
            ),
            {"mediation_id": mediation_id, "document_type": key, "filename": filename, "file_path": str(output_path)},
        )
        generated.append(row_to_dict(row))
    return generated


def case_detail(db: Session, case_id: int) -> dict[str, Any]:
    row = db.execute(
        text(
            """
            SELECT c.*, o.name AS office_name
            FROM cases c
            LEFT JOIN offices o ON o.id = c.office_id
            WHERE c.id = :id
            """
        ),
        {"id": case_id},
    ).fetchone()
    if not row:
        raise HTTPException(status_code=404, detail="Fascicolo non trovato")
    return row_to_dict(row)


@app.get("/health")
def health():
    return {"status": "ok"}


@app.get("/health/full")
def health_full(db: Session = Depends(get_db)):
    database_status = "online"
    try:
        db.execute(text("SELECT 1"))
    except Exception:
        database_status = "offline"
    return {
        "status": "ok" if database_status == "online" else "degraded",
        "database": database_status,
        "kernel": get_kernel_status()["kernel"],
        "parser_registry": "online",
        "intake_engine": "online",
        "version": "0.1.1-alpha-recovery",
    }


@app.get("/kernel/status")
def kernel_status():
    return get_kernel_status()


@app.post("/kernel/events/test")
def kernel_events_test(payload: KernelEventTestPayload):
    if not payload.event_type.strip():
        raise ValidationError("event_type obbligatorio", {"field": "event_type"})
    event_payload = dict(payload.payload or {})
    if payload.case_id is not None:
        event_payload["case_id"] = payload.case_id
    event = publish_kernel_event(payload.event_type, event_payload)
    handled = handle_kernel_event(event)
    return {"event": event, "result": handled}


@app.post("/automation/run/{case_id}")
def run_automation(case_id: int, payload: RecordPayload | None = None, db: Session = Depends(get_db)):
    context = dict((payload.data if payload else {}) or {})
    case = get_case_detail(case_id, db)
    context.setdefault("case_id", case_id)
    context.setdefault("event_type", (case.get("timeline") or [{}])[-1].get("event_type") if case.get("timeline") else "case.created")
    result = AUTOMATION_ENGINE.execute(case_id, context)
    add_case_timeline(db, case_id, "automation.executed", "Automazione eseguita", result.get("event_type"))
    if result["status"] == "executed":
        for rule_result in result.get("results", []):
            add_case_timeline(db, case_id, "workflow.changed", "Workflow aggiornato da automazione", rule_result.get("rule_key"))
            for action in rule_result.get("actions", []):
                if action.get("document_type"):
                    add_case_timeline(db, case_id, "document.generated", "Documento preparato da automazione", action["document_type"])
        add_case_timeline(db, case_id, "next_action.updated", "Prossima azione aggiornata", "Automation Engine")
    audit(db, "automation", case_id, "audit_ai", result, organization_id=case.get("organization_id") or 1)
    audit(db, "automation", case_id, "audit_workflow", result, organization_id=case.get("organization_id") or 1)
    audit(db, "automation", case_id, "audit_automation", result, organization_id=case.get("organization_id") or 1)
    db.commit()
    return result


@app.get("/automation/status/{case_id}")
def automation_status(case_id: int):
    return AUTOMATION_ENGINE.status(case_id)


@app.get("/automation/logs/{case_id}")
def automation_logs(case_id: int):
    return {"case_id": case_id, "logs": [log for log in AUTOMATION_ENGINE.logs if log.get("case_id") == case_id]}


@app.get("/automation/dashboard/stats")
def automation_dashboard_stats():
    today = datetime.now().date().isoformat()
    today_logs = [log for log in AUTOMATION_ENGINE.logs if str(log.get("executed_at") or log.get("scheduled_at") or "").startswith(today)]
    return {
        "executed_today": len([log for log in today_logs if log.get("status") == "executed"]),
        "pending": len([log for log in AUTOMATION_ENGINE.logs if log.get("status") == "scheduled"]),
        "error": len([log for log in AUTOMATION_ENGINE.logs if log.get("status") == "error"]),
        "manual": len([log for log in AUTOMATION_ENGINE.logs if log.get("status") == "manual"]),
    }


@app.get("/modules")
def root_modules():
    return {"modules": APP_MODULES}


@app.post("/parsers/mediation/test")
def test_mediation_parser(payload: ParserTestPayload):
    return MediationParser().parse(payload.text, payload.filename)


@app.post("/parsers/classify")
def classify_parser_payload(payload: ParserTestPayload):
    return classify_parser_document(payload.text, payload.filename)


@app.get("/organizations")
def list_organizations(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT * FROM organizations WHERE active = TRUE ORDER BY name")).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/offices")
def list_offices(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text("SELECT * FROM offices WHERE organization_id = :organization_id AND active = TRUE ORDER BY name"),
        {"organization_id": organization_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/contacts")
def list_contacts(q: str | None = Query(default=None), db: Session = Depends(get_db)):
    params: dict[str, Any] = {}
    where = ""
    if q:
        params["q"] = f"%{q}%"
        where = """
        WHERE first_name ILIKE :q
           OR last_name ILIKE :q
           OR company_name ILIKE :q
           OR email ILIKE :q
           OR pec ILIKE :q
           OR fiscal_code ILIKE :q
           OR vat_number ILIKE :q
        """
    rows = db.execute(
        text(f"SELECT * FROM contacts {where} ORDER BY updated_at DESC, id DESC"),
        params,
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/contacts/{contact_id}")
def get_contact(contact_id: int, db: Session = Depends(get_db)):
    row = db.execute(text("SELECT * FROM contacts WHERE id = :id"), {"id": contact_id}).fetchone()
    if not row:
        raise HTTPException(status_code=404, detail="Contatto non trovato")
    return row_to_dict(row)


@app.post("/contacts", status_code=201)
def create_contact(payload: ContactPayload, db: Session = Depends(get_db)):
    data = normalize_contact(payload)
    duplicate = find_duplicate_contact(db, data)
    if duplicate:
        raise HTTPException(
            status_code=409,
            detail=f"Contatto gia presente nell'anagrafica master con ID {duplicate['id']}",
        )

    insert_data = {"organization_id": 1, **data}
    columns = ", ".join(insert_data.keys())
    placeholders = ", ".join([f":{key}" for key in insert_data.keys()])
    row = db.execute(
        text(f"INSERT INTO contacts ({columns}) VALUES ({placeholders}) RETURNING *"),
        insert_data,
    ).fetchone()
    db.commit()
    return row_to_dict(row)


@app.put("/contacts/{contact_id}")
def update_contact(contact_id: int, payload: ContactPayload, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM contacts WHERE id = :id"), {"id": contact_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Contatto non trovato")

    data = normalize_contact(payload)
    duplicate = find_duplicate_contact(db, data, exclude_id=contact_id)
    if duplicate:
        raise HTTPException(
            status_code=409,
            detail=f"Contatto gia presente nell'anagrafica master con ID {duplicate['id']}",
        )

    assignments = ", ".join([f"{key} = :{key}" for key in data.keys()])
    data["id"] = contact_id
    row = db.execute(
        text(
            f"""
            UPDATE contacts
            SET {assignments}, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            RETURNING *
            """
        ),
        data,
    ).fetchone()
    db.commit()
    return row_to_dict(row)


@app.delete("/contacts/{contact_id}", status_code=204)
def delete_contact(contact_id: int, db: Session = Depends(get_db)):
    result = db.execute(text("DELETE FROM contacts WHERE id = :id"), {"id": contact_id})
    db.commit()
    if result.rowcount == 0:
        raise HTTPException(status_code=404, detail="Contatto non trovato")
    return None


@app.get("/mediations")
def list_mediations(
    organization_id: int = Query(default=1),
    q: str | None = Query(default=None),
    db: Session = Depends(get_db),
):
    params: dict[str, Any] = {"organization_id": organization_id}
    where = "WHERE m.organization_id = :organization_id"
    if q:
        params["q"] = f"%{q}%"
        where += """
        AND (
            m.internal_number ILIKE :q
            OR m.dgstat_number ILIKE :q
            OR m.matter ILIKE :q
            OR m.object ILIKE :q
            OR m.status ILIKE :q
            OR m.outcome ILIKE :q
        )
        """
    rows = db.execute(
        text(
            f"""
            SELECT m.id, m.organization_id, m.office_id, m.internal_number, m.dgstat_number,
                   m.year, m.quarter, m.deposit_date, m.first_meeting_date, m.closing_date,
                   m.status, m.outcome, m.mediation_type, m.matter, m.submatter,
                   m.claim_value, m.object, m.reasons, m.mediator_id, m.tariff_id,
                   m.tariff_row_id, m.calculated_initial_expense,
                   m.calculated_first_meeting_fee, m.calculated_further_fee,
                   m.calculated_vat, m.calculated_total, m.notes, m.created_at,
                   m.updated_at, o.name AS office_name, t.name AS tariff_name
            FROM mediations m
            LEFT JOIN offices o ON o.id = m.office_id
            LEFT JOIN mediation_tariffs t ON t.id = m.tariff_id
            {where}
            ORDER BY m.deposit_date DESC NULLS LAST, m.id DESC
            LIMIT 300
            """
        ),
        params,
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/mediations/intake/pdf")
async def intake_mediation_pdf(
    organization_id: int = Query(default=1),
    office_id: int | None = Query(default=None),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
):
    if not (file.filename or "").lower().endswith(".pdf"):
        raise HTTPException(status_code=400, detail="Caricare un file PDF")
    target_path = await save_upload(file, UPLOAD_ROOT / "intakes" / "pdf")
    extracted = extract_mediation_data_from_pdf(target_path)
    try:
        from .pdf_parser import extract_pdf_text

        pdf_text = extract_pdf_text(target_path)
    except Exception:
        pdf_text = ""
    parser_result = get_registered_parser("mediation", pdf_text, file.filename or target_path.name).parse(pdf_text, file.filename or target_path.name)
    extracted.update(parser_result.get("extracted_fields", {}))
    extracted["document_type"] = parser_result.get("document_type")
    extracted["parser_result"] = parser_result
    extracted["source_file_path"] = str(target_path)
    intake = create_document_intake(db, organization_id, office_id, "pdf", file.filename or target_path.name, extracted)
    return {"intake_id": intake["id"], "extracted_data": extracted, "status": intake["status"]}


@app.post("/mediations/intake/zip")
async def intake_mediation_zip(
    organization_id: int = Query(default=1),
    office_id: int | None = Query(default=None),
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
):
    if not (file.filename or "").lower().endswith(".zip"):
        raise HTTPException(status_code=400, detail="Caricare un file ZIP")
    intake_dir = UPLOAD_ROOT / "intakes" / "zip" / uuid4().hex
    zip_path = await save_upload(file, intake_dir)
    extract_dir = intake_dir / "extracted"
    extract_dir.mkdir(parents=True, exist_ok=True)

    with zipfile.ZipFile(zip_path) as archive:
        archive.extractall(extract_dir)

    files = [
        classified_file(path, extract_dir)
        for path in extract_dir.rglob("*")
        if path.is_file()
    ]
    extracted = {"zip_file_path": str(zip_path), "files": files}
    intake = create_document_intake(db, organization_id, office_id, "zip", file.filename or zip_path.name, extracted)
    return {"intake_id": intake["id"], "files": files, "status": intake["status"]}


@app.get("/intake-sessions/stats")
def intake_session_stats(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT status, COUNT(*) AS count
            FROM intake_sessions
            WHERE organization_id = :organization_id
            GROUP BY status
            """
        ),
        {"organization_id": organization_id},
    ).fetchall()
    counts = {row.status: row.count for row in rows}
    return {
        "in_review": counts.get("in_review", 0),
        "ready": counts.get("ready", 0),
        "created": counts.get("created", 0),
        "error": counts.get("error", 0),
    }


@app.get("/intake-sessions")
def list_intake_sessions(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text("SELECT * FROM intake_sessions WHERE organization_id = :organization_id ORDER BY created_at DESC, id DESC LIMIT 100"),
        {"organization_id": organization_id},
    ).fetchall()
    return [intake_session_detail(db, row.id) for row in rows]


@app.post("/intake-sessions/upload", status_code=201)
async def upload_intake_session(
    organization_id: int = Query(default=1),
    office_id: int | None = Query(default=None),
    files: list[UploadFile] = File(...),
    db: Session = Depends(get_db),
):
    session = db.execute(
        text(
            """
            INSERT INTO intake_sessions (organization_id, office_id, status, review_status)
            VALUES (:organization_id, :office_id, 'in_review', 'pending')
            RETURNING *
            """
        ),
        {"organization_id": organization_id, "office_id": office_id},
    ).fetchone()
    session_id = session.id
    target_dir = UPLOAD_ROOT / "intake_sessions" / str(session_id)
    target_dir.mkdir(parents=True, exist_ok=True)
    allowed = {".pdf", ".docx", ".odt", ".zip", ".jpg", ".jpeg", ".png", ".eml", ".msg"}
    for file in files:
        suffix = Path(file.filename or "").suffix.lower()
        if suffix not in allowed:
            continue
        saved = await save_upload(file, target_dir)
        if suffix == ".zip":
            extract_dir = target_dir / f"zip_{uuid4().hex}"
            extract_dir.mkdir(parents=True, exist_ok=True)
            with zipfile.ZipFile(saved) as archive:
                archive.extractall(extract_dir)
            for path in extract_dir.rglob("*"):
                if path.is_file() and path.suffix.lower() in allowed - {".zip"}:
                    store_intake_document(db, session_id, path, path.name)
        else:
            store_intake_document(db, session_id, saved, file.filename or saved.name)
    return recompute_intake_session(db, session_id)


@app.get("/intake-sessions/{session_id}")
def get_intake_session(session_id: int, db: Session = Depends(get_db)):
    return intake_session_detail(db, session_id)


@app.post("/intake-sessions/{session_id}/review-confirm")
def confirm_intake_session(session_id: int, payload: IntakeSessionCreatePayload, db: Session = Depends(get_db)):
    session = intake_session_detail(db, session_id)
    extracted = {**(session.get("extracted_json") or {}), **payload.extracted_data}
    db.execute(
        text(
            """
            UPDATE intake_sessions
            SET extracted_json = CAST(:extracted_json AS jsonb),
                review_status = 'confirmed',
                status = 'ready',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            """
        ),
        {"id": session_id, "extracted_json": json.dumps(extracted, ensure_ascii=False)},
    )
    db.commit()
    return intake_session_detail(db, session_id)


@app.post("/intake-sessions/{session_id}/create-proceeding", status_code=201)
def create_proceeding_from_intake_session(session_id: int, payload: IntakeSessionCreatePayload, db: Session = Depends(get_db)):
    session = intake_session_detail(db, session_id)
    extracted = {**(session.get("extracted_json") or {}), **payload.extracted_data}
    intake_payload = IntakeCreatePayload(
        organization_id=session.get("organization_id") or 1,
        office_id=session.get("office_id"),
        extracted_data=extracted,
        documents=session.get("documents") or [],
    )
    mediation = create_full_mediation_from_intake(intake_payload, db, generate_documents=False)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "ai_intake_created", "Procedimento creato da Nexus AI Intake", f"Intake session #{session_id}")
    db.execute(
        text(
            """
            UPDATE intake_sessions
            SET status = 'created',
                review_status = 'confirmed',
                created_case_id = :case_id,
                created_mediation_id = :mediation_id,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            """
        ),
        {"id": session_id, "case_id": case["id"], "mediation_id": mediation["id"]},
    )
    audit(db, "intake_sessions", session_id, "ai_intake_create_proceeding", extracted, organization_id=session.get("organization_id") or 1)
    db.commit()
    return {"intake_session": intake_session_detail(db, session_id), "case": case, "mediation": mediation_detail(db, mediation["id"])}


@app.post("/intake/{session_id}/confirm-and-create-mediation", status_code=201)
def confirm_intake_and_create_mediation(session_id: int, payload: IntakeSessionCreatePayload, db: Session = Depends(get_db)):
    try:
        session = intake_session_detail(db, session_id)
        extracted = {**(session.get("extracted_json") or {}), **payload.extracted_data}
        office_id = session.get("office_id") or resolve_office_id_from_data(db, session.get("organization_id") or 1, extracted)
        intake_payload = IntakeCreatePayload(
            organization_id=session.get("organization_id") or 1,
            office_id=office_id,
            extracted_data=extracted,
            documents=session.get("documents") or [],
            mediator_name=extracted.get("mediator_name"),
            first_meeting_date=extracted.get("first_meeting_date"),
            first_meeting_time=extracted.get("first_meeting_time"),
            first_meeting_location=extracted.get("meeting_location") or extracted.get("first_meeting_location"),
            first_meeting_mode=extracted.get("meeting_mode") or "presenza",
        )
        mediation = create_full_mediation_from_intake(intake_payload, db, generate_documents=True, require_office=True, commit=False)
        case = ensure_mediation_case(db, mediation)
        db.execute(
            text(
                """
                UPDATE intake_sessions
                SET status = 'created',
                    review_status = 'confirmed',
                    extracted_json = CAST(:extracted_json AS jsonb),
                    created_case_id = :case_id,
                    created_mediation_id = :mediation_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                """
            ),
            {
                "id": session_id,
                "extracted_json": json.dumps(extracted, ensure_ascii=False),
                "case_id": case["id"],
                "mediation_id": mediation["id"],
            },
        )
        audit(db, "intake_sessions", session_id, "confirm_and_create_mediation", extracted, organization_id=session.get("organization_id") or 1)
        db.commit()
        return {"intake_session": intake_session_detail(db, session_id), "case": case, "mediation": mediation_detail(db, mediation["id"])}
    except Exception:
        db.rollback()
        raise


def build_intake_review(payload: IntakeReviewPayload) -> dict[str, Any]:
    data = dict(payload.extracted_data or {})
    required_extracted = {
        "claimant": "Parte istante",
        "invited_party": "Parte invitata",
        "matter": "Materia",
        "claim_value": "Valore",
        "object": "Oggetto",
    }
    missing = [
        {"field": field, "label": label, "value": "Da verificare"}
        for field, label in required_extracted.items()
        if not data.get(field)
    ]
    for item in missing:
        data[item["field"]] = item["value"]
    return {
        "status": "ready_for_review",
        "extracted_data": data,
        "documents": payload.documents,
        "missing_fields": missing,
        "required_user_fields": [
            {"field": "mediator_name", "label": "Mediatore"},
            {"field": "first_meeting_date", "label": "Data primo incontro"},
            {"field": "first_meeting_time", "label": "Ora primo incontro"},
            {"field": "first_meeting_location", "label": "Sede/modalita"},
        ],
        "summary": {
            "documents_count": len(payload.documents),
            "pdf_intake_id": payload.pdf_intake_id,
            "zip_intake_id": payload.zip_intake_id,
        },
    }


@app.post("/mediations/intake/review")
def review_mediation_intake(payload: IntakeReviewPayload):
    return build_intake_review(payload)


def create_full_mediation_from_intake(payload: IntakeCreatePayload, db: Session, generate_documents: bool = True, require_office: bool = False, commit: bool = True) -> dict[str, Any]:
    data = payload.extracted_data
    organization_id = payload.organization_id or 1
    year = db.execute(text("SELECT EXTRACT(YEAR FROM CURRENT_DATE)::int")).scalar_one()
    quarter = db.execute(text("SELECT EXTRACT(QUARTER FROM CURRENT_DATE)::int")).scalar_one()
    office_id = payload.office_id or resolve_office_id_from_data(db, organization_id, data)
    if require_office and not office_id:
        raise HTTPException(status_code=400, detail="Sede non riconosciuta: selezionare la sede prima di creare la procedura.")
    if not office_id and not require_office:
        office_id = db.execute(
            text("SELECT id FROM offices WHERE organization_id = :organization_id ORDER BY id LIMIT 1"),
            {"organization_id": organization_id},
        ).scalar_one_or_none()

    try:
        claim_value = float(data.get("claim_value") or 0)
    except (TypeError, ValueError):
        claim_value = 0
    mediator_id = payload.mediator_id
    if not mediator_id and payload.mediator_name:
        mediator_id = get_or_create_contact(db, organization_id, payload.mediator_name, is_mediator=True)
    mediation_data = {
        "organization_id": organization_id,
        "office_id": office_id,
        "internal_number": data.get("internal_number") or next_internal_number(db, organization_id, year),
        "dgstat_number": data.get("dgstat_number"),
        "year": data.get("year") or year,
        "quarter": data.get("quarter") or quarter,
        "deposit_date": data.get("deposit_date"),
        "first_meeting_date": payload.first_meeting_date,
        "closing_date": None,
        "status": "depositata",
        "outcome": None,
        "mediation_type": data.get("mediation_type") or "obbligatoria",
        "matter": data.get("matter") or "Da verificare",
        "submatter": data.get("submatter") or "Da verificare",
        "claim_value": claim_value,
        "object": data.get("object") or "Da verificare",
        "reasons": data.get("reasons") or "Da verificare",
        "mediator_id": mediator_id,
        "tariff_id": data.get("tariff_id") or db.execute(
            text("SELECT id FROM mediation_tariffs WHERE organization_id = :organization_id AND active = TRUE ORDER BY id LIMIT 1"),
            {"organization_id": organization_id},
        ).scalar_one_or_none(),
        "notes": "Creata da Document Intake completo"
        + (f" | Mediatore designato: {payload.mediator_name}" if payload.mediator_name else ""),
    }
    mediation_data.update(calculate_mediation_fees(db, organization_id, mediation_data.get("tariff_id"), mediation_data.get("claim_value")))
    insert_data = {key: mediation_data.get(key) for key in MEDIATION_FIELDS}
    insert_data["title"] = mediation_data["internal_number"]
    insert_data["subject"] = mediation_data.get("object")
    insert_data["dispute_value"] = mediation_data.get("claim_value") or 0
    columns = ", ".join(insert_data.keys())
    placeholders = ", ".join([f":{key}" for key in insert_data.keys()])
    mediation_row = db.execute(
        text(f"INSERT INTO mediations ({columns}) VALUES ({placeholders}) RETURNING id"),
        insert_data,
    ).fetchone()
    mediation_id = mediation_row.id
    create_mediation_people(db, mediation_id, organization_id, data)
    create_first_mediation_session(db, mediation_id, payload)
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "intake.reviewed", "Intake revisionato", "Review confermata dall'utente")
    add_case_timeline(db, case["id"], "case.created", "Fascicolo creato", case.get("case_number"))
    add_case_timeline(db, case["id"], "mediation.created", "Mediazione creata", mediation.get("internal_number"))

    for document in payload.documents:
        case_document = None
        if document.get("file_path"):
            case_document = insert_case_document(db, case["id"], parse_document(document.get("file_path"), document.get("filename")))
        db.execute(
            text(
                """
                INSERT INTO mediation_documents (mediation_id, document_type, filename, file_path, notes)
                VALUES (:mediation_id, :document_type, :filename, :file_path, :notes)
                """
            ),
            {
                "mediation_id": mediation_id,
                "document_type": document.get("document_type") or (case_document or {}).get("document_type") or "altro",
                "filename": document.get("filename") or (case_document or {}).get("filename") or "documento",
                "file_path": document.get("file_path") or (case_document or {}).get("file_path"),
                "notes": "Collegato da Document Intake",
            },
        )
    add_case_timeline(db, case["id"], "tariff.calculated", "Tariffario calcolato", f"Totale iniziale {mediation.get('calculated_total')}")
    add_case_timeline(db, case["id"], "intake_confirmed", "Procedura mediazione completa creata", "Document Intake confermato dall'utente")
    if payload.first_meeting_date:
        add_case_timeline(db, case["id"], "first_meeting.scheduled", "Primo incontro programmato", payload.first_meeting_date)
    generated = []
    if generate_documents:
        generated = generate_initial_documents_for_mediation(
            db,
            mediation_id,
            payload,
            template_keys=["assunzione_incarico", "dichiarazione_imparzialita", "convocazione_primo_incontro", "modulo_adesione"],
        )
        add_case_timeline(db, case["id"], "appointment.documents.generated", "Documenti iniziali generati", ", ".join([doc["document_type"] for doc in generated]))
        add_case_timeline(db, case["id"], "notice.generated", "Convocazione generata", "Convocazione al primo incontro predisposta")
    assignment = create_mediator_assignment_draft(db, mediation_id, mediation, generated)
    if assignment:
        add_case_timeline(db, case["id"], "mediator.assigned", "Mediatore assegnato", assignment.get("mediator_name"))

    for intake_id in [payload.pdf_intake_id, payload.zip_intake_id]:
        if intake_id:
            db.execute(
                text("UPDATE document_intakes SET status = 'confirmed', updated_at = CURRENT_TIMESTAMP WHERE id = :id"),
                {"id": intake_id},
            )

    next_action = calculate_next_action(case["id"], next_action_context_for_mediation(mediation_detail(db, mediation_id)))
    add_case_timeline(db, case["id"], "next_action.updated", "Prossima azione aggiornata", next_action["title"])
    audit(db, "mediations", mediation_id, "create_from_document_intake", {"data": data, "next_action": next_action})
    if commit:
        db.commit()
    return mediation_detail(db, mediation_id)


@app.post("/mediations/intake/create", status_code=201)
def create_mediation_from_intake(payload: IntakeCreatePayload, db: Session = Depends(get_db)):
    return create_full_mediation_from_intake(payload, db)


@app.post("/mediations/create-from-intake", status_code=201)
def create_mediation_from_intake_v2(payload: IntakeCreatePayload, db: Session = Depends(get_db)):
    return create_full_mediation_from_intake(payload, db)


@app.post("/mediations/create-from-documents", status_code=201)
def create_mediation_from_documents(payload: IntakeCreatePayload, db: Session = Depends(get_db)):
    return create_full_mediation_from_intake(payload, db)


@app.post("/mediations/{mediation_id}/generate-initial-documents", status_code=201)
def generate_mediation_initial_documents(mediation_id: int, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM mediations WHERE id = :id"), {"id": mediation_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    generated = generate_initial_documents_for_mediation(db, mediation_id)
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "documents_generated", "Documenti iniziali generati", ", ".join([doc["document_type"] for doc in generated]))
    audit(db, "generated_documents", mediation_id, "generate_initial_documents", generated, organization_id=mediation.get("organization_id") or 1)
    db.commit()
    return {"generated_documents": generated, "mediation": mediation_detail(db, mediation_id)}


@app.post("/mediations/{mediation_id}/generate-startup-package", status_code=201)
def generate_mediation_startup_package(mediation_id: int, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM mediations WHERE id = :id"), {"id": mediation_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    generated = generate_initial_documents_for_mediation(
        db,
        mediation_id,
        template_keys=["assunzione_incarico", "dichiarazione_imparzialita", "convocazione_primo_incontro", "modulo_adesione"],
    )
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "startup_package_generated", "Startup package generato", ", ".join([doc["document_type"] for doc in generated]))
    next_action = calculate_next_action(case["id"], next_action_context_for_mediation(mediation_detail(db, mediation_id)))
    add_case_timeline(db, case["id"], "next_action.updated", "Prossima azione aggiornata", next_action["title"])
    audit(db, "generated_documents", mediation_id, "generate_startup_package", generated, organization_id=mediation.get("organization_id") or 1)
    db.commit()
    return {"generated_documents": generated, "mediation": mediation_detail(db, mediation_id)}


@app.get("/mediations/{mediation_id}/next-action")
def get_mediation_next_action(mediation_id: int, db: Session = Depends(get_db)):
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    return calculate_next_action(case["id"], next_action_context_for_mediation(mediation))


@app.post("/mediations/{mediation_id}/next-action/complete")
def complete_mediation_next_action(mediation_id: int, payload: RecordPayload, db: Session = Depends(get_db)):
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    action_key = payload.data.get("action_key") or mediation.get("next_action", {}).get("action_key")
    if not action_key:
        raise HTTPException(status_code=400, detail="action_key obbligatorio")
    completed = complete_kernel_action(case["id"], action_key)
    add_case_timeline(db, case["id"], "next_action.completed", "Azione completata", action_key)
    next_action = calculate_next_action(case["id"], next_action_context_for_mediation(mediation_detail(db, mediation_id)))
    add_case_timeline(db, case["id"], "next_action.updated", "Prossima azione aggiornata", next_action["title"])
    audit(db, "mediations", mediation_id, "complete_next_action", completed, organization_id=mediation.get("organization_id") or 1)
    db.commit()
    return {"completed": completed, "next_action": next_action, "mediation": mediation_detail(db, mediation_id)}


@app.get("/mediations/{mediation_id}/creation-summary")
def get_mediation_creation_summary(mediation_id: int, db: Session = Depends(get_db)):
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    return {
        "case_id": case["id"],
        "mediation_id": mediation_id,
        "internal_number": mediation.get("internal_number"),
        "office": mediation.get("office_name"),
        "parties_count": len(mediation.get("parties", [])),
        "lawyers_count": len(mediation.get("lawyers", [])),
        "sessions_count": len(mediation.get("sessions", [])),
        "documents_count": len(mediation.get("documents", [])),
        "generated_documents_count": len(mediation.get("generated_documents", [])),
        "tariff": {
            "name": mediation.get("tariff_name"),
            "startup_expenses": mediation.get("calculated_initial_expense"),
            "first_meeting_expenses": mediation.get("calculated_first_meeting_fee"),
            "vat": mediation.get("calculated_vat"),
            "total": mediation.get("calculated_total"),
        },
        "next_action": mediation.get("next_action"),
    }


@app.post("/mediations/{mediation_id}/generate-first-meeting-notice", status_code=201)
def generate_mediation_first_meeting_notice(mediation_id: int, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM mediations WHERE id = :id"), {"id": mediation_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    return generate_first_meeting_notice(db, mediation_id)


@app.post("/mediations/{mediation_id}/send-mediator-appointment", status_code=201)
def send_mediator_appointment(mediation_id: int, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM mediations WHERE id = :id"), {"id": mediation_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    return create_mediator_appointment_package(db, mediation_id)


@app.post("/mediations/{mediation_id}/mediator-appointment-reminder", status_code=201)
def remind_mediator_appointment(mediation_id: int, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM mediations WHERE id = :id"), {"id": mediation_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    return create_mediator_appointment_package(db, mediation_id, reminder=True)


@app.post("/mediations/{mediation_id}/mediator-signature-received")
def mediator_signature_received(mediation_id: int, db: Session = Depends(get_db)):
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    assignment = db.execute(
        text(
            """
            UPDATE mediator_assignments
            SET accepted_at = CURRENT_TIMESTAMP,
                status = 'accepted',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = (
                SELECT id FROM mediator_assignments WHERE mediation_id = :mediation_id ORDER BY id DESC LIMIT 1
            )
            RETURNING *
            """
        ),
        {"mediation_id": mediation_id},
    ).fetchone()
    if not assignment:
        raise HTTPException(status_code=404, detail="Nomina mediatore non trovata")
    add_case_timeline(db, case["id"], "mediator_signature_received", "Firma/accettazione mediatore ricevuta", assignment.status)
    next_action = calculate_next_action(case["id"], next_action_context_for_mediation(mediation_detail(db, mediation_id)))
    add_case_timeline(db, case["id"], "next_action.updated", "Prossima azione aggiornata", next_action["title"])
    audit(db, "mediator_assignments", assignment.id, "mediator_signature_received", row_to_dict(assignment), organization_id=mediation.get("organization_id") or 1)
    db.commit()
    return {"assignment": row_to_dict(assignment), "mediation": mediation_detail(db, mediation_id)}


@app.get("/cases")
def list_cases(
    organization_id: int = Query(default=1),
    case_type: str | None = Query(default=None),
    status: str | None = Query(default=None),
    q: str | None = Query(default=None),
    db: Session = Depends(get_db),
):
    params: dict[str, Any] = {"organization_id": organization_id}
    where = "WHERE c.organization_id = :organization_id"
    if case_type:
        where += " AND c.case_type = :case_type"
        params["case_type"] = case_type
    if status:
        where += " AND c.status = :status"
        params["status"] = status
    if q:
        where += " AND (c.case_number ILIKE :q OR c.title ILIKE :q OR c.notes ILIKE :q)"
        params["q"] = f"%{q}%"
    rows = db.execute(
        text(
            f"""
            SELECT c.*, o.name AS office_name
            FROM cases c
            LEFT JOIN offices o ON o.id = c.office_id
            {where}
            ORDER BY c.updated_at DESC, c.id DESC
            """
        ),
        params,
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/cases/{case_id}")
def get_case(case_id: int, db: Session = Depends(get_db)):
    return case_detail(db, case_id)


@app.post("/cases", status_code=201)
def create_case(payload: CasePayload, db: Session = Depends(get_db)):
    case = create_case_record(db, clean_model_data(payload))
    db.commit()
    return case_detail(db, case["id"])


@app.put("/cases/{case_id}")
def update_case(case_id: int, payload: CasePayload, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT * FROM cases WHERE id = :id"), {"id": case_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Fascicolo non trovato")
    data = clean_model_data(payload)
    data["id"] = case_id
    assignments = ", ".join([f"{key} = :{key}" for key in data.keys() if key != "id"])
    db.execute(text(f"UPDATE cases SET {assignments}, updated_at = CURRENT_TIMESTAMP WHERE id = :id"), data)
    add_case_timeline(db, case_id, "case_updated", "Fascicolo aggiornato")
    db.commit()
    return case_detail(db, case_id)


@app.delete("/cases/{case_id}", status_code=204)
def delete_case(case_id: int, db: Session = Depends(get_db)):
    result = db.execute(text("DELETE FROM cases WHERE id = :id"), {"id": case_id})
    db.commit()
    if result.rowcount == 0:
        raise HTTPException(status_code=404, detail="Fascicolo non trovato")
    return None


@app.get("/cases/{case_id}/documents")
def list_case_documents(case_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT * FROM case_documents WHERE case_id = :case_id ORDER BY uploaded_at DESC"), {"case_id": case_id}).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/cases/{case_id}/documents", status_code=201)
async def upload_case_document(case_id: int, file: UploadFile = File(...), db: Session = Depends(get_db)):
    case_detail(db, case_id)
    target_path = await save_upload(file, UPLOAD_ROOT / "cases" / str(case_id))
    metadata = parse_document(target_path, file.filename)
    document = insert_case_document(db, case_id, metadata)
    add_case_timeline(db, case_id, "document_uploaded", "Documento caricato", file.filename)
    db.commit()
    return document


@app.post("/cases/{case_id}/documents/bulk", status_code=201)
async def upload_case_documents_bulk(case_id: int, files: list[UploadFile] = File(...), db: Session = Depends(get_db)):
    case_detail(db, case_id)
    documents = []
    for file in files:
        target_path = await save_upload(file, UPLOAD_ROOT / "cases" / str(case_id))
        document = insert_case_document(db, case_id, parse_document(target_path, file.filename))
        documents.append(document)
        add_case_timeline(db, case_id, "document_uploaded", "Documento caricato", file.filename)
        add_case_timeline(db, case_id, "document_classified", "Documento classificato", document.get("document_type"))
    db.commit()
    return {"documents": documents}


@app.post("/cases/{case_id}/upload-zip", status_code=201)
async def upload_case_zip(case_id: int, file: UploadFile = File(...), db: Session = Depends(get_db)):
    case_detail(db, case_id)
    if not (file.filename or "").lower().endswith(".zip"):
        raise HTTPException(status_code=400, detail="Caricare un file ZIP")
    case_dir = UPLOAD_ROOT / "cases" / str(case_id)
    zip_path = await save_upload(file, case_dir)
    extract_dir = case_dir / f"zip_{uuid4().hex}"
    extract_dir.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(zip_path) as archive:
        archive.extractall(extract_dir)
    documents = []
    for path in extract_dir.rglob("*"):
        if path.is_file():
            documents.append(insert_case_document(db, case_id, parse_document(path, path.name)))
    add_case_timeline(db, case_id, "zip_uploaded", "Caricato pacchetto documentale ZIP", file.filename)
    db.commit()
    return {"documents": documents}


@app.get("/cases/{case_id}/checklist")
def get_case_checklist(case_id: int, db: Session = Depends(get_db)):
    case = case_detail(db, case_id)
    documents = list_case_documents(case_id, db)
    context: dict[str, Any] = {}
    if case.get("related_entity_type") == "mediation" and case.get("related_entity_id"):
        context = mediation_detail(db, case["related_entity_id"])
        context["timeline"] = case.get("timeline", [])
    checklist = build_checklist(case["case_type"], documents, context)
    return checklist


@app.get("/cases/{case_id}/suggestions")
def get_case_suggestions(case_id: int, db: Session = Depends(get_db)):
    checklist = get_case_checklist(case_id, db)
    extracted = {}
    for doc in list_case_documents(case_id, db):
        extracted.update(doc.get("extracted_json") or {})
    return {"suggestions": suggestions_from_checklist(checklist, extracted)}


@app.post("/cases/{case_id}/assistant")
def ask_case_assistant(case_id: int, payload: AssistantQuestionPayload, db: Session = Depends(get_db)):
    case = case_detail(db, case_id)
    documents = list_case_documents(case_id, db)
    context: dict[str, Any] = {}
    if case.get("related_entity_type") == "mediation" and case.get("related_entity_id"):
        context = mediation_detail(db, case["related_entity_id"])
        context["timeline"] = case.get("timeline", [])
    checklist = build_checklist(case["case_type"], documents, context)
    deadlines = list_case_deadlines(case_id, db)
    tasks = list_case_tasks(case_id, db)
    add_case_timeline(db, case_id, "assistant_question", "Assistente AI consultato", payload.question)
    db.commit()
    return answer_question(payload.question, case, documents, checklist, deadlines, tasks)


@app.get("/case-search")
def search_cases(q: str = Query(default=""), organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT DISTINCT c.*
            FROM cases c
            LEFT JOIN case_documents d ON d.case_id = c.id
            WHERE c.organization_id = :organization_id
              AND (
                c.case_number ILIKE :q OR c.title ILIKE :q OR c.notes ILIKE :q
                OR d.filename ILIKE :q OR d.extracted_text ILIKE :q
                OR CAST(d.extracted_json AS TEXT) ILIKE :q
              )
            ORDER BY c.updated_at DESC, c.id DESC
            """
        ),
        {"organization_id": organization_id, "q": f"%{q}%"},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/cases/{case_id}/timeline")
def list_case_timeline(case_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT * FROM case_timeline WHERE case_id = :case_id ORDER BY created_at DESC, id DESC"), {"case_id": case_id}).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/cases/{case_id}/tasks")
def list_case_tasks(case_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT * FROM case_tasks WHERE case_id = :case_id ORDER BY due_date ASC NULLS LAST, id DESC"), {"case_id": case_id}).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/cases/{case_id}/tasks", status_code=201)
def create_case_task(case_id: int, payload: CaseTaskPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["case_id"] = case_id
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(text(f"INSERT INTO case_tasks ({columns}) VALUES ({placeholders}) RETURNING *"), data).fetchone()
    add_case_timeline(db, case_id, "task_created", "Attività creata", data["title"])
    db.commit()
    return row_to_dict(row)


@app.get("/cases/{case_id}/deadlines")
def list_case_deadlines(case_id: int, db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT * FROM case_deadlines WHERE case_id = :case_id ORDER BY deadline_date ASC"), {"case_id": case_id}).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/cases/{case_id}/deadlines", status_code=201)
def create_case_deadline(case_id: int, payload: CaseDeadlinePayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["case_id"] = case_id
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(text(f"INSERT INTO case_deadlines ({columns}) VALUES ({placeholders}) RETURNING *"), data).fetchone()
    add_case_timeline(db, case_id, "deadline_created", "Scadenza creata", data["title"])
    db.commit()
    return row_to_dict(row)


@app.get("/cases/{case_id}/contacts")
def list_case_contacts(case_id: int, db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT cc.*, c.first_name, c.last_name, c.company_name
            FROM case_contacts cc
            JOIN contacts c ON c.id = cc.contact_id
            WHERE cc.case_id = :case_id
            ORDER BY cc.id DESC
            """
        ),
        {"case_id": case_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/cases/{case_id}/contacts", status_code=201)
def create_case_contact(case_id: int, payload: CaseContactPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["case_id"] = case_id
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(text(f"INSERT INTO case_contacts ({columns}) VALUES ({placeholders}) RETURNING *"), data).fetchone()
    add_case_timeline(db, case_id, "contact_linked", "Contatto collegato", data["role"])
    db.commit()
    return row_to_dict(row)


@app.post("/cases/{case_id}/create-mediation", status_code=201)
def create_mediation_from_case(case_id: int, db: Session = Depends(get_db)):
    case = case_detail(db, case_id)
    if case["case_type"] != "mediation":
        raise HTTPException(status_code=400, detail="Il fascicolo non è di tipo mediazione")
    docs = db.execute(text("SELECT extracted_json FROM case_documents WHERE case_id = :case_id ORDER BY id"), {"case_id": case_id}).fetchall()
    extracted = {}
    for doc in docs:
        if doc.extracted_json:
            extracted.update(doc.extracted_json)
    payload = IntakeCreatePayload(
        organization_id=case["organization_id"],
        office_id=case["office_id"],
        extracted_data={
            "matter": extracted.get("matter"),
            "claim_value": extracted.get("claim_value"),
            "object": extracted.get("object") or case["title"],
            "reasons": case.get("notes"),
        },
        documents=[],
    )
    mediation = create_mediation_from_intake(payload, db)
    db.execute(
        text("UPDATE cases SET related_entity_id = :mediation_id, related_entity_type = 'mediation', updated_at = CURRENT_TIMESTAMP WHERE id = :case_id"),
        {"mediation_id": mediation["id"], "case_id": case_id},
    )
    add_case_timeline(db, case_id, "mediation_created", "Mediazione creata da fascicolo", mediation.get("internal_number"))
    db.commit()
    return mediation


@app.get("/mediations/{mediation_id}")
def get_mediation(mediation_id: int, db: Session = Depends(get_db)):
    return mediation_detail(db, mediation_id)


@app.post("/mediations", status_code=201)
def create_mediation(payload: MediationPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    organization_id = data.get("organization_id") or 1
    year = data.get("year") or db.execute(text("SELECT EXTRACT(YEAR FROM CURRENT_DATE)::int")).scalar_one()
    quarter = data.get("quarter") or db.execute(text("SELECT EXTRACT(QUARTER FROM CURRENT_DATE)::int")).scalar_one()
    data["organization_id"] = organization_id
    data["year"] = year
    data["quarter"] = quarter
    data["internal_number"] = data.get("internal_number") or next_internal_number(db, organization_id, year)
    data["office_id"] = data.get("office_id") or db.execute(
        text("SELECT id FROM offices WHERE organization_id = :organization_id ORDER BY id LIMIT 1"),
        {"organization_id": organization_id},
    ).scalar_one_or_none()
    data.update(calculate_mediation_fees(db, organization_id, data.get("tariff_id"), data.get("claim_value")))

    insert_data = {key: data.get(key) for key in MEDIATION_FIELDS}
    insert_data["title"] = data["internal_number"]
    insert_data["subject"] = data.get("object")
    insert_data["dispute_value"] = data.get("claim_value") or 0
    columns = ", ".join(insert_data.keys())
    placeholders = ", ".join([f":{key}" for key in insert_data.keys()])
    row = db.execute(
        text(f"INSERT INTO mediations ({columns}) VALUES ({placeholders}) RETURNING id"),
        insert_data,
    ).fetchone()
    mediation = mediation_detail(db, row.id)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "mediation_created", "Mediazione creata", mediation.get("internal_number"))
    db.commit()
    return mediation_detail(db, row.id)


@app.put("/mediations/{mediation_id}")
def update_mediation(mediation_id: int, payload: MediationPayload, db: Session = Depends(get_db)):
    existing = db.execute(text("SELECT id FROM mediations WHERE id = :id"), {"id": mediation_id}).fetchone()
    if not existing:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")

    data = clean_model_data(payload)
    organization_id = data.get("organization_id") or 1
    data["organization_id"] = organization_id
    data.update(calculate_mediation_fees(db, organization_id, data.get("tariff_id"), data.get("claim_value")))
    update_data = {key: data.get(key) for key in MEDIATION_FIELDS}
    update_data["title"] = data.get("internal_number")
    update_data["subject"] = data.get("object")
    update_data["dispute_value"] = data.get("claim_value") or 0
    update_data["id"] = mediation_id
    assignments = ", ".join([f"{key} = :{key}" for key in update_data.keys() if key != "id"])
    db.execute(
        text(f"UPDATE mediations SET {assignments}, updated_at = CURRENT_TIMESTAMP WHERE id = :id"),
        update_data,
    )
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "mediation_updated", "Mediazione aggiornata", data.get("status"))
    if data.get("status") in {"chiusa", "archiviata", "closed", "archived"}:
        add_case_timeline(db, case["id"], "case_closed", "Pratica chiusa", data.get("outcome"))
    db.commit()
    return mediation_detail(db, mediation_id)


@app.delete("/mediations/{mediation_id}", status_code=204)
def delete_mediation(mediation_id: int, db: Session = Depends(get_db)):
    result = db.execute(text("DELETE FROM mediations WHERE id = :id"), {"id": mediation_id})
    db.commit()
    if result.rowcount == 0:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    return None


@app.get("/mediation-tariffs")
def list_mediation_tariffs(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT *
            FROM mediation_tariffs
            WHERE organization_id = :organization_id
            ORDER BY active DESC, valid_from DESC NULLS LAST, id DESC
            """
        ),
        {"organization_id": organization_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/mediation-tariffs", status_code=201)
def create_mediation_tariff(payload: MediationTariffPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(
        text(f"INSERT INTO mediation_tariffs ({columns}) VALUES ({placeholders}) RETURNING *"),
        data,
    ).fetchone()
    db.commit()
    return row_to_dict(row)


@app.put("/mediation-tariffs/{tariff_id}")
def update_mediation_tariff(tariff_id: int, payload: MediationTariffPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["id"] = tariff_id
    assignments = ", ".join([f"{key} = :{key}" for key in data.keys() if key != "id"])
    row = db.execute(
        text(f"UPDATE mediation_tariffs SET {assignments}, updated_at = CURRENT_TIMESTAMP WHERE id = :id RETURNING *"),
        data,
    ).fetchone()
    db.commit()
    if not row:
        raise HTTPException(status_code=404, detail="Tariffario non trovato")
    return row_to_dict(row)


@app.get("/mediation-tariffs/{tariff_id}/rows")
def list_mediation_tariff_rows(tariff_id: int, db: Session = Depends(get_db)):
    rows = db.execute(
        text("SELECT * FROM mediation_tariff_rows WHERE tariff_id = :tariff_id ORDER BY value_min ASC"),
        {"tariff_id": tariff_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/mediation-tariffs/{tariff_id}/rows", status_code=201)
def create_mediation_tariff_row(tariff_id: int, payload: MediationTariffRowPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["tariff_id"] = tariff_id
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(
        text(f"INSERT INTO mediation_tariff_rows ({columns}) VALUES ({placeholders}) RETURNING *"),
        data,
    ).fetchone()
    db.commit()
    return row_to_dict(row)


@app.put("/mediation-tariff-rows/{row_id}")
def update_mediation_tariff_row(row_id: int, payload: MediationTariffRowPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["id"] = row_id
    assignments = ", ".join([f"{key} = :{key}" for key in data.keys() if key != "id"])
    row = db.execute(
        text(f"UPDATE mediation_tariff_rows SET {assignments} WHERE id = :id RETURNING *"),
        data,
    ).fetchone()
    db.commit()
    if not row:
        raise HTTPException(status_code=404, detail="Riga tariffaria non trovata")
    return row_to_dict(row)


@app.post("/mediations/{mediation_id}/calculate-fees")
def calculate_and_save_mediation_fees(mediation_id: int, db: Session = Depends(get_db)):
    row = db.execute(
        text("SELECT organization_id, tariff_id, claim_value FROM mediations WHERE id = :id"),
        {"id": mediation_id},
    ).fetchone()
    if not row:
        raise HTTPException(status_code=404, detail="Mediazione non trovata")
    mediation = row_to_dict(row)
    calculated = calculate_mediation_fees(
        db,
        mediation["organization_id"],
        mediation["tariff_id"],
        float(mediation["claim_value"] or 0),
    )
    calculated["id"] = mediation_id
    db.execute(
        text(
            """
            UPDATE mediations
            SET tariff_id = :tariff_id,
                tariff_row_id = :tariff_row_id,
                calculated_initial_expense = :calculated_initial_expense,
                calculated_first_meeting_fee = :calculated_first_meeting_fee,
                calculated_further_fee = :calculated_further_fee,
                calculated_vat = :calculated_vat,
                calculated_total = :calculated_total,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            """
        ),
        calculated,
    )
    db.commit()
    return mediation_detail(db, mediation_id)


@app.get("/economic-rules")
def list_economic_rules(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT er.*, o.name AS office_name
            FROM economic_rules er
            LEFT JOIN offices o ON o.id = er.office_id
            WHERE er.organization_id = :organization_id
            ORDER BY o.name NULLS FIRST, er.rule_key, er.mediation_outcome NULLS FIRST
            """
        ),
        {"organization_id": organization_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/economic-rules", status_code=201)
def create_economic_rule(payload: EconomicRulePayload, db: Session = Depends(get_db)):
    data = economic_rule_data(payload)
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(
        text(f"INSERT INTO economic_rules ({columns}) VALUES ({placeholders}) RETURNING *"),
        data,
    ).fetchone()
    result = row_to_dict(row)
    audit(db, "economic_rules", result["id"], "create", result, organization_id=result.get("organization_id", 1))
    db.commit()
    return result


@app.put("/economic-rules/{rule_id}")
def update_economic_rule(rule_id: int, payload: EconomicRulePayload, db: Session = Depends(get_db)):
    old = db.execute(text("SELECT * FROM economic_rules WHERE id = :id"), {"id": rule_id}).fetchone()
    if not old:
        raise HTTPException(status_code=404, detail="Regola economica non trovata")
    data = economic_rule_data(payload)
    data["id"] = rule_id
    assignments = ", ".join([f"{key} = :{key}" for key in data.keys() if key != "id"])
    row = db.execute(
        text(f"UPDATE economic_rules SET {assignments}, updated_at = CURRENT_TIMESTAMP WHERE id = :id RETURNING *"),
        data,
    ).fetchone()
    result = row_to_dict(row)
    audit(db, "economic_rules", rule_id, "update", result, row_to_dict(old), result.get("organization_id", 1))
    db.commit()
    return result


@app.post("/mediations/{mediation_id}/calculate-economic-split")
def calculate_economic_split(mediation_id: int, db: Session = Depends(get_db)):
    result = calculate_economic_split_data(db, mediation_id)
    db.execute(
        text(
            """
            UPDATE mediations
            SET netto_maturato_calcolato = :netto_maturato,
                quota_mediatore_calcolata = :mediator_share,
                quota_sede_operativa_calcolata = :operational_office_share,
                quota_mediacon_calcolata = :mediacon_share,
                regola_economica_applicata_id = :applied_rule_id,
                data_calcolo = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :mediation_id
            """
        ),
        result,
    )
    audit(db, "mediations", mediation_id, "calculate_economic_split", result)
    db.commit()
    return result


@app.get("/mediations/{mediation_id}/sessions")
def list_mediation_sessions(mediation_id: int, db: Session = Depends(get_db)):
    return mediation_detail(db, mediation_id)["sessions"]


@app.post("/mediations/{mediation_id}/sessions", status_code=201)
def create_mediation_session(mediation_id: int, payload: MediationSessionPayload, db: Session = Depends(get_db)):
    data = clean_model_data(payload)
    data["mediation_id"] = mediation_id
    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(
        text(f"INSERT INTO mediation_sessions ({columns}) VALUES ({placeholders}) RETURNING *"),
        data,
    ).fetchone()
    mediation = mediation_detail(db, mediation_id)
    case = ensure_mediation_case(db, mediation)
    add_case_timeline(db, case["id"], "first_meeting_scheduled", "Incontro fissato", data.get("session_date"))
    db.commit()
    return row_to_dict(row)


@app.get("/config/overview")
def config_overview(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    organization = db.execute(
        text("SELECT * FROM organizations WHERE id = :organization_id"),
        {"organization_id": organization_id},
    ).fetchone()
    counts = {
        "active_modules": db.execute(text("SELECT COUNT(*) FROM config_modules WHERE organization_id = :organization_id AND enabled = TRUE"), {"organization_id": organization_id}).scalar_one(),
        "active_offices": db.execute(text("SELECT COUNT(*) FROM offices WHERE organization_id = :organization_id AND active = TRUE"), {"organization_id": organization_id}).scalar_one(),
        "active_tariffs": db.execute(text("SELECT COUNT(*) FROM mediation_tariffs WHERE organization_id = :organization_id AND active = TRUE"), {"organization_id": organization_id}).scalar_one(),
        "active_workflows": db.execute(text("SELECT COUNT(*) FROM config_workflows WHERE organization_id = :organization_id AND active = TRUE"), {"organization_id": organization_id}).scalar_one(),
    }
    return {"organization": row_to_dict(organization) if organization else None, "counts": counts}


@app.get("/config/modules")
def list_config_modules(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    return config_list("modules", db, organization_id)


@app.put("/config/modules/{record_id}")
def update_config_module(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("modules", record_id, payload, db)


@app.get("/config/numbering-rules")
def list_numbering_rules(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    return config_list("numbering-rules", db, organization_id)


@app.post("/config/numbering-rules", status_code=201)
def create_numbering_rule(payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_create("numbering-rules", payload, db)


@app.put("/config/numbering-rules/{record_id}")
def update_numbering_rule(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("numbering-rules", record_id, payload, db)


@app.get("/config/matters")
def list_config_matters(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    return config_list("matters", db, organization_id)


@app.post("/config/matters", status_code=201)
def create_config_matter(payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_create("matters", payload, db)


@app.put("/config/matters/{record_id}")
def update_config_matter(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("matters", record_id, payload, db)


@app.delete("/config/matters/{record_id}", status_code=204)
def delete_config_matter(record_id: int, db: Session = Depends(get_db)):
    old = db.execute(text("SELECT * FROM config_mediation_matters WHERE id = :id"), {"id": record_id}).fetchone()
    result = db.execute(text("DELETE FROM config_mediation_matters WHERE id = :id"), {"id": record_id})
    if result.rowcount == 0:
        raise HTTPException(status_code=404, detail="Materia non trovata")
    audit(db, "config_mediation_matters", record_id, "delete", old_value=row_to_dict(old) if old else None)
    db.commit()
    return None


@app.get("/config/workflows")
def list_config_workflows(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT w.*, count(s.id) AS steps_count
            FROM config_workflows w
            LEFT JOIN config_workflow_steps s ON s.workflow_id = w.id
            WHERE w.organization_id = :organization_id
            GROUP BY w.id
            ORDER BY w.module_key, w.name
            """
        ),
        {"organization_id": organization_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/config/workflows", status_code=201)
def create_config_workflow(payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_create("workflows", payload, db)


@app.put("/config/workflows/{record_id}")
def update_config_workflow(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("workflows", record_id, payload, db)


@app.get("/config/workflows/{workflow_id}/steps")
def list_workflow_steps(workflow_id: int, db: Session = Depends(get_db)):
    rows = db.execute(
        text("SELECT * FROM config_workflow_steps WHERE workflow_id = :workflow_id ORDER BY step_order, id"),
        {"workflow_id": workflow_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/config/workflows/{workflow_id}/steps", status_code=201)
def create_workflow_step(workflow_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    payload.data["workflow_id"] = workflow_id
    return config_create("workflow-steps", payload, db)


@app.put("/config/workflow-steps/{record_id}")
def update_workflow_step(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("workflow-steps", record_id, payload, db)


@app.delete("/config/workflow-steps/{record_id}", status_code=204)
def delete_workflow_step(record_id: int, db: Session = Depends(get_db)):
    old = db.execute(text("SELECT * FROM config_workflow_steps WHERE id = :id"), {"id": record_id}).fetchone()
    result = db.execute(text("DELETE FROM config_workflow_steps WHERE id = :id"), {"id": record_id})
    if result.rowcount == 0:
        raise HTTPException(status_code=404, detail="Step non trovato")
    audit(db, "config_workflow_steps", record_id, "delete", old_value=row_to_dict(old) if old else None)
    db.commit()
    return None


@app.get("/config/roles")
def list_config_roles(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    return config_list("roles", db, organization_id)


@app.post("/config/roles", status_code=201)
def create_config_role(payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_create("roles", payload, db)


@app.put("/config/roles/{record_id}")
def update_config_role(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("roles", record_id, payload, db)


@app.get("/config/permissions")
def list_config_permissions(db: Session = Depends(get_db)):
    rows = db.execute(text("SELECT * FROM config_permissions ORDER BY module_key, permission_name")).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/config/roles/{role_id}/permissions")
def list_role_permissions(role_id: int, db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT p.*
            FROM config_role_permissions rp
            JOIN config_permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
            ORDER BY p.module_key, p.permission_name
            """
        ),
        {"role_id": role_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.post("/config/roles/{role_id}/permissions", status_code=201)
def set_role_permissions(role_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    permission_ids = payload.data.get("permission_ids", [])
    db.execute(text("DELETE FROM config_role_permissions WHERE role_id = :role_id"), {"role_id": role_id})
    for permission_id in permission_ids:
        db.execute(
            text(
                """
                INSERT INTO config_role_permissions (role_id, permission_id)
                VALUES (:role_id, :permission_id)
                ON CONFLICT (role_id, permission_id) DO NOTHING
                """
            ),
            {"role_id": role_id, "permission_id": permission_id},
        )
    audit(db, "config_role_permissions", role_id, "replace", {"permission_ids": permission_ids})
    db.commit()
    return {"role_id": role_id, "permission_ids": permission_ids}


@app.get("/config/economic-parameters")
def list_economic_parameters(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    return config_list("economic-parameters", db, organization_id)


@app.post("/config/economic-parameters", status_code=201)
def create_economic_parameter(payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_create("economic-parameters", payload, db)


@app.put("/config/economic-parameters/{record_id}")
def update_economic_parameter(record_id: int, payload: ConfigPayload, db: Session = Depends(get_db)):
    return config_update("economic-parameters", record_id, payload, db)


@app.get("/audit-logs")
def list_audit_logs(organization_id: int = Query(default=1), db: Session = Depends(get_db)):
    rows = db.execute(
        text(
            """
            SELECT *
            FROM audit_logs
            WHERE organization_id = :organization_id OR organization_id IS NULL
            ORDER BY created_at DESC, id DESC
            LIMIT 100
            """
        ),
        {"organization_id": organization_id},
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/api/modules")
def modules():
    return [
        {"key": key, "label": value["label"], "fields": value["fields"]}
        for key, value in MODULES.items()
    ]


@app.get("/api/dashboard")
def dashboard(db: Session = Depends(get_db)):
    cards = []
    for key, config in MODULES.items():
        if not config["table"]:
            continue
        count = db.execute(text(f"SELECT COUNT(*) FROM {config['table']}")).scalar_one()
        cards.append({"key": key, "label": config["label"], "count": count})

    upcoming = db.execute(
        text(
            """
            SELECT id, title, area, due_date, priority, status
            FROM deadlines
            WHERE status <> 'chiusa'
            ORDER BY due_date ASC
            LIMIT 8
            """
        )
    ).fetchall()

    return {"cards": cards, "upcoming_deadlines": [row_to_dict(row) for row in upcoming]}


@app.get("/api/{module}")
def list_records(
    module: str,
    q: str | None = Query(default=None),
    db: Session = Depends(get_db),
):
    config = module_config(module)
    table = config["table"]
    params: dict[str, Any] = {}
    where = ""

    if q:
        clauses = [f"CAST({field} AS TEXT) ILIKE :q" for field in config["search"]]
        where = "WHERE " + " OR ".join(clauses)
        params["q"] = f"%{q}%"

    rows = db.execute(
        text(f"SELECT * FROM {table} {where} ORDER BY id DESC LIMIT 200"),
        params,
    ).fetchall()
    return [row_to_dict(row) for row in rows]


@app.get("/api/{module}/{record_id}")
def get_record(module: str, record_id: int, db: Session = Depends(get_db)):
    config = module_config(module)
    row = db.execute(
        text(f"SELECT * FROM {config['table']} WHERE id = :id"),
        {"id": record_id},
    ).fetchone()
    if not row:
        raise HTTPException(status_code=404, detail="Record non trovato")
    return row_to_dict(row)


@app.post("/api/{module}", status_code=201)
def create_record(module: str, payload: RecordPayload, db: Session = Depends(get_db)):
    config = module_config(module)
    data = clean_payload(config, payload.data, include_defaults=True)
    if not data:
        raise HTTPException(status_code=400, detail="Nessun campo valido")

    columns = ", ".join(data.keys())
    placeholders = ", ".join([f":{key}" for key in data.keys()])
    row = db.execute(
        text(f"INSERT INTO {config['table']} ({columns}) VALUES ({placeholders}) RETURNING *"),
        data,
    ).fetchone()
    db.commit()
    return row_to_dict(row)


@app.put("/api/{module}/{record_id}")
def update_record(module: str, record_id: int, payload: RecordPayload, db: Session = Depends(get_db)):
    config = module_config(module)
    data = clean_payload(config, payload.data, include_defaults=False)
    if not data:
        raise HTTPException(status_code=400, detail="Nessun campo valido")

    assignments = ", ".join([f"{key} = :{key}" for key in data.keys()])
    data["id"] = record_id
    row = db.execute(
        text(
            f"""
            UPDATE {config['table']}
            SET {assignments}, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
            RETURNING *
            """
        ),
        data,
    ).fetchone()
    db.commit()
    if not row:
        raise HTTPException(status_code=404, detail="Record non trovato")
    return row_to_dict(row)


@app.delete("/api/{module}/{record_id}", status_code=204)
def delete_record(module: str, record_id: int, db: Session = Depends(get_db)):
    config = module_config(module)
    result = db.execute(
        text(f"DELETE FROM {config['table']} WHERE id = :id"),
        {"id": record_id},
    )
    db.commit()
    if result.rowcount == 0:
        raise HTTPException(status_code=404, detail="Record non trovato")
    return None
