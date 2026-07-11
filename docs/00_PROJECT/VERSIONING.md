# Versioning

## Strategia
Mediacon Nexus ERP adotta una strategia di versionamento semantico per distinguere evoluzioni funzionali, correzioni e cambiamenti incompatibili.

Versione corrente:

```text
0.1.1-alpha-recovery
```

Nome release:

```text
Nexus ERP 0.1.1 Alpha Recovery
```

Formato consigliato:

```text
MAJOR.MINOR.PATCH
```

## Regole
- `MAJOR`: cambiamenti incompatibili su API, modello dati, permessi o workflow principali.
- `MINOR`: nuove funzionalita' compatibili o estensioni di moduli esistenti.
- `PATCH`: correzioni, miglioramenti interni, aggiornamenti documentali e fix senza impatto contrattuale.

## Release candidate
Le versioni candidate possono usare suffissi come:

```text
1.2.0-rc.1
```

## Versioni Alpha
Le versioni Alpha usano il suffisso:

```text
MAJOR.MINOR.PATCH-alpha
```

`0.1.0-alpha` identifica la prima baseline integrata con Configuration Center, CRM base, Mediazioni base, Fascicoli, Parser Mediazione, Intake Engine e Nexus Kernel 1.0.

`0.1.1-alpha-recovery` identifica il consolidamento forense RECOVERY-001: repository Git recuperato, database allineato con migrazioni additive, test pytest ripristinati e gap logici verificati corretti. Non implica che provider esterni e componenti placeholder siano completi.

## Collegamento con i moduli
Ogni incremento di versione deve indicare i codici modulo coinvolti, facendo riferimento a `docs/MODULE_INDEX.md`.

## Tracciabilita'
Ogni rilascio deve aggiornare:
- `docs/00_PROJECT/CHANGELOG.md`
- `docs/00_PROJECT/RELEASE_NOTES.md`
- documentazione tecnica o funzionale impattata
- test book relativo alle aree modificate
