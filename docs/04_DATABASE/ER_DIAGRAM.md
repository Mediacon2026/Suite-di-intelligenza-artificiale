# ER Diagram

## Scopo
Questo documento conterra' la rappresentazione delle principali entita' dati e delle relazioni del sistema.

## Diagramma logico iniziale

```mermaid
erDiagram
    ORGANIZATIONS ||--o{ OFFICES : contains
    ORGANIZATIONS ||--o{ USERS : owns
    USERS }o--o{ ROLES : has
    ROLES }o--o{ PERMISSIONS : grants
    ORGANIZATIONS ||--o{ CONTACTS : manages
    CONTACTS ||--o{ CASES : participates
    CASES ||--o{ CASE_DOCUMENTS : includes
    CASES ||--o{ CASE_TASKS : tracks
    CASES ||--o{ CASE_DEADLINES : schedules
    CASES ||--o{ CASE_TIMELINE : records
    CASES ||--o{ MEDIATIONS : specializes
```

## Note
Il diagramma deve essere aggiornato quando vengono introdotte nuove relazioni strutturali o quando un modulo verticale aggiunge entita' persistenti rilevanti.
