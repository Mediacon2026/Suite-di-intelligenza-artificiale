# Mediacon Enterprise 1.0 — Migration Map

Baseline pre-migrazione generata il 5 agosto 2026 dal contenuto del worktree `feature/mediacon-enterprise` prima di qualunque modifica Enterprise 1.0.

## Perimetro e metodo

La scansione ha incluso tutti i file PHP, i manifest Composer, gli archivi distribuiti e i template presenti nel repository. Il repository non contiene un'installazione WordPress completa né un dump delle tabelle `wp_posts`, `wp_options`, `wp_terms` e `wp_term_taxonomy`: ID, stato editoriale, template salvato nei metadati, menu di navigazione, shortcode/widget registrati da tema o terze parti e plugin installati sul server non sono quindi determinabili offline. Tali dati sono indicati come **runtime** e devono essere acquisiti senza modifiche dal Site Inventory di Enterprise sul sito reale.

La mappa distingue:

- **statico**: rilevato direttamente dal codice versionato;
- **runtime**: risolto interrogando le API WordPress e il database del sito;
- **non rilevato**: nessuna registrazione presente nel sorgente disponibile.

## Plugin

| Plugin | File | Versione baseline | Ruolo | Stato |
|---|---|---:|---|---|
| Mediacon Enterprise | `mediacon-enterprise.php` | 0.7.0 | orchestratore modulare | runtime |
| Mediacon Design Core Compatibility Bridge | `compatibility/mediacon-design-core/mediacon-design-core.php` | 0.1.0 | bridge legacy, senza contenuti | runtime |

Il pacchetto statico non contiene altri plugin legacy. Enterprise deve enumerare a runtime tutti i plugin installati, identificare quelli Mediacon e associare le pagine mediante metadati, callback `template_include`, shortcode e riferimenti nei file plugin.

## Pagine gestite

Gli ID, lo stato e il template WordPress sono campi runtime. Nessuna pagina viene creata o rinominata dalla migrazione.

### Mediazione

| Chiave | Titolo canonico | Slug invariato | Template Enterprise |
|---|---|---|---|
| how-it-works | Come funziona la mediazione | `come-funziona-la-mediazione` | `how-it-works.php` |
| costs | Costi della mediazione | `costi-della-mediazione` | `standard-page.php` |
| civil-commercial | Mediazione civile e commerciale | `mediazione-civile-e-commerciale` | `standard-page.php` |
| court-referred | Mediazione demandata dal giudice | `mediazione-demandata-dal-giudice` | `standard-page.php` |
| online | Mediazione telematica | `mediazione-telematica` | `standard-page.php` |
| application | Istanza di mediazione | `istanza-di-mediazione` | `standard-page.php` |
| participation | Adesione alla mediazione | `adesione-alla-mediazione` | `standard-page.php` |
| faq | FAQ | `faq-mediazione` | `standard-page.php` |
| legislation | Normativa | `normativa` | `editorial-archive.php` |
| case-law | Sentenze e giurisprudenza | `sentenze-e-giurisprudenza` | `editorial-archive.php` |

### Formazione

| Chiave | Titolo canonico | Slug invariato | Template Enterprise |
|---|---|---|---|
| formation | Formazione mediatori | `formazione-mediatori` | `landing.php` |
| base-course | Corso base mediatori | `corso-base-mediatori` | `course-detail.php` |
| advanced | Corso di approfondimento | `corso-approfondimento` | `course-detail.php` |
| renewal | Corso di aggiornamento biennale | `corso-aggiornamento-biennale` | `course-detail.php` |
| calendar | Calendario corsi | `calendario-corsi` | `calendar.php` |
| teachers | Docenti e formatori | `docenti-e-formatori` | `teachers.php` |
| faq | FAQ formazione | `faq-formazione` | `standard-page.php` |
| registration | Iscrizioni | `iscrizioni-formazione` | `registration.php` |
| upcoming | Prossimi corsi | `prossimi-corsi` | `course-archive.php` |
| insights | Approfondimenti formativi | `approfondimenti-formativi` | `insights.php` |

### Editoriale

| Chiave | Risorsa | Slug | Tipo | Template Enterprise |
|---|---|---|---|---|
| blog | pagina articoli | runtime | posts archive | `archive.php` |
| jurisprudence | Sentenze e Giurisprudenza | `giurisprudenza` | categoria | `archive.php` |
| legislation | Normativa | `normativa` | categoria | `archive.php` |
| insights | Approfondimenti | `approfondimenti` | categoria | `archive.php` |
| categories | tutte le categorie | runtime | category archive | `archive.php` |
| courses | Prossimi corsi | `prossimi-corsi` | pagina | `course-archive.php` |
| search | ricerca editoriale | `?s=` | search archive | `archive.php` |
| single | singolo articolo | permalink esistente | single post | `single.php`, opt-in globale |

