# Preventivo module

## Purpose

Preventivo is a public, informational mediation-cost calculator. It does not create or manage cases, customer records, invoices, payments, or legally binding quotes.

The frontend is disabled by default. In **Mediacon Enterprise → Preventivo**, select an existing WordPress page, enable the frontend, and review every economic parameter before publishing it.

## Per-party model

Every party is calculated independently. A party has a role, attendance status, one or more interest centers, its own documented expenses, an amount already paid, a total due, and a residual.

The optional general total is explicitly the arithmetic sum of those autonomous quotes. It is never divided or redistributed.

An invited party marked absent or nonadherent is not charged a tariff. Explicitly assigned documented expenses may still be charged. This prevents the critical one-claimant/one-absent-invitee scenario from doubling the claimant's total.

## Economic rules

- Mandatory mediation applies the configured mandatory reduction.
- Court-ordered mediation always inherits the mandatory economic regime.
- Pure voluntary mediation applies only its configured reduction, which defaults to zero.
- The tariff selected from the configured dispute-value bracket is multiplied by that party's interest-center count.
- The regime reduction is applied to that party's tariff.
- The configured scenario increase and optional additional increase are applied afterward.
- Documented expenses are added only to their assigned party.
- Paid amounts reduce the residual, not the total due, and the residual cannot become negative.

All shipped amounts and percentages are editable estimates. Administrators remain responsible for aligning them with the rules and tariff tables applicable to the organization.

## Scenarios

- Assenza della parte invitata
- Mancato accordo al primo incontro
- Accordo al primo incontro
- Prosecuzione oltre il primo incontro
- Mancato accordo dopo più incontri
- Accordo dopo più incontri
- Proposta del Mediatore

Each scenario has an independently configurable percentage increase. The wizard also supports an explicit additional increase, paid amounts, arbitrary documented expenses, registered letters, digital signature, and extra copies of the minutes.

## Security and output

The public calculation uses a WordPress REST nonce and validates all values again on the server. Administrative saves require manage_options and a dedicated nonce. The browser stores draft wizard state in local storage; no customer registry is created.

The result shows one table per party and a clearly separated general sum. The print command exposes only the result box and uses an A4 print stylesheet, including the configured Mediacon header/footer, date, and sequential simulation reference. Browsers can use their native “Save as PDF” option without an additional PDF dependency.

## Tests

The automated suite covers mandatory, court-ordered, and voluntary regimes; multiple parties and centers; every supported procedural scenario; payments; documented expenses; the WordPress template fallback; A4 printing; an absent invitee; and the no-double-counting invariant.
