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