### Preventivo e Ricerca

| Modulo | Risorsa | Identificazione | Template Enterprise | Default |
|---|---|---|---|---|
| Preventivo | pagina configurata | option `preventivo.page_id` | `wizard.php` | frontend disattivato |
| Ricerca | pagina configurata | option `search.page_id` | `results.php` | frontend disattivato |

## Template PHP

### Core e compatibilità

- `templates/admin-dashboard.php`
- `templates/compatibility-admin.php`
- `templates/compatibility/page-hero.php`
- `templates/compatibility/footer.php`

### Mediazione

- `src/Modules/Mediation/Templates/admin-page.php`
- `src/Modules/Mediation/Templates/page-shell.php`
- `src/Modules/Mediation/Templates/how-it-works.php`
- `src/Modules/Mediation/Templates/standard-page.php`
- `src/Modules/Mediation/Templates/editorial-archive.php`
- `src/Modules/Mediation/Templates/components/breadcrumb.php`
- `src/Modules/Mediation/Templates/components/cards.php`
- `src/Modules/Mediation/Templates/components/cta.php`
- `src/Modules/Mediation/Templates/components/faq.php`
- `src/Modules/Mediation/Templates/components/hero.php`

### Formazione

- `src/Modules/Formation/Templates/admin-page.php`
- `src/Modules/Formation/Templates/page-shell.php`
- `src/Modules/Formation/Templates/landing.php`
- `src/Modules/Formation/Templates/course-detail.php`
- `src/Modules/Formation/Templates/course-archive.php`
- `src/Modules/Formation/Templates/calendar.php`
- `src/Modules/Formation/Templates/teachers.php`
- `src/Modules/Formation/Templates/teacher-detail.php`
- `src/Modules/Formation/Templates/registration.php`
- `src/Modules/Formation/Templates/insights.php`
- `src/Modules/Formation/Templates/standard-page.php`
- `src/Modules/Formation/Templates/components/badge.php`
- `src/Modules/Formation/Templates/components/breadcrumb.php`
- `src/Modules/Formation/Templates/components/course-card.php`
- `src/Modules/Formation/Templates/components/course-grid.php`
- `src/Modules/Formation/Templates/components/cta.php`
- `src/Modules/Formation/Templates/components/faq.php`
- `src/Modules/Formation/Templates/components/hero.php`

### Editoriale

- `src/Modules/Editorial/Templates/admin-page.php`
- `src/Modules/Editorial/Templates/archive.php`
- `src/Modules/Editorial/Templates/course-archive.php`
- `src/Modules/Editorial/Templates/single.php`
- `src/Modules/Editorial/Templates/page-shell.php`
- `src/Modules/Editorial/Templates/components/breadcrumb.php`
- `src/Modules/Editorial/Templates/components/card.php`
- `src/Modules/Editorial/Templates/components/course-card.php`
- `src/Modules/Editorial/Templates/components/cta.php`
- `src/Modules/Editorial/Templates/components/filters.php`
- `src/Modules/Editorial/Templates/components/hero.php`

### Preventivo

- `src/Modules/Preventivo/Templates/admin-page.php`
- `src/Modules/Preventivo/Templates/page-shell.php`
- `src/Modules/Preventivo/Templates/wizard.php`

### Ricerca

- `src/Modules/Search/Templates/admin-page.php`
- `src/Modules/Search/Templates/page-shell.php`
- `src/Modules/Search/Templates/results.php`
- `src/Modules/Search/Templates/search-form.php`

## Shortcode

| Tag | Provider | Stato |
|---|---|---|
| `[mediacon_enterprise]` | Core `Frontend\Shortcode` | statico |
| `[mediacon_search]` | modulo Search | statico, solo modulo attivo |

Gli shortcode registrati da tema, mu-plugin e plugin installati sono runtime.

## Widget, custom post type e tassonomie

- Widget registrati dal pacchetto: **nessuno**.
- Custom post type registrati dal pacchetto: **nessuno**.
- Tassonomie registrate dal pacchetto: **nessuna**.
- Il codice consuma i tipi WordPress `page` e `post` e la tassonomia core `category` senza modificarne la registrazione.
- Widget, CPT e tassonomie di tema/plugin esterni: runtime.

## REST API e route amministrative

Namespace REST: `mediacon-enterprise/v1`.

| Metodo | Route | Modulo | Protezione |
|---|---|---|---|
| POST | `/preventivo/calculate` | Preventivo | permission callback e rate limit |
| POST | `/search/suggest` | Ricerca | permission callback, limiti input e rate limit |

Route `admin-post.php` statiche:

