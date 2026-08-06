# Template map

| Area | WordPress template |
|---|---|
| Home | `front-page.php` |
| Chi siamo, Trasparenza, Privacy | `templates/template-institutional.php` or `page.php` |
| Organismo, Mediazione civile, Come funziona, Costi, Istanza/adesione, Online, Mediatori | `templates/template-mediation.php` or the configured Enterprise template |
| Preventivo | `templates/template-enterprise.php`; configured Enterprise template takes priority |
| Sedi, Contatti | `templates/template-contact.php` |
| Formazione, Corsi, Docenti | `templates/template-formation.php` or the configured Enterprise template |
| Blog, Sentenze, Normativa, Approfondimenti | `archive.php`, `single.php`, or `templates/template-editorial.php` |
| Ricerca | `search.php`; `[mediacon_search]` is used when registered |
| Pagina generica | `page.php` |
| Pagina non trovata | `404.php` |

All page templates render `the_content()` unchanged. The named templates group
visual presentation only; they do not assign themselves to pages and do not
change stored metadata.
# Live-site resource map

The canonical live-site resource map is returned by
`mediacon_one_page_map()` in `inc/site-config.php`. Deployment code can use the
`mediacon_one_page_map` filter to alter paths without writing WordPress options,
posts, URLs, or permalinks. Every page defaults to saved WordPress content;
only the configured Costi page may be owned by the enabled Enterprise
Preventivo module.
