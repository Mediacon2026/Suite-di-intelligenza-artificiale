# Formation Module

The Formation module adds opt-in public presentation templates for Mediacon, Ente di Formazione n. 422. It reads existing WordPress pages and posts and does not create or alter pages, categories, users, permalinks, redirects, enrollments, payments, attendance records, certificates, or private areas.

## Activation and fallback

Enable `formation` in the Core module settings, then open **Mediacon Enterprise → Formazione**. Associate existing pages or retain slug discovery and enable only the required templates. Every page template and both post-detail integrations are disabled by default. When the module or a template is disabled, the active theme receives the original WordPress template unchanged.

Supported page slugs are:

- `formazione-mediatori`
- `corso-base-mediatori`
- `corso-approfondimento`
- `corso-aggiornamento-biennale`
- `calendario-corsi`
- `docenti-e-formatori`
- `faq-formazione`
- `iscrizioni-formazione`
- `prossimi-corsi`
- `approfondimenti-formativi`

An administrator may associate any existing page instead of using these conventional slugs.

## Existing content integration

Courses are published WordPress posts in the configured course category (`corsi` by default). Teachers are posts in the configured teacher category (`docenti`), and insights use the configured insight category (`formazione`). Category slugs and the global enrollment URL are configurable; the module never creates those categories.

Course metadata:

| Key | Value |
| --- | --- |
| `_mediacon_course_type` | `base`, `advanced`, `renewal`, `event`, `seminar`, or `workshop` |
| `_mediacon_course_status` | `iscrizioni-aperte`, `posti-esauriti`, or `concluso` |
| `_mediacon_course_start` | Date in `YYYY-MM-DD` format |
| `_mediacon_course_duration` | Public duration label |
| `_mediacon_course_mode` | `online`, `presenza`, or `ibrida` |
| `_mediacon_course_venue` | Public venue |
| `_mediacon_course_teachers` | Speaker or teacher names |
| `_mediacon_course_availability` | Availability label |
| `_mediacon_course_price` | Public price label |
| `_mediacon_course_enrollment_url` | Course-specific enrollment URL |
| `_mediacon_course_program` | One program item per line |
| `_mediacon_course_audience` | Intended audience |
| `_mediacon_course_requirements` | Entry requirements |

Course dates and prices have no hard-coded defaults. The institutional course pages provide duration defaults of 80, 14, and 18 hours; post metadata can override duration.

Teacher metadata:

| Key | Value |
| --- | --- |
| `_mediacon_teacher_qualification` | Professional qualification |
| `_mediacon_teacher_expertise` | Areas of expertise |
| `_mediacon_teacher_courses` | Associated courses |

The post body supplies the biography and the featured image supplies the profile photograph. No WordPress user record is read or changed.

## Public behavior

The course archive supports search, type, delivery mode, status, and date-range filters, orders courses by start date, and paginates results. The calendar displays upcoming published courses. The templates include institutional heroes, breadcrumbs, uniform cards, status badges, program timelines, course facts, teacher profiles, accessible FAQ controls, and enrollment calls to action. Styles are scoped to the module, mobile-first, and use the Core asset pipeline.
