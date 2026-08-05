# Public Mediation module

## Scope

The module improves the public mediation section of the WordPress site. It does not implement case management, CRM, workflow, or document storage.

## Supported pages

| Area | Default slug | Template |
| --- | --- | --- |
| How mediation works | `come-funziona-la-mediazione` | Process |
| Mediation costs | `costi-della-mediazione` | Standard |
| Civil and commercial mediation | `mediazione-civile-e-commerciale` | Standard |
| Court-referred mediation | `mediazione-demandata-dal-giudice` | Standard |
| Online mediation | `mediazione-telematica` | Standard |
| Mediation application | `istanza-di-mediazione` | Standard |
| Participation in mediation | `adesione-alla-mediazione` | Standard |
| FAQ | `faq-mediazione` | Standard |
| Legislation | `normativa` | Editorial archive |
| Case law | `sentenze-e-giurisprudenza` | Editorial archive |

Administrators may associate any existing WordPress page instead of using automatic slug detection. The stored page ID preserves its existing permalink.

## Safety model

- Replacement templates are off by default.
- No page, post, taxonomy term, or redirect is created automatically.
- Disabling one template restores that page's original theme template.
- Removing `mediation` from the Core `enabled_modules` setting prevents every module hook from registering.
- All changes use the Core settings manager and authenticated `admin-post` router.
- Saves require `manage_options` and a WordPress nonce.

## Editorial integration

The legislation archive reads existing published posts assigned to the `normativa` category. The case-law archive reads the `giurisprudenza` category. Search, year filtering, and pagination are implemented through `WP_Query`; no direct SQL is used.

## Frontend integration

Module CSS is scoped under `.me-mediation`. JavaScript is dependency-free ES6 and is used only for the accessible FAQ accordion. The module reuses the Core design tokens with safe fallback values and honors reduced-motion preferences.

The future estimate engine can supply its URL with the `mediacon_enterprise_estimate_url` filter. The module links to the configured destination but does not implement the engine.

## Verification

```shell
composer install
composer lint
composer phpcs
composer test
composer smoke
node --check src/Modules/Mediation/Assets/js/mediation.js
```
