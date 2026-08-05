# Mediacon Enterprise technical audit

Audit date: 2026-08-05
Audited version: 0.6.1
Scope: Core, providers, lifecycle, administration, public frontend, Mediazione, Formazione, Editoriale, Preventivo, Search, templates, assets, tests, Composer production package, and uninstall behavior.

## Architecture

The plugin uses a single PSR-4 namespace, a lazy service container, a centralized hook manager, a route collector, one settings option, and independently switchable modules. Module bootstrap order is deterministic. Search and Editoriale deliberately consume existing Formation repositories; Search also consumes Mediazione and Editoriale presentation services. Fallback registrations keep Search and Editoriale operational when an upstream presentation module is disabled, and no circular service resolution was found.

The Mediazione and Formazione page catalogs previously duplicated page lookup, configuration enrichment, and current-page resolution. That behavior now lives in the Core PageCatalog base class, while each module retains only its definitions and settings key.

## Corrected findings

- Centralized the built-in module list and aligned activation, settings defaults, and runtime selection. A fresh activation no longer enables only Mediazione and Formazione.
- Changed the Core status shortcode to expose enabled modules instead of every registered module.
- Hardened absolute template path validation with a directory-boundary-aware prefix.
- Rejected Preventivo requests containing no valid party or no claimant.
- Rejected non-finite dispute and monetary values before calculation.
- Moved rate limiting from Search into Core and applied the same anonymized, scope-separated limiter to Search and Preventivo public endpoints.
- Bounded the Docenti query to 100 records, disabled unnecessary row counting, and bounded Editoriale topic facets to 100 terms.
- Kept Search candidates within the configured global maximum even after virtual FAQ records are added.
- Prevented configured pages belonging to disabled modules from being reclassified as generic searchable pages.
- Changed highlighting to one regular-expression pass so later terms cannot alter mark elements inserted for earlier terms.
- Removed duplicated Mediazione/Formazione page catalog mechanics.
- Added Search cache-generation options to explicit uninstall cleanup.
- Added regression tests covering the corrected Core, performance, validation, rate-limit, template, and Search behaviors.

## Residual findings by priority

### Critical

None identified after remediation.

### High

None identified after remediation.

### Medium

- The test suite uses WordPress doubles rather than the official WordPress integration test environment. Hook timing, REST dispatch, database queries, theme template loading, multisite activation, and browser behavior therefore remain integration risks.
- SimulationNumber updates the shared settings option with a read-modify-write sequence. Concurrent Preventivo requests can receive duplicate informational simulation references. The references are explicitly non-case-management identifiers, but an atomic dedicated counter would be required if they become legally or operationally significant.
- The transient rate limiter uses a non-atomic read/write sequence. It is appropriate as lightweight abuse control, not as a strict quota under concurrent traffic or a distributed cache race.
- Mediazione, Formazione, and Search administration screens call get_pages to populate selectors. Very large page inventories can make those administration requests expensive; replacing them safely requires an asynchronous selector and is outside a no-new-features audit.
- Search and Editoriale depend on concrete Formation and Editoriale classes. There are no cycles, but extracting read-only content contracts into a neutral integration layer would reduce cross-module coupling in a future architectural release.
- No executable code-coverage driver or minimum threshold is configured. Functional domains have useful regression coverage, but a reliable statement-level percentage cannot be reported from the present toolchain.

### Low

- Several module templates and stylesheets are intentionally compact and difficult to review line-by-line. Formatting them without behavior changes would improve maintainability.
- Some module services repeat local fallback arrays already represented in SettingsManager. Central typed configuration value objects would reduce drift but would be a broad internal migration.
- Automated browser accessibility checks are absent. Keyboard and ARIA behavior is implemented, but should eventually be verified against real assistive-technology/browser combinations.
- Public and administrative copy mixes Italian and English source strings. All user-facing strings are translation-ready, but source-language consistency would simplify localization review.

## Security review

Administrative writes require manage_options and WordPress nonces. Public REST operations validate REST nonces, sanitize and bound inputs, and now share scoped rate limiting. Queries use WordPress APIs, explicitly request published content where relevant, and contain no handcrafted SQL. Templates escape output or use tightly scoped safe markup. No dynamic evaluation, shell execution, remote service call, unsafe deserialization, or bundled production dependency advisory was found.

## Performance review

Public queries are paginated or explicitly bounded after remediation. Search uses capped WordPress queries, transient result caching, namespace invalidation, disabled metadata caching where possible, and a global candidate ceiling. Assets are module-scoped except Search assets, which intentionally remain global while public Search is enabled so the reusable header component works outside the result page.

## Test assessment

The suite covers module enable/disable behavior, template fallbacks, editorial repositories/cards, Preventivo domain calculations, Search types/ranking/synonyms/filtering/pagination/cache/query limits, and audit regressions. The principal coverage gap is integration with an actual WordPress database, REST server, active theme, and browser; this is recorded above rather than hidden behind an inferred percentage.
