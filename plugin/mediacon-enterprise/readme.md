# Mediacon Enterprise

Current version: 0.7.0

Version 0.7.0 adds a guarded Mediacon Design Core compatibility module and a separately installable dependency bridge. See `docs/LEGACY-COMPATIBILITY.md` for the exact supported surface and its limits.

Mediacon Enterprise is a modular WordPress foundation for mediation and professional-formation workflows.

## Requirements

- WordPress 6.8 or newer
- PHP 8.2 or newer

## Installation

1. Upload `mediacon-enterprise.zip` from **Plugins → Add New → Upload Plugin**.
2. Activate **Mediacon Enterprise**.
3. Open **Mediacon Enterprise** in the WordPress administration menu.

The distributable archive includes Composer's optimized production autoloader.

## Architecture

The bootstrap creates a service container and registers the hook, asset, router, settings, and module managers. Feature modules implement a small common contract and are booted by the module manager.

## Public Mediation module

The Mediation module integrates optional public templates with existing WordPress pages. It never creates pages, changes page content, changes permalinks, or adds permanent redirects.

Administrators can open **Mediacon Enterprise → Mediazione** to:

- connect each supported area to an existing WordPress page;
- inspect the resolved public URL;
- enable or disable each replacement template independently;
- open the associated page in a separate browser tab.

All templates are disabled by default. When a template or the complete module is disabled, WordPress immediately returns to the active theme's original template and stored page content.

The module supports process, costs, civil and commercial mediation, court-referred mediation, online mediation, application, participation, FAQ, legislation, and case-law pages. Editorial archives read existing published posts from the `normativa` and `giurisprudenza` categories; they do not introduce a separate editorial engine.

## Public Formation module

The Formation module provides optional templates for the institutional formation landing page, the 80-hour base course, the 14-hour advanced course, the 18-hour biennial renewal course, course archives and calendar, teachers, FAQ, enrollment, and formation insights. It reads existing WordPress pages, posts, categories, featured images, and documented post metadata. It does not add an LMS or manage students, payments, attendance, certificates, quizzes, or user accounts.

Open **Mediacon Enterprise → Formazione** to associate existing pages, inspect resolved URLs, configure category slugs and enrollment settings, and enable each public template. Templates and course/teacher detail replacements are opt-in. See [`docs/FORMATION-MODULE.md`](docs/FORMATION-MODULE.md) for the content model and metadata keys.

## Public Editorial module

The Editorial module adds a unified, opt-in presentation layer for the existing Blog, jurisprudence, legislation, insights, courses, search, category archives, and single articles. It uses standard WordPress content and reuses Formation's course repository. Templates are disabled by default and preserve the active theme fallback when inactive.

Open **Mediacon Enterprise → Editoriale** to connect existing categories, enable archive templates independently, configure the shared card system, and review non-destructive content-quality signals. See [`docs/EDITORIAL-MODULE.md`](docs/EDITORIAL-MODULE.md) for behavior, filters, optional metadata, and fallback guarantees.

## Public Preventivo module

The Preventivo module is an opt-in public mediation-cost simulator. It calculates a separate quote for every claimant and invited party, including that party's interest centers, configured tariff, reductions, scenario increases, documented expenses, paid amount, and residual. An invited party marked absent or non-adherent receives no tariff charge; only expenses explicitly assigned to that party remain.

Open **Mediacon Enterprise → Preventivo** to configure tariff brackets, economic reductions and increases, default expenses, explanatory texts, print header/footer, simulation numbering, target page, and frontend status. Default values are configurable estimates and must be checked against the applicable rules before publication. See docs/PREVENTIVO-MODULE.md.

## Public Search module

The Search module provides one WordPress-native search experience for published pages, editorial posts, courses, teachers, and the Mediazione and Formazione FAQ. It does not create an external index, call cloud services, or alter source content. Private, draft, trashed, excluded, or module-disabled content is omitted automatically.

Use the [mediacon_search] shortcode as a reusable header or page component, or enable the dedicated results-page template. Results support transparent relevance scoring, date sorting, area and year filters, pagination, configurable synonyms, and an accessible keyboard-operated autocomplete. Cost-related searches may suggest the separate Preventivo calculator without indexing it as editorial content.

Open **Mediacon Enterprise → Ricerca** to select the results page, searchable areas, exclusions, autocomplete limits, cache duration, synonym groups, and content priorities. See docs/SEARCH-MODULE.md for the complete behavior and ranking model.

## Data removal

Deactivation preserves settings. To remove plugin settings during uninstall, define `MEDIACON_ENTERPRISE_REMOVE_DATA` as `true` before deleting the plugin.

## Development

```shell
composer install
composer lint
composer phpcs
```

## License

GPL-2.0-or-later.
