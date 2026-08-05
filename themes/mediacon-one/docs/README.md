# Mediacon One

Mediacon One is the proprietary presentation theme for `mediacon.org`. It
renders existing WordPress content and leaves business rules, calculations,
search services, compatibility, and configuration to Mediacon Enterprise.

## Boundaries

- The theme never creates or updates posts, pages, menus, slugs, URLs, options,
  or database records.
- Enterprise template filters continue to own configured Mediation, Formation,
  Editorial, Preventivo, and Search pages.
- The theme calls `[mediacon_search]` only when Enterprise and the shortcode are
  available. Native WordPress search is the fallback.
- No Elementor API, remote code, CDN, jQuery, or compatibility plugin is used.
- The theme provides no state-changing form handler. Any form supplied by a
  plugin remains responsible for its nonce, capability checks, validation, and
  persistence.

## Navigation and contact data

Assign existing WordPress menus to Primary, Mega, Footer, and Social locations.
The theme does not invent menu items or social URLs. The footer includes only
the identifiers and office cities supplied for the project; detailed contacts
remain existing WordPress content reachable through the Contatti page.

## Performance

Assets are local, dependency-free, versioned with `filemtime()`, and compatible
with page caching. Navigation JavaScript is deferred; accordion JavaScript is
loaded only on pages and the front page. Images use WordPress responsive image
markup, lazy loading in cards, and fixed card crops.
