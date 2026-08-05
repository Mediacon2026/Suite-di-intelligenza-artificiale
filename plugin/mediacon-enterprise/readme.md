# Mediacon Enterprise

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

The bootstrap creates a service container and registers the hook, asset, router, settings, and module managers. Feature modules implement a small common contract and are booted by the module manager. The initial release contains Mediation and Formation modules.

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
