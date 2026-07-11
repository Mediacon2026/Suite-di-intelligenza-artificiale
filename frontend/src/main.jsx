import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  BriefcaseBusiness,
  Building2,
  CalendarClock,
  CreditCard,
  FileText,
  GraduationCap,
  LayoutDashboard,
  LifeBuoy,
  Pencil,
  Plus,
  RefreshCw,
  Save,
  Search,
  Settings,
  Trash2,
  UserRound,
  UsersRound,
  X,
} from 'lucide-react';
import './styles.css';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

const modules = [
  { key: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { key: 'intake', label: 'Intake Review', icon: FileText },
  { key: 'crm', label: 'CRM', icon: UsersRound },
  { key: 'mediazioni', label: 'Mediazioni', icon: Building2 },
  { key: 'fascicoli', label: 'Fascicoli', icon: FileText },
  { key: 'formazione', label: 'Formazione', icon: GraduationCap },
  { key: 'orientamento', label: 'Orientamento', icon: UserRound },
  { key: 'occ', label: 'OCC', icon: LifeBuoy },
  { key: 'crisi-impresa', label: 'Crisi Impresa', icon: BriefcaseBusiness },
  { key: 'documenti', label: 'Documenti', icon: FileText },
  { key: 'pagamenti', label: 'Pagamenti', icon: CreditCard },
  { key: 'scadenze', label: 'Scadenze', icon: CalendarClock },
  { key: 'impostazioni', label: 'Impostazioni', icon: Settings },
];

const emptyContact = {
  contact_type: 'persona',
  first_name: '',
  last_name: '',
  company_name: '',
  fiscal_code: '',
  vat_number: '',
  email: '',
  pec: '',
  phone: '',
  mobile: '',
  address: '',
  city: '',
  province: '',
  zip_code: '',
  notes: '',
  is_lawyer: false,
  is_mediator: false,
  is_trainer: false,
  is_student: false,
  is_debtor: false,
  is_creditor: false,
  is_client: false,
  is_company: false,
};

const roleFields = [
  ['is_lawyer', 'Avvocato'],
  ['is_mediator', 'Mediatore'],
  ['is_trainer', 'Formatore'],
  ['is_student', 'Studente'],
  ['is_debtor', 'Debitore'],
  ['is_creditor', 'Creditore'],
  ['is_client', 'Cliente'],
  ['is_company', 'Azienda'],
];

const emptyMediation = {
  organization_id: 1,
  office_id: '',
  internal_number: '',
  dgstat_number: '',
  year: new Date().getFullYear(),
  quarter: Math.floor(new Date().getMonth() / 3) + 1,
  deposit_date: new Date().toISOString().slice(0, 10),
  first_meeting_date: '',
  closing_date: '',
  status: 'depositata',
  outcome: '',
  mediation_type: 'obbligatoria',
  matter: '',
  submatter: '',
  claim_value: '',
  object: '',
  reasons: '',
  mediator_id: '',
  tariff_id: '',
  notes: '',
};

const emptyCase = {
  organization_id: 1,
  office_id: '',
  case_type: 'mediation',
  case_number: '',
  title: '',
  status: 'aperto',
  priority: 'media',
  opened_at: new Date().toISOString().slice(0, 10),
  closed_at: '',
  related_entity_id: '',
  related_entity_type: '',
  notes: '',
};

const caseTabs = ['Documenti', 'Checklist', 'Timeline', 'Assistente AI', 'Dati estratti'];

const configSections = [
  ['overview', 'Panoramica'],
  ['modules', 'Moduli'],
  ['numbering', 'Numerazioni'],
  ['matters', 'Materie'],
  ['workflows', 'Workflow'],
  ['roles', 'Ruoli'],
  ['economics', 'Motore Economico'],
  ['logs', 'Log'],
];

async function api(path, options = {}) {
  const response = await fetch(`${API_URL}${path}`, {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  });
  if (!response.ok) {
    const error = await response.json().catch(() => ({ detail: 'Errore inatteso' }));
    throw new Error(error.detail || 'Errore inatteso');
  }
  if (response.status === 204) return null;
  return response.json();
}

async function uploadApi(path, formData) {
  const response = await fetch(`${API_URL}${path}`, {
    method: 'POST',
    body: formData,
  });
  if (!response.ok) {
    const error = await response.json().catch(() => ({ detail: 'Errore inatteso' }));
    throw new Error(error.detail || 'Errore inatteso');
  }
  return response.json();
}

function fieldValue(value) {
  if (Array.isArray(value)) return value.join(', ');
  if (value && typeof value === 'object') return JSON.stringify(value);
  return value ?? '';
}

function App() {
  const [active, setActive] = useState('dashboard');
  const activeModule = useMemo(() => modules.find((module) => module.key === active), [active]);

  return (
    <main className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <span className="brand-mark">M</span>
          <div>
            <strong>Mediacon Hub</strong>
            <small>ERP</small>
          </div>
        </div>
        <nav className="nav-list">
          {modules.map((module) => {
            const Icon = module.icon;
            return (
              <button
                key={module.key}
                className={active === module.key ? 'active' : ''}
                onClick={() => setActive(module.key)}
                title={module.label}
              >
                <Icon size={18} />
                <span>{module.label}</span>
              </button>
            );
          })}
        </nav>
      </aside>

      <section className="workspace">
        <header className="topbar">
          <div>
            <h1>{activeModule.label}</h1>
            <p>Mediacon Hub ERP</p>
          </div>
        </header>

        {active === 'dashboard' && <Dashboard />}
        {active === 'intake' && <IntakeReviewPage />}
        {active === 'crm' && <CrmPage />}
        {active === 'mediazioni' && <MediationPage />}
        {active === 'fascicoli' && <CasesPage />}
        {active === 'impostazioni' && <ConfigurationCenter />}
        {active !== 'dashboard' && active !== 'intake' && active !== 'crm' && active !== 'mediazioni' && active !== 'fascicoli' && active !== 'impostazioni' && <EmptyModule module={activeModule} />}
      </section>
    </main>
  );
}

function Dashboard() {
  const [status, setStatus] = useState('checking');
  const [checkedAt, setCheckedAt] = useState('');
  const [intakeStats, setIntakeStats] = useState({ in_review: 0, ready: 0, created: 0, error: 0 });
  const [automationStats, setAutomationStats] = useState({ executed_today: 0, pending: 0, error: 0, manual: 0 });

  async function checkBackend() {
    setStatus('checking');
    try {
      const data = await api('/health');
      setStatus(data.status === 'ok' ? 'online' : 'offline');
      setIntakeStats(await api('/intake-sessions/stats'));
      setAutomationStats(await api('/automation/dashboard/stats'));
    } catch {
      setStatus('offline');
    } finally {
      setCheckedAt(new Date().toLocaleTimeString('it-IT'));
    }
  }

  useEffect(() => {
    checkBackend();
  }, []);

  return (
    <section className="dashboard-grid">
      <div className="table-panel health-panel">
        <div className="panel-heading">
          <h2>Stato sistema</h2>
          <button className="icon-button" onClick={checkBackend} title="Aggiorna">
            <RefreshCw size={18} />
          </button>
        </div>
        <div className="health-content">
          <span className={`health-dot ${status}`} />
          <div>
            <strong>
              Backend {status === 'online' ? 'online' : status === 'offline' ? 'offline' : 'in verifica'}
            </strong>
            <p>Endpoint controllato: {API_URL}/health</p>
            {checkedAt && <small>Ultimo controllo: {checkedAt}</small>}
          </div>
        </div>
      </div>
      <div className="table-panel">
        <div className="panel-heading">
          <h2>Nuove acquisizioni</h2>
        </div>
        <div className="stats-grid">
          <Info label="In revisione" value={intakeStats.in_review} />
          <Info label="Pronte" value={intakeStats.ready} />
          <Info label="Create" value={intakeStats.created} />
          <Info label="Errore" value={intakeStats.error} />
        </div>
      </div>
      <div className="table-panel">
        <div className="panel-heading">
          <h2>Automazioni</h2>
        </div>
        <div className="stats-grid">
          <Info label="Eseguite oggi" value={automationStats.executed_today} />
          <Info label="In attesa" value={automationStats.pending} />
          <Info label="Errore" value={automationStats.error} />
          <Info label="Manuali" value={automationStats.manual} />
        </div>
      </div>
    </section>
  );
}

function IntakeReviewPage() {
  const [sessions, setSessions] = useState([]);
  const [selected, setSelected] = useState(null);
  const [reviewData, setReviewData] = useState({});
  const [offices, setOffices] = useState([]);
  const [step, setStep] = useState(1);
  const [message, setMessage] = useState('');

  async function loadSessions() {
    try {
      const data = await api('/intake-sessions');
      setSessions(data);
      if (!selected && data[0]) {
        setSelected(data[0]);
        setReviewData(data[0].extracted_json || {});
      }
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function loadOffices() {
    try {
      setOffices(await api('/offices'));
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function uploadFiles(files) {
    if (!files?.length) return;
    try {
      const formData = new FormData();
      Array.from(files).forEach((file) => formData.append('files', file));
      const session = await uploadApi('/intake-sessions/upload', formData);
      setSelected(session);
      setReviewData(session.extracted_json || {});
      await loadSessions();
      setMessage('Documenti acquisiti e classificati.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function confirmReview() {
    if (!selected) return;
    try {
      const session = await api(`/intake-sessions/${selected.id}/review-confirm`, {
        method: 'POST',
        body: JSON.stringify({ extracted_data: reviewData }),
      });
      setSelected(session);
      setMessage('Review confermata.');
      await loadSessions();
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function createProceeding() {
    if (!selected) return;
    if (!reviewData.office_id && !reviewData.office_city) {
      setMessage('Seleziona la sede prima di creare la procedura completa.');
      setStep(3);
      return;
    }
    try {
      const result = await api(`/intake/${selected.id}/confirm-and-create-mediation`, {
        method: 'POST',
        body: JSON.stringify({ extracted_data: reviewData }),
      });
      setSelected(result.intake_session);
      setMessage(`Procedimento creato: ${result.mediation.internal_number}`);
      await loadSessions();
    } catch (error) {
      setMessage(error.message);
    }
  }

  useEffect(() => {
    loadSessions();
    loadOffices();
  }, []);

  function updateField(field, value) {
    setReviewData({ ...reviewData, [field]: value });
  }

  return (
    <section className="intake-review-page">
      <div className="table-panel">
        <div className="panel-heading">
          <h2>Intake Review</h2>
          <div className="upload-actions">
            <label className="upload-inline">
              <FileText size={16} />
              Carica documenti
              <input multiple type="file" accept=".pdf,.docx,.odt,.zip,.jpg,.jpeg,.png,.eml,.msg" onChange={(event) => uploadFiles(event.target.files)} />
            </label>
            <label className="upload-inline">
              <FileText size={16} />
              Carica cartella
              <input multiple type="file" webkitdirectory="" directory="" onChange={(event) => uploadFiles(event.target.files)} />
            </label>
          </div>
        </div>
        {message && <div className="notice">{message}</div>}
        <div className="intake-session-list">
          {sessions.map((session) => (
            <button
              type="button"
              key={session.id}
              className={selected?.id === session.id ? 'selected-row' : ''}
              onClick={() => {
                setSelected(session);
                setReviewData(session.extracted_json || {});
              }}
            >
              #{session.id} - {session.status} - {session.documents?.length || 0} documenti
            </button>
          ))}
          {!sessions.length && <p className="muted">Nessuna acquisizione presente.</p>}
        </div>
      </div>

      {selected && (
        <>
        <div className="wizard-steps">
          {[
            [1, 'Documenti'],
            [2, 'Dati estratti'],
            [3, 'Dati operativi'],
            [4, 'Riepilogo'],
          ].map(([key, label]) => (
            <button key={key} className={step === key ? 'active' : ''} onClick={() => setStep(key)}>{label}</button>
          ))}
        </div>
        <div className="intake-review-grid">
          {step === 1 && (
          <section className="table-panel">
            <div className="panel-heading"><h2>Documenti</h2></div>
            <SimpleTable columns={['Tipo', 'File', 'Modulo', 'Confidence']} rows={(selected.documents || []).map((document) => [
              document.document_type,
              document.filename,
              document.module || '-',
              `${Math.round(Number(document.confidence || 0) * 100)}%`,
            ])} />
          </section>
          )}

          {step === 2 && (
          <section className="table-panel">
            <div className="panel-heading"><h2>Dati estratti</h2></div>
            <div className="form-grid">
              {[
                ['organization_name', 'Organismo'],
                ['office_name', 'Sede'],
                ['internal_number', 'Numero protocollo'],
                ['deposit_date', 'Data deposito'],
                ['deposit_time', 'Ora deposito'],
                ['mediation_type', 'Tipologia mediazione'],
                ['matter', 'Materia'],
                ['claim_value', 'Valore'],
                ['competent_court', 'Giudice competente'],
                ['object', 'Oggetto'],
                ['reasons', 'Ragioni della pretesa'],
                ['claimant', 'Parte istante'],
                ['claimant_lawyer', 'Avvocato'],
                ['invited_party', 'Invitato'],
                ['invited_party_lawyer', 'Avvocato invitato'],
                ['pec_addresses', 'PEC'],
                ['email_addresses', 'Mail'],
                ['phones', 'Telefoni'],
                ['addresses', 'Indirizzi'],
              ].map(([field, label]) => (
                <label key={field} className={['object', 'reasons', 'addresses'].includes(field) ? 'wide' : ''}>
                  <span>{label}</span>
                  <input value={fieldValue(reviewData[field])} onChange={(event) => updateField(field, event.target.value)} />
                  <small className="field-hint">
                    {Math.round(Number(selected.field_metadata?.[field]?.confidence || 0) * 100)}% - {selected.field_metadata?.[field]?.source_document || 'sorgente non disponibile'} - {selected.field_metadata?.[field]?.status || 'da_verificare'}
                  </small>
                </label>
              ))}
            </div>
          </section>
          )}

          {step === 2 && (
          <section className="table-panel">
            <div className="panel-heading"><h2>Dati mancanti</h2></div>
            <SimpleTable columns={['Campo', 'Stato']} rows={(selected.missing_fields || []).map((field) => [
              field,
              'da verificare',
            ])} />
            <DetailSection title="Documenti presenti">
              {(selected.documents || []).map((document) => <p key={document.id}>{document.document_type}</p>)}
            </DetailSection>
            <DetailSection title="Documenti mancanti">
              {['istanza_mediazione', 'procura', 'documento_identita', 'ricevuta_pagamento'].filter((type) => !(selected.documents || []).some((doc) => doc.document_type === type)).map((type) => <p key={type}>{type}</p>)}
            </DetailSection>
          </section>
          )}

          {step === 3 && (
          <section className="table-panel">
            <div className="panel-heading"><h2>Dati operativi</h2></div>
            <div className="form-grid">
              <label>
                <span>Sede</span>
                <select value={reviewData.office_id || ''} onChange={(event) => updateField('office_id', event.target.value)}>
                  <option value="">Da riconoscere/selezionare</option>
                  {offices.map((office) => <option key={office.id} value={office.id}>{office.name}</option>)}
                </select>
              </label>
              <label><span>Mediatore</span><input value={reviewData.mediator_name || ''} onChange={(event) => updateField('mediator_name', event.target.value)} /></label>
              <label><span>Data primo incontro</span><input type="date" value={reviewData.first_meeting_date || ''} onChange={(event) => updateField('first_meeting_date', event.target.value)} /></label>
              <label><span>Ora primo incontro</span><input type="time" value={reviewData.first_meeting_time || ''} onChange={(event) => updateField('first_meeting_time', event.target.value)} /></label>
              <label>
                <span>Modalita</span>
                <select value={reviewData.meeting_mode || 'presenza'} onChange={(event) => updateField('meeting_mode', event.target.value)}>
                  <option value="presenza">Presenza</option>
                  <option value="telematica">Telematica</option>
                  <option value="mista">Mista</option>
                </select>
              </label>
              <label className="wide"><span>Luogo incontro / Webex</span><input value={reviewData.meeting_location || reviewData.webex_link || ''} onChange={(event) => updateField('meeting_location', event.target.value)} /></label>
            </div>
          </section>
          )}

          {step === 4 && (
          <section className="table-panel">
            <div className="panel-heading"><h2>Riepilogo e azioni</h2></div>
            <SimpleTable columns={['Voce', 'Valore']} rows={[
              ['Procedura', reviewData.internal_number || 'Numero generato da Configuration Center'],
              ['Sede', offices.find((office) => String(office.id) === String(reviewData.office_id))?.name || reviewData.office_city || 'Da verificare'],
              ['Parti', `${reviewData.claimant || 'Istante da verificare'} / ${reviewData.invited_party || 'Invitato da verificare'}`],
              ['Avvocati', `${reviewData.claimant_lawyer || '-'} / ${reviewData.invited_party_lawyer || '-'}`],
              ['Documenti', `${selected.documents?.length || 0}`],
              ['Tariffario', 'Tariffario attivo organismo'],
              ['Spese iniziali', 'Calcolate in creazione'],
              ['Mediatore', reviewData.mediator_name || 'Da assegnare'],
              ['Incontro', `${reviewData.first_meeting_date || '-'} ${reviewData.first_meeting_time || ''} - ${reviewData.meeting_mode || 'presenza'}`],
              ['Documenti generati', 'Assunzione, imparzialita, convocazione, adesione'],
            ]} />
            <button className="primary-button" onClick={confirmReview}>
              <Save size={18} />
              Conferma review
            </button>
            <button className="primary-button" onClick={createProceeding}>
              <Plus size={18} />
              Crea procedura completa
            </button>
            <p className="notice compact-notice">La creazione genera fascicolo, mediazione, tariffario, primo incontro, documenti iniziali, timeline, audit e prossima azione.</p>
          </section>
          )}
        </div>
        </>
      )}
    </section>
  );
}

function CasesPage() {
  const [cases, setCases] = useState([]);
  const [selected, setSelected] = useState(null);
  const [editing, setEditing] = useState(null);
  const [tab, setTab] = useState('Documenti');
  const [caseData, setCaseData] = useState({ documents: [], timeline: [], tasks: [], deadlines: [], contacts: [], checklist: { items: [], completion_percentage: 0 }, suggestions: [] });
  const [assistantQuestion, setAssistantQuestion] = useState('');
  const [assistantAnswer, setAssistantAnswer] = useState(null);
  const [filters, setFilters] = useState({ q: '', case_type: '', status: '' });
  const [message, setMessage] = useState('');

  async function loadCases(nextFilters = filters) {
    const params = new URLSearchParams();
    Object.entries(nextFilters).forEach(([key, value]) => value && params.set(key, value));
    try {
      const data = await api(`/cases${params.toString() ? `?${params}` : ''}`);
      setCases(data);
      if (!selected && data[0]) await selectCase(data[0]);
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function loadCaseRelated(caseId) {
    const [documents, timeline, tasks, deadlines, contacts, checklist, suggestions] = await Promise.all([
      api(`/cases/${caseId}/documents`),
      api(`/cases/${caseId}/timeline`),
      api(`/cases/${caseId}/tasks`),
      api(`/cases/${caseId}/deadlines`),
      api(`/cases/${caseId}/contacts`),
      api(`/cases/${caseId}/checklist`),
      api(`/cases/${caseId}/suggestions`),
    ]);
    setCaseData({ documents, timeline, tasks, deadlines, contacts, checklist, suggestions: suggestions.suggestions || [] });
  }

  async function selectCase(item) {
    try {
      const detail = await api(`/cases/${item.id}`);
      setSelected(detail);
      await loadCaseRelated(item.id);
    } catch (error) {
      setMessage(error.message);
    }
  }

  useEffect(() => {
    loadCases();
  }, []);

  async function saveCase(event) {
    event.preventDefault();
    try {
      const created = await api('/cases', { method: 'POST', body: JSON.stringify(normalizeCaseForm(editing)) });
      setEditing(null);
      await loadCases();
      await selectCase(created);
      setMessage('Fascicolo creato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function uploadCaseFile(file) {
    if (!file || !selected) return;
    const formData = new FormData();
    formData.append('file', file);
    try {
      await uploadApi(`/cases/${selected.id}/documents`, formData);
      await loadCaseRelated(selected.id);
      setMessage('Documento caricato e classificato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function uploadCaseFiles(files) {
    if (!files?.length || !selected) return;
    const formData = new FormData();
    Array.from(files).forEach((file) => formData.append('files', file));
    try {
      await uploadApi(`/cases/${selected.id}/documents/bulk`, formData);
      await loadCaseRelated(selected.id);
      setMessage('Documenti caricati e classificati.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function uploadCaseZip(file) {
    if (!file || !selected) return;
    const formData = new FormData();
    formData.append('file', file);
    try {
      await uploadApi(`/cases/${selected.id}/upload-zip`, formData);
      await loadCaseRelated(selected.id);
      setMessage('Pacchetto ZIP caricato e registrato in timeline.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function createMediationFromCase() {
    if (!selected) return;
    try {
      const mediation = await api(`/cases/${selected.id}/create-mediation`, { method: 'POST' });
      await selectCase(selected);
      setMessage(`Mediazione creata: ${mediation.internal_number}`);
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function askAssistant() {
    if (!selected || !assistantQuestion.trim()) return;
    try {
      const answer = await api(`/cases/${selected.id}/assistant`, {
        method: 'POST',
        body: JSON.stringify({ question: assistantQuestion }),
      });
      setAssistantAnswer(answer);
      await loadCaseRelated(selected.id);
    } catch (error) {
      setMessage(error.message);
    }
  }

  return (
    <section className="case-layout">
      <div className="table-panel case-list">
        <div className="panel-heading crm-toolbar">
          <h2>Fascicoli</h2>
          <button className="primary-button" onClick={() => setEditing({ ...emptyCase })}>
            <Plus size={18} />
            Nuovo fascicolo
          </button>
        </div>
        <div className="crm-search">
          <div className="search-box">
            <Search size={17} />
            <input value={filters.q} onChange={(event) => setFilters({ ...filters, q: event.target.value })} placeholder="Cerca fascicolo" />
          </div>
          <select value={filters.case_type} onChange={(event) => setFilters({ ...filters, case_type: event.target.value })}>
            <option value="">Tipo</option>
            <option value="mediation">Mediazione</option>
            <option value="training">Formazione</option>
            <option value="orientation">Orientamento</option>
            <option value="occ">OCC</option>
            <option value="crisis">Crisi</option>
            <option value="advisor">Advisor</option>
          </select>
          <select value={filters.status} onChange={(event) => setFilters({ ...filters, status: event.target.value })}>
            <option value="">Stato</option>
            <option value="aperto">Aperto</option>
            <option value="in_corso">In corso</option>
            <option value="chiuso">Chiuso</option>
          </select>
          <button className="icon-button" onClick={() => loadCases(filters)} title="Cerca"><RefreshCw size={18} /></button>
        </div>
        {message && <div className="notice">{message}</div>}
        <div className="table-scroll">
          <table>
            <thead>
              <tr><th>Numero</th><th>Titolo</th><th>Tipo</th><th>Stato</th><th>Priorita</th><th>Sede</th></tr>
            </thead>
            <tbody>
              {cases.map((item) => (
                <tr key={item.id} className={selected?.id === item.id ? 'selected-row' : ''} onClick={() => selectCase(item)}>
                  <td>{item.case_number}</td>
                  <td>{item.title}</td>
                  <td>{item.case_type}</td>
                  <td><span className="status-pill">{item.status}</span></td>
                  <td>{item.priority}</td>
                  <td>{item.office_name || ''}</td>
                </tr>
              ))}
              {cases.length === 0 && <tr><td className="empty-state" colSpan="6">Nessun fascicolo trovato.</td></tr>}
            </tbody>
          </table>
        </div>
      </div>

      <CaseDetail
        selected={selected}
        tab={tab}
        setTab={setTab}
        caseData={caseData}
        onUploadFile={uploadCaseFile}
        onUploadFiles={uploadCaseFiles}
        onUploadZip={uploadCaseZip}
        onCreateMediation={createMediationFromCase}
        assistantQuestion={assistantQuestion}
        setAssistantQuestion={setAssistantQuestion}
        assistantAnswer={assistantAnswer}
        onAskAssistant={askAssistant}
      />

      {editing && (
        <CaseModal caseRecord={editing} setCaseRecord={setEditing} onSubmit={saveCase} onClose={() => setEditing(null)} />
      )}
    </section>
  );
}

function CrmPage() {
  const [contacts, setContacts] = useState([]);
  const [selected, setSelected] = useState(null);
  const [editing, setEditing] = useState(null);
  const [query, setQuery] = useState('');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');

  async function loadContacts(search = query) {
    setLoading(true);
    setMessage('');
    try {
      const qs = search ? `?q=${encodeURIComponent(search)}` : '';
      const data = await api(`/contacts${qs}`);
      setContacts(data);
      if (selected) {
        const updated = data.find((contact) => contact.id === selected.id);
        setSelected(updated || data[0] || null);
      } else {
        setSelected(data[0] || null);
      }
    } catch (error) {
      setMessage(error.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadContacts('');
  }, []);

  async function saveContact(event) {
    event.preventDefault();
    const isExisting = Boolean(editing.id);
    const path = isExisting ? `/contacts/${editing.id}` : '/contacts';
    const method = isExisting ? 'PUT' : 'POST';
    try {
      const saved = await api(path, { method, body: JSON.stringify(editing) });
      setEditing(null);
      await loadContacts(query);
      setSelected(saved);
      setMessage('Contatto salvato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function deleteContact(contact) {
    const name = displayName(contact);
    if (!confirm(`Eliminare ${name}?`)) return;
    try {
      await api(`/contacts/${contact.id}`, { method: 'DELETE' });
      setSelected(null);
      await loadContacts(query);
      setMessage('Contatto eliminato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  return (
    <section className="crm-layout">
      <div className="table-panel crm-list">
        <div className="panel-heading crm-toolbar">
          <h2>Master Anagrafica</h2>
          <button className="primary-button" onClick={() => setEditing({ ...emptyContact })}>
            <Plus size={18} />
            Nuovo contatto
          </button>
        </div>
        <div className="crm-search">
          <div className="search-box">
            <Search size={17} />
            <input
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              onKeyDown={(event) => event.key === 'Enter' && loadContacts(query)}
              placeholder="Cerca nome, societa, email, PEC, CF o P. IVA"
            />
          </div>
          <button className="icon-button" onClick={() => loadContacts(query)} title="Cerca">
            <RefreshCw size={18} />
          </button>
        </div>
        {message && <div className="notice">{message}</div>}
        {loading && <div className="notice">Caricamento contatti...</div>}
        <div className="table-scroll">
          <table>
            <thead>
              <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Email</th>
                <th>PEC</th>
                <th>Telefono</th>
                <th>Ruoli</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              {contacts.map((contact) => (
                <tr
                  key={contact.id}
                  className={selected?.id === contact.id ? 'selected-row' : ''}
                  onClick={() => setSelected(contact)}
                >
                  <td>{displayName(contact)}</td>
                  <td>{contact.contact_type}</td>
                  <td>{contact.email || ''}</td>
                  <td>{contact.pec || ''}</td>
                  <td>{contact.mobile || contact.phone || ''}</td>
                  <td>{contactRoles(contact).join(', ')}</td>
                  <td className="row-actions" onClick={(event) => event.stopPropagation()}>
                    <button onClick={() => setEditing(toFormContact(contact))}>
                      <Pencil size={16} />
                      Modifica
                    </button>
                    <button className="danger" onClick={() => deleteContact(contact)} title="Elimina">
                      <Trash2 size={16} />
                    </button>
                  </td>
                </tr>
              ))}
              {contacts.length === 0 && (
                <tr>
                  <td className="empty-state" colSpan="7">Nessun contatto trovato.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <ContactDetail
        contact={selected}
        onEdit={(contact) => setEditing(toFormContact(contact))}
      />

      {editing && (
        <ContactModal
          contact={editing}
          setContact={setEditing}
          onSubmit={saveContact}
          onClose={() => setEditing(null)}
        />
      )}
    </section>
  );
}

function CaseDetail({ selected, tab, setTab, caseData, onUploadFile, onUploadFiles, onUploadZip, onCreateMediation, assistantQuestion, setAssistantQuestion, assistantAnswer, onAskAssistant }) {
  if (!selected) {
    return (
      <aside className="table-panel case-detail">
        <div className="empty-state">Seleziona o crea un fascicolo.</div>
      </aside>
    );
  }

  return (
    <aside className="table-panel case-detail">
      <div className="panel-heading">
        <div>
          <h2>{selected.case_number}</h2>
          <span>{selected.case_type} - {selected.status} - {selected.office_name || 'sede non indicata'}</span>
        </div>
        {selected.case_type === 'mediation' && !selected.related_entity_id && (
          <button className="primary-button" onClick={onCreateMediation}>Crea mediazione da fascicolo</button>
        )}
      </div>
      <div className="case-tabs">
        {caseTabs.map((item) => <button key={item} className={tab === item ? 'active' : ''} onClick={() => setTab(item)}>{item}</button>)}
      </div>
      <div className="config-body">
        {tab === 'Documenti' && (
          <>
            <div
              className="drop-zone"
              onDragOver={(event) => event.preventDefault()}
              onDrop={(event) => {
                event.preventDefault();
                onUploadFiles(event.dataTransfer.files);
              }}
            >
              Trascina qui PDF, immagini, PEC, EML, MSG o altri documenti
            </div>
            <div className="row-actions">
              <label className="upload-inline">
                <FileText size={16} />
                Upload file
                <input type="file" onChange={(event) => onUploadFile(event.target.files?.[0])} />
              </label>
              <label className="upload-inline">
                <FileText size={16} />
                Upload multiplo
                <input multiple type="file" onChange={(event) => onUploadFiles(event.target.files)} />
              </label>
              <label className="upload-inline">
                <FileText size={16} />
                Upload ZIP
                <input type="file" accept=".zip,application/zip" onChange={(event) => onUploadZip(event.target.files?.[0])} />
              </label>
            </div>
            <SimpleTable columns={['Tipo', 'File', 'Classificazione', 'Firma/CAD', 'Dimensione']} rows={caseData.documents.map((doc) => [
              doc.document_type,
              doc.original_filename || doc.filename,
              doc.classification_status,
              <DocumentSignatureStatus key={doc.id || doc.filename} status={doc.signature_status || doc.preservation_status} />,
              doc.file_size || '',
            ])} />
          </>
        )}
        {tab === 'Checklist' && (
          <>
            <div className="progress-wrap">
              <strong>Completezza {Math.round(caseData.checklist.completion_percentage || 0)}%</strong>
              <div className="progress-bar"><span style={{ width: `${caseData.checklist.completion_percentage || 0}%` }} /></div>
            </div>
            <SimpleTable columns={['Voce', 'Stato']} rows={(caseData.checklist.items || []).map((item) => [
              item.label,
              <ChecklistStatus key={item.key} status={item.status} />,
            ])} />
            <DetailSection title="Suggerimenti">
              {(caseData.suggestions || []).map((item) => <p key={item}>{item}</p>)}
              {!caseData.suggestions?.length && <p className="muted">Nessun suggerimento.</p>}
            </DetailSection>
          </>
        )}
        {tab === 'Timeline' && <SimpleTable columns={['Data', 'Evento', 'Titolo', 'Descrizione']} rows={caseData.timeline.map((item) => [item.created_at, item.event_type, item.title, item.description || ''])} />}
        {tab === 'Assistente AI' && (
          <div className="assistant-panel">
            <div className="inline-form">
              <label>
                <span>Domanda</span>
                <input value={assistantQuestion} onChange={(event) => setAssistantQuestion(event.target.value)} placeholder="Chi è la parte istante?" />
              </label>
              <button className="primary-button" onClick={onAskAssistant}>Chiedi</button>
            </div>
            {assistantAnswer && (
              <DetailSection title="Risposta">
                <p>{assistantAnswer.answer}</p>
                {(assistantAnswer.suggestions || []).map((item) => <p className="muted" key={item}>{item}</p>)}
              </DetailSection>
            )}
          </div>
        )}
        {tab === 'Dati estratti' && (
          <SimpleTable columns={['Documento', 'Tipo', 'Pagine', 'Dati estratti']} rows={caseData.documents.map((doc) => [
            doc.original_filename || doc.filename,
            doc.document_type,
            doc.extracted_json?.page_count || '',
            JSON.stringify(doc.extracted_json || {}, null, 2),
          ])} />
        )}
      </div>
    </aside>
  );
}

function CaseModal({ caseRecord, setCaseRecord, onSubmit, onClose }) {
  function updateField(field, value) {
    setCaseRecord({ ...caseRecord, [field]: value });
  }

  return (
    <div className="modal-backdrop">
      <form className="modal compact-modal" onSubmit={onSubmit}>
        <header>
          <h2>Nuovo fascicolo</h2>
          <button type="button" className="icon-button" onClick={onClose} title="Chiudi"><X size={18} /></button>
        </header>
        <div className="form-grid">
          <label>
            <span>Tipo fascicolo</span>
            <select value={caseRecord.case_type} onChange={(event) => updateField('case_type', event.target.value)}>
              <option value="mediation">Mediazione</option>
              <option value="training">Formazione</option>
              <option value="orientation">Orientamento</option>
              <option value="occ">OCC</option>
              <option value="crisis">Crisi</option>
              <option value="advisor">Advisor</option>
            </select>
          </label>
          <label><span>Titolo</span><input value={caseRecord.title} onChange={(event) => updateField('title', event.target.value)} required /></label>
          <label><span>Stato</span><input value={caseRecord.status} onChange={(event) => updateField('status', event.target.value)} /></label>
          <label><span>Priorita</span><input value={caseRecord.priority} onChange={(event) => updateField('priority', event.target.value)} /></label>
          <label><span>Aperto il</span><input type="date" value={caseRecord.opened_at || ''} onChange={(event) => updateField('opened_at', event.target.value)} /></label>
          <label className="wide"><span>Note</span><textarea rows="3" value={caseRecord.notes || ''} onChange={(event) => updateField('notes', event.target.value)} /></label>
        </div>
        <footer>
          <button type="button" onClick={onClose}>Annulla</button>
          <button className="primary-button" type="submit"><Save size={18} />Salva</button>
        </footer>
      </form>
    </div>
  );
}

function MediationPage() {
  const [mediations, setMediations] = useState([]);
  const [selected, setSelected] = useState(null);
  const [editing, setEditing] = useState(null);
  const [sessionForm, setSessionForm] = useState(null);
  const [intakeOpen, setIntakeOpen] = useState(false);
  const [intakeState, setIntakeState] = useState({
    step: 1,
    pdfIntakeId: null,
    zipIntakeId: null,
    extractedData: {},
    documents: [],
    missingFields: [],
    createdMediation: null,
    mediatorName: '',
    firstMeetingDate: '',
    firstMeetingTime: '',
    firstMeetingLocation: '',
    firstMeetingMode: 'presenza',
  });
  const [economicSplit, setEconomicSplit] = useState(null);
  const [offices, setOffices] = useState([]);
  const [tariffs, setTariffs] = useState([]);
  const [query, setQuery] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);

  async function loadLookups() {
    const [officeData, tariffData] = await Promise.all([
      api('/offices'),
      api('/mediation-tariffs'),
    ]);
    setOffices(officeData);
    setTariffs(tariffData);
  }

  async function loadMediations(search = query) {
    setLoading(true);
    setMessage('');
    try {
      const qs = search ? `?q=${encodeURIComponent(search)}` : '';
      const data = await api(`/mediations${qs}`);
      setMediations(data);
      if (selected) {
        const updated = data.find((mediation) => mediation.id === selected.id);
        setSelected(updated ? await api(`/mediations/${updated.id}`) : data[0] ? await api(`/mediations/${data[0].id}`) : null);
      } else {
        setSelected(data[0] ? await api(`/mediations/${data[0].id}`) : null);
      }
    } catch (error) {
      setMessage(error.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadLookups().catch((error) => setMessage(error.message));
    loadMediations('');
  }, []);

  async function selectMediation(mediation) {
    try {
      setSelected(await api(`/mediations/${mediation.id}`));
      setEconomicSplit(null);
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function saveMediation(event) {
    event.preventDefault();
    const payload = normalizeMediationForm(editing);
    const isExisting = Boolean(editing.id);
    try {
      const saved = await api(isExisting ? `/mediations/${editing.id}` : '/mediations', {
        method: isExisting ? 'PUT' : 'POST',
        body: JSON.stringify(payload),
      });
      setEditing(null);
      await loadMediations(query);
      setSelected(saved);
      setMessage('Mediazione salvata.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function deleteMediation(mediation) {
    if (!confirm(`Eliminare ${mediation.internal_number || `mediazione #${mediation.id}`}?`)) return;
    try {
      await api(`/mediations/${mediation.id}`, { method: 'DELETE' });
      setSelected(null);
      await loadMediations(query);
      setMessage('Mediazione eliminata.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function recalculateFees(mediation) {
    try {
      const updated = await api(`/mediations/${mediation.id}/calculate-fees`, { method: 'POST' });
      await loadMediations(query);
      setSelected(updated);
      setMessage('Calcolo tariffario aggiornato e salvato sulla pratica.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function calculateEconomicSplit(mediation) {
    try {
      const result = await api(`/mediations/${mediation.id}/calculate-economic-split`, { method: 'POST' });
      setEconomicSplit(result);
      setMessage('Ripartizione economica calcolata.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function generateFirstMeetingNotice(mediation) {
    try {
      const result = await api(`/mediations/${mediation.id}/generate-first-meeting-notice`, { method: 'POST' });
      await loadMediations(query);
      setSelected(result.mediation);
      setMessage('Convocazione al primo incontro generata e collegata al fascicolo.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function sendMediatorAppointment(mediation) {
    try {
      const result = await api(`/mediations/${mediation.id}/send-mediator-appointment`, { method: 'POST' });
      await loadMediations(query);
      setSelected(result.mediation);
      setMessage('Pacchetto nomina mediatore generato e invio registrato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function remindMediatorAppointment(mediation) {
    try {
      const result = await api(`/mediations/${mediation.id}/mediator-appointment-reminder`, { method: 'POST' });
      await loadMediations(query);
      setSelected(result.mediation);
      setMessage('Sollecito nomina mediatore registrato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function markMediatorSignatureReceived(mediation) {
    try {
      const result = await api(`/mediations/${mediation.id}/mediator-signature-received`, { method: 'POST' });
      await loadMediations(query);
      setSelected(result.mediation);
      setMessage('Firma/accettazione del mediatore registrata.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function completeNextAction(mediation) {
    try {
      const result = await api(`/mediations/${mediation.id}/next-action/complete`, {
        method: 'POST',
        body: JSON.stringify({ data: { action_key: mediation.next_action?.action_key } }),
      });
      await loadMediations(query);
      setSelected(result.mediation);
      setMessage('Prossima azione aggiornata.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function saveSession(event) {
    event.preventDefault();
    const {
      video_provider,
      participants,
      digital_signature_consent,
      signature_mode,
      webex_link_generated,
      ...sessionPayload
    } = sessionForm;
    try {
      await api(`/mediations/${selected.id}/sessions`, {
        method: 'POST',
        body: JSON.stringify(sessionPayload),
      });
      setSessionForm(null);
      setSelected(await api(`/mediations/${selected.id}`));
      setMessage('Incontro aggiunto.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function uploadIntakePdf(file) {
    if (!file) return;
    try {
      const formData = new FormData();
      formData.append('file', file);
      const result = await uploadApi('/mediations/intake/pdf', formData);
      const nextState = {
        ...intakeState,
        pdfIntakeId: result.intake_id,
        extractedData: { ...intakeState.extractedData, ...result.extracted_data },
        step: 2,
      };
      await reviewIntake(nextState);
      setMessage('PDF istanza analizzato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function uploadIntakeZip(file) {
    if (!file) return;
    try {
      const formData = new FormData();
      formData.append('file', file);
      const result = await uploadApi('/mediations/intake/zip', formData);
      const nextState = {
        ...intakeState,
        zipIntakeId: result.intake_id,
        documents: result.files || [],
        step: 2,
      };
      await reviewIntake(nextState);
      setMessage('ZIP documentale estratto e classificato.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function reviewIntake(state = intakeState) {
    const review = await api('/mediations/intake/review', {
      method: 'POST',
      body: JSON.stringify({
        organization_id: 1,
        office_id: state.extractedData?.office_id || null,
        pdf_intake_id: state.pdfIntakeId,
        zip_intake_id: state.zipIntakeId,
        extracted_data: state.extractedData,
        documents: state.documents,
      }),
    });
    setIntakeState({
      ...state,
      extractedData: review.extracted_data || state.extractedData,
      documents: review.documents || state.documents,
      missingFields: review.missing_fields || [],
      step: Math.max(state.step || 1, 2),
    });
  }

  async function createFromIntake() {
    if (!intakeState.mediatorName || !intakeState.firstMeetingDate || !intakeState.firstMeetingTime) {
      setMessage('Indica mediatore, data e ora del primo incontro.');
      return;
    }
    try {
      const created = await api('/mediations/create-from-documents', {
        method: 'POST',
        body: JSON.stringify({
          organization_id: 1,
          office_id: intakeState.extractedData.office_id || null,
          pdf_intake_id: intakeState.pdfIntakeId,
          zip_intake_id: intakeState.zipIntakeId,
          extracted_data: intakeState.extractedData,
          documents: intakeState.documents,
          mediator_name: intakeState.mediatorName,
          first_meeting_date: intakeState.firstMeetingDate,
          first_meeting_time: intakeState.firstMeetingTime,
          first_meeting_location: intakeState.firstMeetingLocation,
          first_meeting_mode: intakeState.firstMeetingMode,
        }),
      });
      setIntakeState({
        step: 5,
        pdfIntakeId: null,
        zipIntakeId: null,
        extractedData: {},
        documents: [],
        missingFields: [],
        createdMediation: created,
        mediatorName: '',
        firstMeetingDate: '',
        firstMeetingTime: '',
        firstMeetingLocation: '',
        firstMeetingMode: 'presenza',
      });
      await loadMediations(query);
      setSelected(created);
      setMessage('Procedura di mediazione completa creata dai documenti.');
    } catch (error) {
      setMessage(error.message);
    }
  }

  return (
    <section className="mediation-layout">
      <div className="table-panel mediation-list">
        <div className="panel-heading crm-toolbar">
          <h2>Procedimenti di Mediazione</h2>
          <div className="row-actions">
            <button onClick={() => setIntakeOpen(true)}>
              <FileText size={18} />
              Crea mediazione da documenti
            </button>
            <button className="primary-button" onClick={() => setEditing({ ...emptyMediation, tariff_id: tariffs[0]?.id || '' })}>
              <Plus size={18} />
              Nuova mediazione
            </button>
          </div>
        </div>
        <div className="crm-search">
          <div className="search-box">
            <Search size={17} />
            <input
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              onKeyDown={(event) => event.key === 'Enter' && loadMediations(query)}
              placeholder="Cerca numero, DGStat, materia, oggetto, stato"
            />
          </div>
          <button className="icon-button" onClick={() => loadMediations(query)} title="Aggiorna">
            <RefreshCw size={18} />
          </button>
        </div>
        {message && <div className="notice">{message}</div>}
        {loading && <div className="notice">Caricamento procedimenti...</div>}
        <div className="table-scroll">
          <table>
            <thead>
              <tr>
                <th>Numero interno</th>
                <th>DGStat</th>
                <th>Sede</th>
                <th>Deposito</th>
                <th>Materia</th>
                <th>Valore</th>
                <th>Tipo</th>
                <th>Stato</th>
                <th>Esito</th>
                <th>Mediatore</th>
                <th>Totale</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              {mediations.map((mediation) => (
                <tr
                  key={mediation.id}
                  className={selected?.id === mediation.id ? 'selected-row' : ''}
                  onClick={() => selectMediation(mediation)}
                >
                  <td>{mediation.internal_number}</td>
                  <td>{mediation.dgstat_number || ''}</td>
                  <td>{mediation.office_name || ''}</td>
                  <td>{mediation.deposit_date || ''}</td>
                  <td>{mediation.matter || ''}</td>
                  <td>{formatCurrency(mediation.claim_value)}</td>
                  <td>{mediation.mediation_type || ''}</td>
                  <td><span className="status-pill">{mediation.status || ''}</span></td>
                  <td>{mediation.outcome || ''}</td>
                  <td>{mediation.mediator_id || ''}</td>
                  <td>{formatCurrency(mediation.calculated_total)}</td>
                  <td className="row-actions" onClick={(event) => event.stopPropagation()}>
                    <button onClick={() => setEditing(toMediationForm(mediation))}>
                      <Pencil size={16} />
                      Modifica
                    </button>
                    <button className="danger" onClick={() => deleteMediation(mediation)} title="Elimina">
                      <Trash2 size={16} />
                    </button>
                  </td>
                </tr>
              ))}
              {mediations.length === 0 && (
                <tr>
                  <td className="empty-state" colSpan="12">Nessun procedimento trovato.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <MediationDetail
        mediation={selected}
        onEdit={(mediation) => setEditing(toMediationForm(mediation))}
        onRecalculate={recalculateFees}
        onCalculateEconomicSplit={calculateEconomicSplit}
        onGenerateFirstMeetingNotice={generateFirstMeetingNotice}
        onSendMediatorAppointment={sendMediatorAppointment}
        onRemindMediatorAppointment={remindMediatorAppointment}
        onMarkMediatorSignatureReceived={markMediatorSignatureReceived}
        onCompleteNextAction={completeNextAction}
        economicSplit={economicSplit}
        onOpenIntake={() => setIntakeOpen(true)}
        onNewSession={() => setSessionForm({
          session_number: (selected?.sessions?.length || 0) + 1,
          session_date: new Date().toISOString().slice(0, 10),
          start_time: '',
          end_time: '',
          mode: 'presenza',
          video_provider: 'WEBEX',
          location_or_link: '',
          participants: '',
          digital_signature_consent: false,
          signature_mode: 'digitale',
          webex_link_generated: false,
          outcome: '',
          notes: '',
        })}
      />

      {editing && (
        <MediationModal
          mediation={editing}
          setMediation={setEditing}
          offices={offices}
          tariffs={tariffs}
          onSubmit={saveMediation}
          onClose={() => setEditing(null)}
        />
      )}

      {sessionForm && selected && (
        <SessionModal
          session={sessionForm}
          setSession={setSessionForm}
          onSubmit={saveSession}
          onClose={() => setSessionForm(null)}
        />
      )}

      {intakeOpen && (
        <IntakeModal
          intakeState={intakeState}
          setIntakeState={setIntakeState}
          onUploadPdf={uploadIntakePdf}
          onUploadZip={uploadIntakeZip}
          offices={offices}
          onCreate={createFromIntake}
          onClose={() => {
            setIntakeOpen(false);
            setIntakeState({
              step: 1,
              pdfIntakeId: null,
              zipIntakeId: null,
              extractedData: {},
              documents: [],
              missingFields: [],
              createdMediation: null,
              mediatorName: '',
              firstMeetingDate: '',
              firstMeetingTime: '',
              firstMeetingLocation: '',
              firstMeetingMode: 'presenza',
            });
          }}
        />
      )}
    </section>
  );
}

function MediationDetail({
  mediation,
  onEdit,
  onRecalculate,
  onCalculateEconomicSplit,
  onGenerateFirstMeetingNotice,
  onSendMediatorAppointment,
  onRemindMediatorAppointment,
  onMarkMediatorSignatureReceived,
  onCompleteNextAction,
  economicSplit,
  onOpenIntake,
  onNewSession,
}) {
  if (!mediation) {
    return (
      <aside className="table-panel mediation-detail">
        <div className="empty-state">
          <p>Seleziona un procedimento per aprire la scheda.</p>
          <button className="primary-button" onClick={onOpenIntake}>
            <FileText size={18} />
            Crea mediazione da documenti
          </button>
        </div>
      </aside>
    );
  }

  return (
    <aside className="table-panel mediation-detail">
      <div className="panel-heading">
        <h2>Scheda Procedimento</h2>
        <div className="row-actions">
          <button onClick={onOpenIntake}>
            <FileText size={16} />
            Crea da documenti
          </button>
          <button className="icon-button" onClick={() => onEdit(mediation)} title="Modifica">
            <Pencil size={18} />
          </button>
        </div>
      </div>
      <div className="detail-body mediation-sections">
        <section className="next-action-box">
          <span>Prossima azione</span>
          <h3>{mediation.next_action?.title || 'Nessuna azione calcolata'}</h3>
          <p>{mediation.next_action?.description || 'La pratica non ha ancora una prossima azione disponibile.'}</p>
          <Info label="Priorita" value={mediation.next_action?.priority} />
          <Info label="Motivo" value={mediation.next_action?.reason} />
          {mediation.next_action?.action_key && (
            <button className="primary-button small-button" onClick={() => onCompleteNextAction(mediation)}>
              <Save size={16} />
              {mediation.next_action.button_label || 'Completa azione'}
            </button>
          )}
        </section>

        <DetailSection title="A. Dati generali">
          <Info label="Organismo" value={mediation.organization_name} />
          <Info label="Sede" value={mediation.office_name} />
          <Info label="Numero interno" value={mediation.internal_number} />
          <Info label="Numero DGStat" value={mediation.dgstat_number} />
          <Info label="Anno / Trimestre" value={`${mediation.year || '-'} / ${mediation.quarter || '-'}`} />
          <Info label="Data deposito" value={mediation.deposit_date} />
          <Info label="Materia" value={mediation.matter} />
          <Info label="Oggetto" value={mediation.object} />
          <Info label="Ragioni" value={mediation.reasons} />
          <Info label="Valore lite" value={formatCurrency(mediation.claim_value)} />
          <Info label="Tipologia" value={mediation.mediation_type} />
          <Info label="Stato" value={mediation.status} />
          <Info label="Esito" value={mediation.outcome} />
        </DetailSection>

        <DetailSection title="B. Parti">
          {mediation.parties?.length ? mediation.parties.map((party) => (
            <p key={party.id}>{party.party_role}: {displayName(party)} {party.legal_aid ? '- gratuito patrocinio' : ''} {party.present ? '- presente' : ''}</p>
          )) : <p className="muted">Sezione predisposta. Nessuna parte inserita.</p>}
        </DetailSection>

        <DetailSection title="C. Avvocati">
          {mediation.lawyers?.length ? mediation.lawyers.map((lawyer) => (
            <p key={lawyer.id}>{displayName(lawyer)} {lawyer.power_of_attorney ? '- procura presente' : ''}</p>
          )) : <p className="muted">Sezione predisposta. Nessun avvocato collegato.</p>}
        </DetailSection>

        <DetailSection title="D. Mediatore">
          <Info label="Mediatore assegnato" value={mediation.mediator_id || 'Da assegnare'} />
          <Info label="Stato nomina" value={mediation.mediator_assignment?.status || 'Da inviare'} />
          <Info label="Firma ricevuta" value={mediation.mediator_assignment?.accepted_at ? 'Si' : 'No'} />
          <Info label="Co-mediatore" value="Predisposto" />
          <Info label="Note" value={mediation.notes} />
          <div className="row-actions wrap-actions">
            <button className="primary-button small-button" onClick={() => onSendMediatorAppointment(mediation)}>
              <FileText size={16} />
              Invia nomina
            </button>
            <button className="small-button" onClick={() => onRemindMediatorAppointment(mediation)}>
              <RefreshCw size={16} />
              Sollecito
            </button>
            <button className="small-button" onClick={() => onMarkMediatorSignatureReceived(mediation)}>
              <Save size={16} />
              Firma ricevuta
            </button>
          </div>
          {mediation.mediator_assignment?.generated_documents?.length ? (
            <div className="mini-list">
              <strong>Documenti generati</strong>
              {mediation.mediator_assignment.generated_documents.map((document) => (
                <p key={document.id || document.filename}>{document.document_type || 'documento'} - {document.filename || ''}</p>
              ))}
            </div>
          ) : <p className="muted">Documenti nomina non ancora generati.</p>}
        </DetailSection>

        <DetailSection title="E. Incontri">
          <button className="primary-button small-button" onClick={onNewSession}>
            <Plus size={16} />
            Nuovo incontro
          </button>
          {mediation.sessions?.length ? mediation.sessions.map((session) => (
            <p key={session.id}>#{session.session_number} - {session.session_date || '-'} - {session.mode || '-'} - {session.outcome || 'esito da definire'}</p>
          )) : <p className="muted">Nessun incontro registrato.</p>}
        </DetailSection>

        <DetailSection title="F. Tariffa e calcolo">
          <Info label="Tariffario applicato" value={mediation.tariff_name} />
          <Info label="Scaglione" value={`${formatCurrency(mediation.tariff_value_min)} - ${mediation.tariff_value_max ? formatCurrency(mediation.tariff_value_max) : 'oltre'}`} />
          <Info label="Spese iniziali" value={formatCurrency(mediation.calculated_initial_expense)} />
          <Info label="Primo incontro" value={formatCurrency(mediation.calculated_first_meeting_fee)} />
          <Info label="Ulteriori incontri" value={formatCurrency(mediation.calculated_further_fee)} />
          <Info label="IVA" value={formatCurrency(mediation.calculated_vat)} />
          <Info label="Totale" value={formatCurrency(mediation.calculated_total)} />
          <button className="primary-button small-button" onClick={() => onRecalculate(mediation)}>
            <RefreshCw size={16} />
            Ricalcola
          </button>
          <p className="notice compact-notice">Il calcolo salvato resta storico anche se il tariffario cambia.</p>
        </DetailSection>

        <DetailSection title="G. Documenti">
          <button className="primary-button small-button" onClick={() => onGenerateFirstMeetingNotice(mediation)}>
            <FileText size={16} />
            Genera convocazione
          </button>
          <p className="muted">Genera DOCX e PDF della convocazione al primo incontro e collega il documento al fascicolo.</p>
        </DetailSection>

        <DetailSection title="G2. Economia">
          <button className="primary-button small-button" onClick={() => onCalculateEconomicSplit(mediation)}>
            <RefreshCw size={16} />
            Calcola ripartizione
          </button>
          {economicSplit ? (
            <>
              <Info label="Totale incassato" value={formatCurrency(economicSplit.total_collected)} />
              <Info label="Netto maturato" value={formatCurrency(economicSplit.netto_maturato)} />
              <Info label="Spese avvio netto" value={formatCurrency(economicSplit.startup_expenses_net)} />
              <Info label="Primo incontro netto" value={formatCurrency(economicSplit.first_meeting_net)} />
              <Info label="Ulteriori spese netto" value={formatCurrency(economicSplit.further_expenses_net)} />
              <Info label="IVA esclusa" value={formatCurrency(economicSplit.vat_excluded)} />
              <Info label="Quota mediatore" value={formatCurrency(economicSplit.mediator_share)} />
              <Info label="Quota sede operativa" value={formatCurrency(economicSplit.operational_office_share)} />
              <Info label="Quota Mediacon" value={formatCurrency(economicSplit.mediacon_share)} />
              <Info label="Esito considerato" value={economicSplit.mediation_outcome} />
              <Info label="Regola applicata" value={(economicSplit.applied_rules || []).map((rule) => `${rule.rule_name} ${rule.percentage}%`).join(', ') || '-'} />
              <Info label="Storico salvato" value="Netto maturato, quote e regola economica applicata sono salvati sulla mediazione" />
              <p className="notice compact-notice">{economicSplit.note}</p>
            </>
          ) : (
            <p className="muted">Calcola la ripartizione per vedere quota mediatore, sede operativa e Mediacon.</p>
          )}
        </DetailSection>

        <DetailSection title="H. Timeline">
          {mediation.timeline?.length ? (
            <div className="timeline-list">
              {mediation.timeline.map((event) => (
                <div className="timeline-item" key={event.id}>
                  <span>{event.created_at || '-'}</span>
                  <strong>{event.title || event.event_type}</strong>
                  <p>{event.description || event.event_type}</p>
                </div>
              ))}
            </div>
          ) : (
            <p className="muted">Nessun evento timeline registrato.</p>
          )}
        </DetailSection>
      </div>
    </aside>
  );
}

function MediationModal({ mediation, setMediation, offices, tariffs, onSubmit, onClose }) {
  function updateField(field, value) {
    setMediation({ ...mediation, [field]: value });
  }

  return (
    <div className="modal-backdrop">
      <form className="modal" onSubmit={onSubmit}>
        <header>
          <h2>{mediation.id ? 'Modifica mediazione' : 'Nuova mediazione'}</h2>
          <button type="button" className="icon-button" onClick={onClose} title="Chiudi">
            <X size={18} />
          </button>
        </header>
        <div className="form-grid">
          <label>
            <span>Sede</span>
            <select value={mediation.office_id || ''} onChange={(event) => updateField('office_id', event.target.value)}>
              <option value="">Seleziona sede</option>
              {offices.map((office) => <option key={office.id} value={office.id}>{office.name}</option>)}
            </select>
          </label>
          <label>
            <span>Tariffario</span>
            <select value={mediation.tariff_id || ''} onChange={(event) => updateField('tariff_id', event.target.value)}>
              <option value="">Tariffario attivo</option>
              {tariffs.map((tariff) => <option key={tariff.id} value={tariff.id}>{tariff.name}</option>)}
            </select>
          </label>
          <label>
            <span>Numero interno</span>
            <input value={mediation.internal_number || ''} onChange={(event) => updateField('internal_number', event.target.value)} placeholder="Generato se vuoto" />
          </label>
          <label>
            <span>Numero DGStat</span>
            <input value={mediation.dgstat_number || ''} onChange={(event) => updateField('dgstat_number', event.target.value)} />
          </label>
          <label>
            <span>Anno</span>
            <input type="number" value={mediation.year || ''} onChange={(event) => updateField('year', event.target.value)} />
          </label>
          <label>
            <span>Trimestre</span>
            <input type="number" min="1" max="4" value={mediation.quarter || ''} onChange={(event) => updateField('quarter', event.target.value)} />
          </label>
          <label>
            <span>Data deposito</span>
            <input type="date" value={mediation.deposit_date || ''} onChange={(event) => updateField('deposit_date', event.target.value)} />
          </label>
          <label>
            <span>Valore lite</span>
            <input type="number" step="0.01" value={mediation.claim_value || ''} onChange={(event) => updateField('claim_value', event.target.value)} />
          </label>
          <label>
            <span>Materia</span>
            <input value={mediation.matter || ''} onChange={(event) => updateField('matter', event.target.value)} />
          </label>
          <label>
            <span>Sottomateria</span>
            <input value={mediation.submatter || ''} onChange={(event) => updateField('submatter', event.target.value)} />
          </label>
          <label>
            <span>Tipologia</span>
            <select value={mediation.mediation_type || 'obbligatoria'} onChange={(event) => updateField('mediation_type', event.target.value)}>
              <option value="obbligatoria">Obbligatoria</option>
              <option value="volontaria">Volontaria</option>
              <option value="demandata">Demandata</option>
              <option value="clausola">Clausola</option>
            </select>
          </label>
          <label>
            <span>Stato</span>
            <input value={mediation.status || ''} onChange={(event) => updateField('status', event.target.value)} />
          </label>
          <label>
            <span>Esito</span>
            <input value={mediation.outcome || ''} onChange={(event) => updateField('outcome', event.target.value)} />
          </label>
          <label>
            <span>Mediatore ID</span>
            <input type="number" value={mediation.mediator_id || ''} onChange={(event) => updateField('mediator_id', event.target.value)} />
          </label>
          <label className="wide">
            <span>Oggetto</span>
            <textarea rows="2" value={mediation.object || ''} onChange={(event) => updateField('object', event.target.value)} />
          </label>
          <label className="wide">
            <span>Ragioni</span>
            <textarea rows="3" value={mediation.reasons || ''} onChange={(event) => updateField('reasons', event.target.value)} />
          </label>
          <label className="wide">
            <span>Note</span>
            <textarea rows="3" value={mediation.notes || ''} onChange={(event) => updateField('notes', event.target.value)} />
          </label>
        </div>
        <footer>
          <button type="button" onClick={onClose}>Annulla</button>
          <button type="submit" className="primary-button">
            <Save size={18} />
            Salva e calcola
          </button>
        </footer>
      </form>
    </div>
  );
}

function SessionModal({ session, setSession, onSubmit, onClose }) {
  function updateField(field, value) {
    setSession({ ...session, [field]: value });
  }

  function generateWebexLink() {
    const sessionNumber = session.session_number || '1';
    const date = session.session_date || new Date().toISOString().slice(0, 10);
    const slug = `nexus-med-${date}-${sessionNumber}`.replace(/[^a-zA-Z0-9-]/g, '-').toLowerCase();
    setSession({
      ...session,
      video_provider: 'WEBEX',
      location_or_link: `https://mediacon.webex.com/meet/${slug}`,
      webex_link_generated: true,
    });
  }

  return (
    <div className="modal-backdrop">
      <form className="modal compact-modal" onSubmit={onSubmit}>
        <header>
          <h2>Nuovo incontro</h2>
          <button type="button" className="icon-button" onClick={onClose} title="Chiudi">
            <X size={18} />
          </button>
        </header>
        <div className="form-grid">
          <label>
            <span>Numero incontro</span>
            <input type="number" value={session.session_number || ''} onChange={(event) => updateField('session_number', event.target.value)} />
          </label>
          <label>
            <span>Data</span>
            <input type="date" value={session.session_date || ''} onChange={(event) => updateField('session_date', event.target.value)} />
          </label>
          <label>
            <span>Ora inizio</span>
            <input type="time" value={session.start_time || ''} onChange={(event) => updateField('start_time', event.target.value)} />
          </label>
          <label>
            <span>Ora fine</span>
            <input type="time" value={session.end_time || ''} onChange={(event) => updateField('end_time', event.target.value)} />
          </label>
          <label>
            <span>Modalita</span>
            <select value={session.mode || 'presenza'} onChange={(event) => updateField('mode', event.target.value)}>
              <option value="presenza">Presenza</option>
              <option value="telematica">Telematica</option>
              <option value="mista">Mista</option>
            </select>
          </label>
          <label>
            <span>Provider</span>
            <select value={session.video_provider || 'WEBEX'} onChange={(event) => updateField('video_provider', event.target.value)}>
              <option value="WEBEX">Webex</option>
            </select>
          </label>
          <label className="wide webex-link-row">
            <span>Link incontro</span>
            <input value={session.location_or_link || ''} onChange={(event) => updateField('location_or_link', event.target.value)} />
            <button type="button" onClick={generateWebexLink}>
              Genera link Webex
            </button>
          </label>
          <label className="wide">
            <span>Partecipanti</span>
            <textarea
              rows="3"
              value={session.participants || ''}
              onChange={(event) => updateField('participants', event.target.value)}
              placeholder="Nome, ruolo, email/PEC, presenza o remoto"
            />
          </label>
          <label className="checkbox-label">
            <input
              type="checkbox"
              checked={Boolean(session.digital_signature_consent)}
              onChange={(event) => updateField('digital_signature_consent', event.target.checked)}
            />
            <span>Consenso firma digitale</span>
          </label>
          <label>
            <span>Modalita firma</span>
            <select value={session.signature_mode || 'digitale'} onChange={(event) => updateField('signature_mode', event.target.value)}>
              <option value="digitale">Digitale</option>
              <option value="analogica">Analogica</option>
            </select>
          </label>
          <label className="wide">
            <span>Esito incontro</span>
            <input value={session.outcome || ''} onChange={(event) => updateField('outcome', event.target.value)} />
          </label>
          <label className="wide">
            <span>Note</span>
            <textarea rows="3" value={session.notes || ''} onChange={(event) => updateField('notes', event.target.value)} />
          </label>
        </div>
        <p className="notice compact-notice">Predisposizione Webex: il link e i consensi sono pronti per il Telemediation Engine, senza chiamate reali alle API Webex.</p>
        <footer>
          <button type="button" onClick={onClose}>Annulla</button>
          <button type="submit" className="primary-button">
            <Save size={18} />
            Salva incontro
          </button>
        </footer>
      </form>
    </div>
  );
}

function IntakeModal({ intakeState, setIntakeState, onUploadPdf, onUploadZip, onCreate, onClose }) {
  const data = intakeState.extractedData || {};
  const step = intakeState.step || 1;

  function updateExtracted(field, value) {
    setIntakeState({
      ...intakeState,
      extractedData: { ...data, [field]: value },
    });
  }

  function updateField(field, value) {
    setIntakeState({ ...intakeState, [field]: value });
  }

  function goStep(nextStep) {
    setIntakeState({ ...intakeState, step: nextStep });
  }

  return (
    <div className="modal-backdrop">
      <div className="modal intake-modal">
        <header>
          <h2>Creazione automatica mediazione</h2>
          <button type="button" className="icon-button" onClick={onClose} title="Chiudi">
            <X size={18} />
          </button>
        </header>
        <div className="wizard-steps">
          {['Upload documenti', 'Dati estratti', 'Mediatore/Incontro', 'Conferma', 'Pratica creata'].map((label, index) => (
            <button key={label} type="button" className={step === index + 1 ? 'active' : ''} onClick={() => goStep(index + 1)}>
              {index + 1}. {label}
            </button>
          ))}
        </div>
        <div className="intake-grid">
          {step === 1 && <section className="config-subpanel">
            <h3>Step 1 Upload documenti</h3>
            <label className="upload-box">
              <span>PDF istanza</span>
              <input type="file" accept="application/pdf,.pdf" onChange={(event) => onUploadPdf(event.target.files?.[0])} />
            </label>
            <label className="upload-box">
              <span>ZIP documentazione</span>
              <input type="file" accept="application/zip,.zip" onChange={(event) => onUploadZip(event.target.files?.[0])} />
            </label>
            <p className="muted">L’AI locale propone i dati. La pratica nasce solo dopo conferma utente.</p>
          </section>}

          {step === 2 && <section className="config-subpanel">
            <h3>Step 2 Dati estratti</h3>
            {Boolean(intakeState.missingFields?.length) && (
              <p className="notice compact-notice">Dati mancanti marcati come "Da verificare".</p>
            )}
            <div className="form-grid intake-form">
              <label><span>Parte istante</span><input value={data.claimant || ''} onChange={(event) => updateExtracted('claimant', event.target.value)} /></label>
              <label><span>Parte invitata</span><input value={data.invited_party || ''} onChange={(event) => updateExtracted('invited_party', event.target.value)} /></label>
              <label><span>Avvocato istante</span><input value={data.claimant_lawyer || data.lawyer || ''} onChange={(event) => updateExtracted('claimant_lawyer', event.target.value)} /></label>
              <label><span>Avvocato invitato</span><input value={data.invited_party_lawyer || ''} onChange={(event) => updateExtracted('invited_party_lawyer', event.target.value)} /></label>
              <label><span>PEC</span><input value={data.pec || ''} onChange={(event) => updateExtracted('pec', event.target.value)} /></label>
              <label><span>Materia</span><input value={data.matter || ''} onChange={(event) => updateExtracted('matter', event.target.value)} /></label>
              <label><span>Valore</span><input type="number" value={data.claim_value || ''} onChange={(event) => updateExtracted('claim_value', event.target.value)} /></label>
              <label className="wide"><span>Oggetto</span><textarea rows="2" value={data.object || ''} onChange={(event) => updateExtracted('object', event.target.value)} /></label>
              <label className="wide"><span>Ragioni</span><textarea rows="3" value={data.reasons || ''} onChange={(event) => updateExtracted('reasons', event.target.value)} /></label>
            </div>
          </section>}

          {step === 2 && <section className="config-subpanel wide-panel">
            <h3>Documenti classificati</h3>
            <SimpleTable columns={['Tipo', 'File']} rows={(intakeState.documents || []).map((document) => [
              document.document_type,
              document.filename,
            ])} />
          </section>}

          {step === 3 && <section className="config-subpanel wide-panel">
            <h3>Step 3 Mediatore/Incontro</h3>
            <div className="form-grid intake-form">
              <label><span>Mediatore</span><input value={intakeState.mediatorName || ''} onChange={(event) => updateField('mediatorName', event.target.value)} /></label>
              <label><span>Data primo incontro</span><input type="date" value={intakeState.firstMeetingDate || ''} onChange={(event) => updateField('firstMeetingDate', event.target.value)} /></label>
              <label><span>Ora primo incontro</span><input type="time" value={intakeState.firstMeetingTime || ''} onChange={(event) => updateField('firstMeetingTime', event.target.value)} /></label>
              <label><span>Sede/modalita</span><input value={intakeState.firstMeetingLocation || ''} onChange={(event) => updateField('firstMeetingLocation', event.target.value)} /></label>
              <label>
                <span>Modalita incontro</span>
                <select value={intakeState.firstMeetingMode || 'presenza'} onChange={(event) => updateField('firstMeetingMode', event.target.value)}>
                  <option value="presenza">Presenza</option>
                  <option value="telematica">Telematica</option>
                  <option value="mista">Mista</option>
                </select>
              </label>
            </div>
          </section>}

          {step === 4 && <section className="config-subpanel wide-panel">
            <h3>Step 4 Conferma</h3>
            <SimpleTable columns={['Dato', 'Valore']} rows={[
              ['Parte istante', data.claimant || 'Da verificare'],
              ['Parte invitata', data.invited_party || 'Da verificare'],
              ['Materia', data.matter || 'Da verificare'],
              ['Valore', data.claim_value || 'Da verificare'],
              ['Mediatore', intakeState.mediatorName || 'Da indicare'],
              ['Primo incontro', `${intakeState.firstMeetingDate || '-'} ${intakeState.firstMeetingTime || ''}`],
              ['Documenti', `${(intakeState.documents || []).length}`],
            ]} />
            <p className="notice compact-notice">La conferma crea fascicolo, mediazione, parti, avvocati, tariffario, calcolo indennita, timeline, checklist e documenti iniziali.</p>
          </section>}

          {step === 5 && <section className="config-subpanel wide-panel">
            <h3>Step 5 Pratica creata</h3>
            <p className="notice">Procedura creata: {intakeState.createdMediation?.internal_number || 'completata'}.</p>
            <p className="muted">La pratica e' stata popolata dai documenti e dai dati confermati.</p>
          </section>}
        </div>
        <footer>
          <button type="button" onClick={onClose}>{step === 5 ? 'Chiudi' : 'Annulla'}</button>
          {step > 1 && step < 5 && <button type="button" onClick={() => goStep(step - 1)}>Indietro</button>}
          {step < 4 && <button type="button" className="primary-button" onClick={() => goStep(step + 1)}>Avanti</button>}
          {step === 4 && (
            <button type="button" className="primary-button" onClick={onCreate}>
              <Save size={18} />
              Crea pratica completa
            </button>
          )}
        </footer>
      </div>
    </div>
  );
}

function DetailSection({ title, children }) {
  return (
    <section className="detail-section">
      <h3>{title}</h3>
      <div>{children}</div>
    </section>
  );
}

function Info({ label, value }) {
  return (
    <p className="info-row">
      <span>{label}</span>
      <strong>{value || '-'}</strong>
    </p>
  );
}

function ConfigurationCenter() {
  const [section, setSection] = useState('overview');
  const [data, setData] = useState({
    overview: null,
    modules: [],
    numbering: [],
    matters: [],
    workflows: [],
    roles: [],
    permissions: [],
    economics: [],
    economicRules: [],
    offices: [],
    logs: [],
  });
  const [workflowSteps, setWorkflowSteps] = useState({});
  const [rolePermissions, setRolePermissions] = useState({});
  const [forms, setForms] = useState({
    numbering: { module_key: 'mediazioni', format_pattern: '{sequence}/2026', current_year: 2026, current_sequence: 0, reset_policy: 'yearly', active: true },
    matter: { name: '', ministerial_code: '', active: true },
    workflow: { module_key: 'mediazioni', name: '', active: true },
    step: { workflow_id: '', step_order: 1, step_key: '', step_name: '', required: true },
    role: { role_key: '', role_name: '', description: '', active: true },
    economic: { parameter_key: '', parameter_value: '', valid_from: new Date().toISOString().slice(0, 10), valid_to: '', active: true },
    economicRule: {
      office_id: '',
      rule_key: 'mediator_first_meeting_net_percentage',
      rule_name: 'Compenso mediatore sede principale',
      rule_type: 'mediator_compensation',
      percentage: 50,
      applies_to: 'netto_maturato',
      calculation_base: 'netto_maturato',
      includes_startup_expenses: true,
      includes_first_meeting_expenses: true,
      includes_further_expenses: true,
      applies_to_main_office: true,
      applies_to_operational_office: false,
      mediation_outcome: '',
      active: true,
      valid_from: new Date().toISOString().slice(0, 10),
      valid_to: '',
      notes: '',
    },
  });
  const [message, setMessage] = useState('');

  async function loadConfig() {
    setMessage('');
    try {
      const [overview, modulesData, numbering, matters, workflows, roles, permissions, economics, economicRules, offices, logs] = await Promise.all([
        api('/config/overview'),
        api('/config/modules'),
        api('/config/numbering-rules'),
        api('/config/matters'),
        api('/config/workflows'),
        api('/config/roles'),
        api('/config/permissions'),
        api('/config/economic-parameters'),
        api('/economic-rules'),
        api('/offices'),
        api('/audit-logs'),
      ]);
      setData({ overview, modules: modulesData, numbering, matters, workflows, roles, permissions, economics, economicRules, offices, logs });
      if (workflows[0]) {
        const stepPairs = await Promise.all(workflows.map(async (workflow) => [workflow.id, await api(`/config/workflows/${workflow.id}/steps`)]));
        setWorkflowSteps(Object.fromEntries(stepPairs));
      }
      if (roles[0]) {
        const permissionPairs = await Promise.all(roles.map(async (role) => [role.id, await api(`/config/roles/${role.id}/permissions`)]));
        setRolePermissions(Object.fromEntries(permissionPairs));
      }
    } catch (error) {
      setMessage(error.message);
    }
  }

  useEffect(() => {
    loadConfig();
  }, []);

  async function saveConfig(path, body, success = 'Configurazione salvata.') {
    try {
      await api(path, { method: 'POST', body: JSON.stringify({ data: body }) });
      await loadConfig();
      setMessage(success);
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function updateConfig(path, body, success = 'Configurazione aggiornata.') {
    try {
      await api(path, { method: 'PUT', body: JSON.stringify({ data: body }) });
      await loadConfig();
      setMessage(success);
    } catch (error) {
      setMessage(error.message);
    }
  }

  async function deleteConfig(path, success = 'Elemento eliminato.') {
    try {
      await api(path, { method: 'DELETE' });
      await loadConfig();
      setMessage(success);
    } catch (error) {
      setMessage(error.message);
    }
  }

  function updateForm(name, field, value) {
    setForms({ ...forms, [name]: { ...forms[name], [field]: value } });
  }

  return (
    <section className="config-center">
      <div className="config-tabs">
        {configSections.map(([key, label]) => (
          <button key={key} className={section === key ? 'active' : ''} onClick={() => setSection(key)}>
            {label}
          </button>
        ))}
      </div>

      {message && <div className="notice">{message}</div>}

      {section === 'overview' && (
        <div className="stats-grid">
          <article className="stat-card"><span>Organismo corrente</span><strong>{data.overview?.organization?.name || '-'}</strong></article>
          <article className="stat-card"><span>Moduli attivi</span><strong>{data.overview?.counts?.active_modules || 0}</strong></article>
          <article className="stat-card"><span>Sedi attive</span><strong>{data.overview?.counts?.active_offices || 0}</strong></article>
          <article className="stat-card"><span>Tariffari attivi</span><strong>{data.overview?.counts?.active_tariffs || 0}</strong></article>
          <article className="stat-card"><span>Workflow attivi</span><strong>{data.overview?.counts?.active_workflows || 0}</strong></article>
        </div>
      )}

      {section === 'modules' && (
        <ConfigPanel title="Moduli">
          <SimpleTable columns={['Modulo', 'Chiave', 'Attivo']} rows={data.modules.map((module) => [
            module.module_name,
            module.module_key,
            <label className="switch-label" key={module.id}>
              <input type="checkbox" checked={Boolean(module.enabled)} onChange={(event) => updateConfig(`/config/modules/${module.id}`, { enabled: event.target.checked })} />
              <span>{module.enabled ? 'Attivo' : 'Disattivo'}</span>
            </label>,
          ])} />
        </ConfigPanel>
      )}

      {section === 'numbering' && (
        <ConfigPanel title="Numerazioni">
          <InlineForm onSubmit={() => saveConfig('/config/numbering-rules', normalizeConfigNumbers(forms.numbering))}>
            <TextInput label="Modulo" value={forms.numbering.module_key} onChange={(value) => updateForm('numbering', 'module_key', value)} />
            <TextInput label="Formato" value={forms.numbering.format_pattern} onChange={(value) => updateForm('numbering', 'format_pattern', value)} />
            <TextInput label="Anno" type="number" value={forms.numbering.current_year} onChange={(value) => updateForm('numbering', 'current_year', value)} />
            <TextInput label="Sequenza" type="number" value={forms.numbering.current_sequence} onChange={(value) => updateForm('numbering', 'current_sequence', value)} />
            <TextInput label="Reset" value={forms.numbering.reset_policy} onChange={(value) => updateForm('numbering', 'reset_policy', value)} />
          </InlineForm>
          <p className="muted">Anteprima: {previewNumber(forms.numbering)}</p>
          <SimpleTable columns={['Modulo', 'Formato', 'Anno', 'Sequenza', 'Reset', 'Azioni']} rows={data.numbering.map((rule) => [
            rule.module_key,
            rule.format_pattern,
            rule.current_year,
            rule.current_sequence,
            rule.reset_policy,
            <button key={rule.id} onClick={() => updateConfig(`/config/numbering-rules/${rule.id}`, normalizeConfigNumbers(rule))}>Salva</button>,
          ])} />
        </ConfigPanel>
      )}

      {section === 'matters' && (
        <ConfigPanel title="Materie">
          <InlineForm onSubmit={() => saveConfig('/config/matters', forms.matter)}>
            <TextInput label="Materia" value={forms.matter.name} onChange={(value) => updateForm('matter', 'name', value)} />
            <TextInput label="Codice ministeriale" value={forms.matter.ministerial_code} onChange={(value) => updateForm('matter', 'ministerial_code', value)} />
          </InlineForm>
          <SimpleTable columns={['Materia', 'Codice', 'Stato', 'Azioni']} rows={data.matters.map((matter) => [
            matter.name,
            matter.ministerial_code || '',
            matter.active ? 'Attiva' : 'Disattiva',
            <div className="row-actions" key={matter.id}>
              <button onClick={() => updateConfig(`/config/matters/${matter.id}`, { active: !matter.active })}>{matter.active ? 'Disattiva' : 'Attiva'}</button>
              <button className="danger" onClick={() => deleteConfig(`/config/matters/${matter.id}`)}>Elimina</button>
            </div>,
          ])} />
        </ConfigPanel>
      )}

      {section === 'workflows' && (
        <ConfigPanel title="Workflow">
          <InlineForm onSubmit={() => saveConfig('/config/workflows', forms.workflow)}>
            <TextInput label="Modulo" value={forms.workflow.module_key} onChange={(value) => updateForm('workflow', 'module_key', value)} />
            <TextInput label="Nome workflow" value={forms.workflow.name} onChange={(value) => updateForm('workflow', 'name', value)} />
          </InlineForm>
          {data.workflows.map((workflow) => (
            <div className="config-subpanel" key={workflow.id}>
              <h3>{workflow.name}</h3>
              <InlineForm onSubmit={() => saveConfig(`/config/workflows/${workflow.id}/steps`, normalizeStepForm({ ...forms.step, workflow_id: workflow.id }))}>
                <TextInput label="Ordine" type="number" value={forms.step.step_order} onChange={(value) => updateForm('step', 'step_order', value)} />
                <TextInput label="Chiave" value={forms.step.step_key} onChange={(value) => updateForm('step', 'step_key', value)} />
                <TextInput label="Nome step" value={forms.step.step_name} onChange={(value) => updateForm('step', 'step_name', value)} />
              </InlineForm>
              <SimpleTable columns={['Ordine', 'Chiave', 'Step', 'Obbligatorio', 'Azioni']} rows={(workflowSteps[workflow.id] || []).map((step) => [
                step.step_order,
                step.step_key,
                step.step_name,
                step.required ? 'Si' : 'No',
                <button className="danger" key={step.id} onClick={() => deleteConfig(`/config/workflow-steps/${step.id}`)}>Elimina</button>,
              ])} />
            </div>
          ))}
        </ConfigPanel>
      )}

      {section === 'roles' && (
        <ConfigPanel title="Ruoli e permessi">
          <InlineForm onSubmit={() => saveConfig('/config/roles', forms.role)}>
            <TextInput label="Chiave ruolo" value={forms.role.role_key} onChange={(value) => updateForm('role', 'role_key', value)} />
            <TextInput label="Nome ruolo" value={forms.role.role_name} onChange={(value) => updateForm('role', 'role_name', value)} />
            <TextInput label="Descrizione" value={forms.role.description} onChange={(value) => updateForm('role', 'description', value)} />
          </InlineForm>
          {data.roles.map((role) => {
            const selectedIds = new Set((rolePermissions[role.id] || []).map((permission) => permission.id));
            return (
              <div className="config-subpanel" key={role.id}>
                <h3>{role.role_name}</h3>
                <div className="permission-grid">
                  {data.permissions.map((permission) => (
                    <label className="checkbox-label" key={permission.id}>
                      <input
                        type="checkbox"
                        defaultChecked={selectedIds.has(permission.id)}
                        onChange={(event) => {
                          const next = new Set(selectedIds);
                          if (event.target.checked) next.add(permission.id);
                          else next.delete(permission.id);
                          saveConfig(`/config/roles/${role.id}/permissions`, { permission_ids: [...next] }, 'Permessi aggiornati.');
                        }}
                      />
                      <span>{permission.permission_name}</span>
                    </label>
                  ))}
                </div>
              </div>
            );
          })}
        </ConfigPanel>
      )}

      {section === 'economics' && (
        <ConfigPanel title="Motore Economico">
          <div className="economic-summary">
            {['Casarano', 'Pachino', 'Napoli'].map((officeName) => {
              const rules = data.economicRules.filter((rule) => rule.office_name === officeName && rule.active);
              return (
                <article className="config-subpanel" key={officeName}>
                  <h3>{officeName}</h3>
                  {rules.length ? rules.map((rule) => (
                    <p key={rule.id}>
                      <strong>{rule.rule_name}:</strong> {rule.percentage}% del netto maturato
                    </p>
                  )) : <p className="muted">Nessuna regola attiva.</p>}
                </article>
              );
            })}
          </div>
          <InlineForm onSubmit={() => saveConfig('/config/economic-parameters', forms.economic)}>
            <TextInput label="Parametro" value={forms.economic.parameter_key} onChange={(value) => updateForm('economic', 'parameter_key', value)} />
            <TextInput label="Valore" value={forms.economic.parameter_value} onChange={(value) => updateForm('economic', 'parameter_value', value)} />
            <TextInput label="Valido dal" type="date" value={forms.economic.valid_from} onChange={(value) => updateForm('economic', 'valid_from', value)} />
          </InlineForm>
          <SimpleTable columns={['Parametro', 'Valore', 'Dal', 'Al', 'Stato']} rows={data.economics.map((parameter) => [
            parameter.parameter_key,
            parameter.parameter_value,
            parameter.valid_from || '',
            parameter.valid_to || '',
            parameter.active ? 'Attivo' : 'Non attivo',
          ])} />
          <div className="config-subpanel">
            <h3>Regole economiche compensi e sedi</h3>
            <InlineForm onSubmit={() => saveEconomicRule(forms.economicRule, saveConfig)}>
              <label>
                <span>Sede applicabile</span>
                <select value={forms.economicRule.office_id || ''} onChange={(event) => updateForm('economicRule', 'office_id', event.target.value)}>
                  <option value="">Tutte le sedi</option>
                  {data.offices.map((office) => <option key={office.id} value={office.id}>{office.name}</option>)}
                </select>
              </label>
              <TextInput label="Chiave regola" value={forms.economicRule.rule_key} onChange={(value) => updateForm('economicRule', 'rule_key', value)} />
              <TextInput label="Nome regola" value={forms.economicRule.rule_name} onChange={(value) => updateForm('economicRule', 'rule_name', value)} />
              <TextInput label="Tipo" value={forms.economicRule.rule_type} onChange={(value) => updateForm('economicRule', 'rule_type', value)} />
              <TextInput label="Percentuale" type="number" value={forms.economicRule.percentage} onChange={(value) => updateForm('economicRule', 'percentage', value)} />
              <TextInput label="Base calcolo" value={forms.economicRule.calculation_base || 'netto_maturato'} onChange={(value) => updateForm('economicRule', 'calculation_base', value)} />
              <TextInput label="Esito applicabile" value={forms.economicRule.mediation_outcome} onChange={(value) => updateForm('economicRule', 'mediation_outcome', value)} />
              <TextInput label="Valida dal" type="date" value={forms.economicRule.valid_from} onChange={(value) => updateForm('economicRule', 'valid_from', value)} />
              <TextInput label="Valida al" type="date" value={forms.economicRule.valid_to} onChange={(value) => updateForm('economicRule', 'valid_to', value)} />
            </InlineForm>
          </div>
          <SimpleTable columns={['Sede', 'Regola', 'Tipo', 'Percentuale', 'Base', 'Include', 'Validita', 'Stato']} rows={data.economicRules.map((rule) => [
            rule.office_name || 'Tutte',
            rule.rule_name,
            rule.rule_type,
            `${rule.percentage}%`,
            rule.calculation_base || rule.applies_to || '',
            [
              rule.includes_startup_expenses ? 'avvio' : '',
              rule.includes_first_meeting_expenses ? 'primo incontro' : '',
              rule.includes_further_expenses ? 'ulteriori' : '',
            ].filter(Boolean).join(', '),
            `${rule.valid_from || '-'} / ${rule.valid_to || '-'}`,
            rule.active ? 'Attiva' : 'Non attiva',
          ])} />
        </ConfigPanel>
      )}

      {section === 'logs' && (
        <ConfigPanel title="Log ultime modifiche">
          <SimpleTable columns={['Data', 'Entita', 'ID', 'Azione']} rows={data.logs.map((log) => [
            log.created_at,
            log.entity_name,
            log.entity_id || '',
            log.action,
          ])} />
        </ConfigPanel>
      )}
    </section>
  );
}

function ConfigPanel({ title, children }) {
  return (
    <section className="table-panel config-panel">
      <div className="panel-heading">
        <h2>{title}</h2>
      </div>
      <div className="config-body">{children}</div>
    </section>
  );
}

function SimpleTable({ columns, rows }) {
  return (
    <div className="table-scroll">
      <table>
        <thead>
          <tr>{columns.map((column) => <th key={column}>{column}</th>)}</tr>
        </thead>
        <tbody>
          {rows.map((row, index) => (
            <tr key={index}>{row.map((cell, cellIndex) => <td key={cellIndex}>{cell}</td>)}</tr>
          ))}
          {rows.length === 0 && (
            <tr><td className="empty-state" colSpan={columns.length}>Nessun dato configurato.</td></tr>
          )}
        </tbody>
      </table>
    </div>
  );
}

const documentSignatureStatuses = [
  'Da firmare',
  'Inviato per firma',
  'Firmato parzialmente',
  'Firmato completo',
  'Da conservare',
  'Conservato CAD',
  'Errore conservazione',
];

function DocumentSignatureStatus({ status }) {
  const value = status || 'Da firmare';
  const normalized = documentSignatureStatuses.includes(value) ? value : 'Da firmare';
  return <span className="status-pill document-status-pill">{normalized}</span>;
}

function ChecklistStatus({ status }) {
  const normalized = status || 'da verificare';
  return <span className={`status-pill checklist-status ${normalized.replaceAll(' ', '-')}`}>{normalized}</span>;
}

function InlineForm({ children, onSubmit }) {
  return (
    <form className="inline-form" onSubmit={(event) => { event.preventDefault(); onSubmit(); }}>
      {children}
      <button className="primary-button" type="submit">
        <Save size={16} />
        Salva
      </button>
    </form>
  );
}

function TextInput({ label, value, onChange, type = 'text' }) {
  return (
    <label>
      <span>{label}</span>
      <input type={type} value={value || ''} onChange={(event) => onChange(event.target.value)} />
    </label>
  );
}

function normalizeConfigNumbers(rule) {
  return {
    ...rule,
    current_year: Number(rule.current_year || new Date().getFullYear()),
    current_sequence: Number(rule.current_sequence || 0),
    active: rule.active ?? true,
  };
}

function normalizeStepForm(step) {
  return {
    ...step,
    workflow_id: Number(step.workflow_id),
    step_order: Number(step.step_order || 1),
    required: step.required ?? true,
  };
}

function saveEconomicRule(rule, saveConfig) {
  const payload = {
    ...rule,
    office_id: rule.office_id ? Number(rule.office_id) : null,
    percentage: Number(rule.percentage || 0),
    calculation_base: rule.calculation_base || 'netto_maturato',
    applies_to: rule.applies_to || 'netto_maturato',
    includes_startup_expenses: rule.includes_startup_expenses ?? true,
    includes_first_meeting_expenses: rule.includes_first_meeting_expenses ?? true,
    includes_further_expenses: rule.includes_further_expenses ?? true,
    applies_to_main_office: rule.applies_to_main_office ?? false,
    applies_to_operational_office: rule.applies_to_operational_office ?? false,
    valid_to: rule.valid_to || null,
    mediation_outcome: rule.mediation_outcome || null,
    active: rule.active ?? true,
  };
  return saveConfig('/economic-rules', payload, 'Regola economica salvata.');
}

function previewNumber(rule) {
  const sequence = String(Number(rule.current_sequence || 0) + 1).padStart(3, '0');
  return (rule.format_pattern || '{sequence}/2026').replace('{sequence}', sequence).replace('{year}', String(rule.current_year || new Date().getFullYear()));
}

function ContactDetail({ contact, onEdit }) {
  if (!contact) {
    return (
      <aside className="table-panel contact-detail">
        <div className="empty-state">Seleziona un contatto per vedere la scheda.</div>
      </aside>
    );
  }

  return (
    <aside className="table-panel contact-detail">
      <div className="panel-heading">
        <h2>Scheda contatto</h2>
        <button className="icon-button" onClick={() => onEdit(contact)} title="Modifica">
          <Pencil size={18} />
        </button>
      </div>
      <div className="detail-body">
        <h3>{displayName(contact)}</h3>
        <div className="role-list">
          {contactRoles(contact).map((role) => <span className="status-pill" key={role}>{role}</span>)}
          {contactRoles(contact).length === 0 && <span className="muted">Nessun ruolo assegnato</span>}
        </div>
        <dl>
          <dt>Email</dt>
          <dd>{contact.email || '-'}</dd>
          <dt>PEC</dt>
          <dd>{contact.pec || '-'}</dd>
          <dt>Telefono</dt>
          <dd>{contact.phone || '-'}</dd>
          <dt>Cellulare</dt>
          <dd>{contact.mobile || '-'}</dd>
          <dt>Codice fiscale</dt>
          <dd>{contact.fiscal_code || '-'}</dd>
          <dt>Partita IVA</dt>
          <dd>{contact.vat_number || '-'}</dd>
          <dt>Indirizzo</dt>
          <dd>{formatAddress(contact)}</dd>
          <dt>Note</dt>
          <dd>{contact.notes || '-'}</dd>
        </dl>
      </div>
    </aside>
  );
}

function ContactModal({ contact, setContact, onSubmit, onClose }) {
  function updateField(field, value) {
    const next = { ...contact, [field]: value };
    if (field === 'contact_type' && value === 'azienda') next.is_company = true;
    if (field === 'is_company' && value) next.contact_type = 'azienda';
    setContact(next);
  }

  return (
    <div className="modal-backdrop">
      <form className="modal" onSubmit={onSubmit}>
        <header>
          <h2>{contact.id ? 'Modifica contatto' : 'Nuovo contatto'}</h2>
          <button type="button" className="icon-button" onClick={onClose} title="Chiudi">
            <X size={18} />
          </button>
        </header>

        <div className="form-grid">
          <label>
            <span>Tipo contatto</span>
            <select value={contact.contact_type || 'persona'} onChange={(event) => updateField('contact_type', event.target.value)}>
              <option value="persona">Persona</option>
              <option value="azienda">Azienda</option>
            </select>
          </label>
          <label>
            <span>Societa</span>
            <input value={contact.company_name || ''} onChange={(event) => updateField('company_name', event.target.value)} />
          </label>
          <label>
            <span>Nome</span>
            <input value={contact.first_name || ''} onChange={(event) => updateField('first_name', event.target.value)} />
          </label>
          <label>
            <span>Cognome</span>
            <input value={contact.last_name || ''} onChange={(event) => updateField('last_name', event.target.value)} />
          </label>
          <label>
            <span>Codice fiscale</span>
            <input value={contact.fiscal_code || ''} onChange={(event) => updateField('fiscal_code', event.target.value)} />
          </label>
          <label>
            <span>Partita IVA</span>
            <input value={contact.vat_number || ''} onChange={(event) => updateField('vat_number', event.target.value)} />
          </label>
          <label>
            <span>Email</span>
            <input type="email" value={contact.email || ''} onChange={(event) => updateField('email', event.target.value)} />
          </label>
          <label>
            <span>PEC</span>
            <input value={contact.pec || ''} onChange={(event) => updateField('pec', event.target.value)} />
          </label>
          <label>
            <span>Telefono</span>
            <input value={contact.phone || ''} onChange={(event) => updateField('phone', event.target.value)} />
          </label>
          <label>
            <span>Cellulare</span>
            <input value={contact.mobile || ''} onChange={(event) => updateField('mobile', event.target.value)} />
          </label>
          <label className="wide">
            <span>Indirizzo</span>
            <input value={contact.address || ''} onChange={(event) => updateField('address', event.target.value)} />
          </label>
          <label>
            <span>Citta</span>
            <input value={contact.city || ''} onChange={(event) => updateField('city', event.target.value)} />
          </label>
          <label>
            <span>Provincia</span>
            <input value={contact.province || ''} onChange={(event) => updateField('province', event.target.value)} />
          </label>
          <label>
            <span>CAP</span>
            <input value={contact.zip_code || ''} onChange={(event) => updateField('zip_code', event.target.value)} />
          </label>
          <fieldset className="wide role-fieldset">
            <legend>Ruoli</legend>
            <div className="role-checkboxes">
              {roleFields.map(([field, label]) => (
                <label className="checkbox-label" key={field}>
                  <input
                    type="checkbox"
                    checked={Boolean(contact[field])}
                    onChange={(event) => updateField(field, event.target.checked)}
                  />
                  <span>{label}</span>
                </label>
              ))}
            </div>
          </fieldset>
          <label className="wide">
            <span>Note</span>
            <textarea value={contact.notes || ''} onChange={(event) => updateField('notes', event.target.value)} rows="3" />
          </label>
        </div>

        <footer>
          <button type="button" onClick={onClose}>Annulla</button>
          <button type="submit" className="primary-button">
            <Save size={18} />
            Salva
          </button>
        </footer>
      </form>
    </div>
  );
}

function EmptyModule({ module }) {
  return (
    <section className="table-panel empty-module">
      <div className="panel-heading">
        <h2>{module.label}</h2>
      </div>
      <div className="empty-state">
        Pagina modulo pronta per lo sviluppo delle funzioni operative.
      </div>
    </section>
  );
}

function displayName(contact) {
  if (contact.company_name) return contact.company_name;
  return [contact.first_name, contact.last_name].filter(Boolean).join(' ') || `Contatto #${contact.id}`;
}

function contactRoles(contact) {
  return roleFields.filter(([field]) => contact[field]).map(([, label]) => label);
}

function formatAddress(contact) {
  const cityLine = [contact.zip_code, contact.city, contact.province].filter(Boolean).join(' ');
  return [contact.address, cityLine].filter(Boolean).join(', ') || '-';
}

function toFormContact(contact) {
  return { ...emptyContact, ...contact };
}

function toMediationForm(mediation) {
  return { ...emptyMediation, ...mediation };
}

function normalizeMediationForm(mediation) {
  const numberFields = ['organization_id', 'office_id', 'year', 'quarter', 'claim_value', 'mediator_id', 'tariff_id'];
  const payload = { ...mediation };
  numberFields.forEach((field) => {
    if (payload[field] === '' || payload[field] === null || payload[field] === undefined) {
      payload[field] = null;
    } else {
      payload[field] = Number(payload[field]);
    }
  });
  return payload;
}

function normalizeCaseForm(caseRecord) {
  const payload = { ...caseRecord };
  ['organization_id', 'office_id', 'related_entity_id'].forEach((field) => {
    payload[field] = payload[field] ? Number(payload[field]) : null;
  });
  return payload;
}

function formatCurrency(value) {
  const number = Number(value || 0);
  return new Intl.NumberFormat('it-IT', {
    style: 'currency',
    currency: 'EUR',
  }).format(number);
}

createRoot(document.getElementById('root')).render(<App />);
