# Search module

The public Search module offers a single, accessible search interface backed by bounded WordPress queries and the content repositories already used by Mediacon Enterprise. It creates no external index, sends no query to third parties, stores no personal data, and never changes posts, pages, terms, or module content.

## Searchable content

Only published public content is eligible. Administrators can include or exclude WordPress pages, Blog posts, jurisprudence, legislation, insights, courses, teachers, Mediazione and Formazione FAQ, and the public pages configured by those two modules. Explicit post IDs can also be excluded.

When Mediazione, Formazione, or Editoriale is disabled, its classified content is omitted and generic WordPress search remains available. Preventivo content is not indexed. Queries containing configured cost terms can instead receive a direct suggestion for the enabled calculator page.

## Reusable interface

The [mediacon_search] shortcode renders the global search form and can be placed in a header, widget area, or page. The configured results page can use the optional Search template; disabling that template restores the active theme immediately.

Autocomplete starts after the configured minimum length, waits 250 milliseconds after input, and returns no more than ten suggestions. Arrow Up and Arrow Down move through suggestions, Enter follows the selected result, and Escape closes the list. The form, labels, live status, focus state, expanded state, controlled-list relationship, and active descendant are exposed to assistive technologies.

## Filters and URLs

The result page supports All, Mediazione, Formazione, Blog, Sentenze, Normativa, Approfondimenti, Corsi, and FAQ. It also supports relevance or date ordering, publication year, and pagination. Query, filter, year, sort order, and page remain in the URL so a result view can be bookmarked or shared.

## Ranking

Ranking is deterministic and testable:

- exact normalized title match: 100 points;
- each matched query term in the title: 20 points;
- each taxonomy or category match: 12 points;
- each excerpt match: 8 points;
- each body-content match: 3 points;
- the configured content-area priority is added afterward.

Publication date is only a secondary tie-breaker. Choosing date order bypasses relevance ordering. Synonym expansion uses administrator-defined groups and does not use AI or opaque scoring.

## Performance and security

Repository requests are publication-status constrained, capped, and avoid unnecessary metadata loading. Result cache keys include the normalized query, filters, order, year, and page. The Core Cache Manager versions the cache namespace and invalidates it after public content or taxonomy changes and after Search settings are saved.

Search input is sanitized and bounded by configurable minimum and maximum lengths. REST autocomplete requires a WordPress nonce and applies a configurable transient-based rate limit. Administrative changes require manage_options and a settings nonce. Output is escaped, highlighting only adds safe mark elements, and no unprepared SQL is used.

## Administration

Open **Mediacon Enterprise → Ricerca** to configure the results page and template, included areas, excluded IDs, results per page, suggestion limit, query bounds, synonym groups, content priorities, autocomplete, cache use and lifetime, and the request rate limit. Settings are non-destructive and take effect without modifying public content.
