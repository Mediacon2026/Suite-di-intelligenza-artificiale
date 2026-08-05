# Mediacon Design Core compatibility

Mediacon Enterprise 0.7.0 includes a bounded compatibility module and the source of a separate WordPress dependency bridge. The bridge must be installed under the exact folder `mediacon-design-core`; it delegates to Enterprise and does not create a container, router, settings manager, module manager, or asset manager.

## Verified infrastructure contracts

The compatibility module defines these constants only when they do not already exist:

- `MEDIACON_DESIGN_CORE_VERSION`
- `MEDIACON_DESIGN_CORE_FILE`
- `MEDIACON_DESIGN_CORE_PATH`
- `MEDIACON_DESIGN_CORE_URL`

It exposes the following guarded functions:

- `mediacon_design_core()`
- `mediacon_design_core_path()` and `mediacon_design_core_url()`
- `mediacon_design_core_register_style()` and `mediacon_design_core_register_script()`
- `mediacon_design_core_enqueue_style()` and `mediacon_design_core_enqueue_script()`
- `mediacon_design_core_register_page()` and `mediacon_design_core_page_id()`
- `mediacon_design_core_template()` and `mediacon_design_core_component()`
- `mediacon_design_core_setting()`
- `mediacon_design_core_logo_url()`

The asset handles `mediacon-design-core` for CSS and JavaScript delegate to the corresponding Enterprise assets. Relative asset paths are constrained to the Enterprise plugin directory. Template rendering delegates to the existing safe Enterprise loader. Missing templates return `false` and are recorded instead of being represented by fabricated content.

Lifecycle actions are `mediacon_design_core_loaded` and `mediacon_design_core_init`. Filters are `mediacon_design_core_path`, `mediacon_design_core_url`, `mediacon_design_core_logo_url`, and `mediacon_design_core_pages`.

## Collision and failure policy

Every constant and global function is guarded. Explicit type aliases created through `LegacyClassBridge` require an existing Enterprise target and never replace an existing class, interface, or trait. No application-specific aliases are registered because no legacy PHP sources were supplied. Missing requirements and collisions are visible under **Mediacon Enterprise > Compatibilità legacy**.

Existing installations are migrated non-destructively: compatibility is enabled by default through `compatibility_enabled`, while the other saved module choices remain unchanged. Setting that flag to `false` or returning `false` from `mediacon_enterprise_compatibility_enabled` makes the module inert.

The layer does not simulate legal, tariff, accounting, mediation, or training results. It does not automatically install the bridge and never writes to `wp-content/plugins`.

## Current limit

This is an infrastructure compatibility surface, not evidence of complete compatibility with unidentified legacy plugins. Full compatibility can be asserted only after their PHP sources are supplied, mapped, and loaded by the regression harness.