- `mediacon_enterprise_save_mediation`
- `mediacon_enterprise_save_formation`
- `mediacon_enterprise_save_editorial`
- `mediacon_enterprise_save_preventivo`
- `mediacon_enterprise_save_search`
- `mediacon_enterprise_download_bridge`

## Opzioni WordPress

Opzioni scritte direttamente:

- `mediacon_enterprise_version`
- `mediacon_enterprise_settings`
- `mediacon_enterprise_cache_{group}` (versionamento cache; `search` è il gruppo staticamente rilevato)

Opzioni core lette:

- `active_plugins`
- `page_for_posts`
- `date_format`

La struttura `mediacon_enterprise_settings` contiene `enabled_modules`, `compatibility_enabled`, `delete_on_uninstall`, `mediation`, `formation`, `editorial`, `preventivo` e `search`. Nessuna opzione viene rimossa in disattivazione; l'uninstall elimina dati soltanto con `MEDIACON_ENTERPRISE_REMOVE_DATA === true`.

L'elenco di tutte le option name presenti sul sito è runtime e deve essere acquisito senza leggere o esporre i valori.

## Menu amministrativi

Menu statici baseline:

- `Mediacon Enterprise` (top level e Dashboard implicita)
- `Mediazione`
- `Formazione`
- `Editoriale`
- `Preventivo`
- `Ricerca`
- `Compatibilità legacy`

Menu pubblici registrati dal pacchetto: **nessuno**. Le location del tema e le istanze menu sono runtime.

## Hook

### Hook WordPress consumati

- `plugins_loaded`, `init`, `admin_init`, `admin_menu`, `admin_enqueue_scripts`, `wp_enqueue_scripts`, `rest_api_init`, `template_include`, `admin_notices`
- `save_post`, `deleted_post`, `edited_term`
- `admin_post_{azione}` per le route elencate sopra
- `the_content`

### Hook Enterprise/legacy pubblicati o filtrati

- `mediacon_enterprise_booted`
- `mediacon_enterprise_module_booted`
- `mediacon_enterprise_capabilities`
- `mediacon_enterprise_compatibility_enabled`
- `mediacon_enterprise_compatibility_ready`
- `mediacon_enterprise_compatibility_bridge_loaded`
- `mediacon_enterprise_estimate_url`
- `mediacon_design_core_loaded`
- `mediacon_design_core_init`
- `mediacon_design_core_path`
- `mediacon_design_core_url`
- `mediacon_design_core_pages`
- `mediacon_design_core_logo_url`

La lista completa dei callback agganciati sul sito e la loro provenienza plugin è runtime.

## Contratti legacy Design Core

Costanti compatibili: `MEDIACON_DESIGN_CORE_VERSION`, `MEDIACON_DESIGN_CORE_FILE`, `MEDIACON_DESIGN_CORE_PATH`, `MEDIACON_DESIGN_CORE_URL`, `MDC_VERSION`, `MDC_FILE`, `MDC_PATH`, `MDC_URL`.

Funzioni compatibili: `mediacon_design_core`, `mediacon_design_core_path`, `mediacon_design_core_url`, `mediacon_design_core_register_style`, `mediacon_design_core_register_script`, `mediacon_design_core_enqueue_style`, `mediacon_design_core_enqueue_script`, `mediacon_design_core_register_page`, `mediacon_design_core_page_id`, `mediacon_design_core_template`, `mediacon_design_core_component`, `mediacon_design_core_setting`, `mediacon_design_core_logo_url`, `mdc_register_page`, `mdc_render_page_hero`, `mdc_render_footer`.

## Regole invarianti della migrazione

1. Nessuna creazione, modifica o cancellazione di contenuti.
2. Nessuna modifica a slug, permalink o URL.
3. Nessuna modifica ai template pubblici esistenti salvo selezione amministrativa esplicita e reversibile.
4. Default conservativo: gestione WordPress/legacy invariata finché un amministratore non seleziona Enterprise.
5. Ogni modulo e la compatibilità devono poter essere disattivati.
6. Ogni migrazione deve salvare uno snapshot e offrire rollback e verifica.
7. Fatal e warning devono essere registrati in forma limitata e non sensibile, senza sopprimere il comportamento WordPress.

## Dati da completare automaticamente sul sito

Il Site Inventory Enterprise 1.0 deve completare questa baseline con: tutte le pagine e relativi ID/status/template; plugin e versioni/attivazione; associazioni pagina-plugin; shortcode globali; widget registrati; CPT; tassonomie; hook e callback; route REST; nomi delle opzioni; menu amministrativi e di navigazione; file template di tema, child theme, mu-plugin e plugin. L'assenza del database nel repository non autorizza assunzioni o modifiche.
