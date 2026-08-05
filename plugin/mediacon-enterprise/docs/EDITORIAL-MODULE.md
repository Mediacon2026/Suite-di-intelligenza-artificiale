# Editorial Module

The Editorial module provides opt-in public presentation for existing content on mediacon.org. It uses standard WordPress posts, categories, tags, dates, featured images, excerpts, and permalinks. It does not create a post type, category, tag, page, redirect, permalink, or alternative content-management system.

## Activation and fallback

Enable `editorial` through the Core module settings and open **Mediacon Enterprise → Editoriale**. Every archive integration and the single-article template are disabled by default. If the module or an individual template is disabled, WordPress receives the original active-theme template unchanged.

Supported areas are Blog, Sentenze e Giurisprudenza, Normativa, Approfondimenti, Prossimi Corsi, WordPress search, category archives, and individual articles. Category associations can be selected from existing categories or detected from the conventional `giurisprudenza`, `normativa`, and `approfondimenti` slugs.

## Unified card framework

Blog, jurisprudence, legislation, insights, and Formation course results share a single visual card contract. Cards have equal height, a configurable image ratio, `object-fit: cover`, lazy-loaded images, a branded image fallback, two-line title and excerpt clamping, visible category and date information, keyboard focus, and responsive columns. Administrators can configure title and excerpt character limits, image ratio, columns, and posts per page.

## Filters

Archive queries use `WP_Query` and preserve filter values in the query string. Available filters include text, existing category, published year, existing tag as topic or subject, and order. Jurisprudence and legislation authority options appear only when the corresponding values exist on published posts.

Optional existing metadata read by the module:

| Key | Used for |
| --- | --- |
| `_mediacon_judicial_body` | Judicial-body filter for jurisprudence |
| `_mediacon_decision_number` | Decision number displayed on jurisprudence cards |
| `_mediacon_legal_source` | Source filter for legislation |

The module never adds or populates these values. Missing values are omitted from the interface.

## Formation integration

The Prossimi Corsi archive reuses the Formation `CourseRepository` for query, dates, course type, mode, enrollment status, excerpt, image, and URL. It does not copy course-query logic or replace the Formation content model. Course and teacher single templates remain under Formation ownership.

## Single article

The optional single template contains an institutional hero, breadcrumb, metadata, featured image, original post content, related posts, previous/next navigation, a final call to action, and accessible email, LinkedIn, and copy-link controls. No remote JavaScript library is loaded.

## Quality signals

The administration page scans recent published posts and reports missing featured images, manual excerpts, categories, empty content, long titles, missing image alternative text, possible visible shortcodes, and multiple `H1` elements. The audit is read-only and never repairs content automatically.
