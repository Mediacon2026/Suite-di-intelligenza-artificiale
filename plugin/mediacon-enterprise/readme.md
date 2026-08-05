# Mediacon Enterprise

Mediacon Enterprise is a modular WordPress foundation for mediation and professional-formation workflows.

## Requirements

- WordPress 6.4 or newer
- PHP 8.1 or newer

## Installation

1. Upload `mediacon-enterprise.zip` from **Plugins → Add New → Upload Plugin**.
2. Activate **Mediacon Enterprise**.
3. Open **Mediacon Enterprise** in the WordPress administration menu.

The distributable archive includes Composer's optimized production autoloader.

## Architecture

The bootstrap creates a service container and registers the hook, asset, router, settings, and module managers. Feature modules implement a small common contract and are booted by the module manager. The initial release contains Mediation and Formation modules.

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
