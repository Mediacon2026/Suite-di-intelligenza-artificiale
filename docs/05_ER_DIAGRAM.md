# 05 - ER Diagram

```mermaid
erDiagram
    organizations ||--o{ offices : has
    organizations ||--o{ users : has
    users }o--o{ roles : user_roles
    contacts ||--o{ contact_roles : has
    offices ||--o{ mediations : manages
    contacts ||--o{ mediation_parties : participates
    mediations ||--o{ mediation_parties : has
    mediations ||--o{ mediation_sessions : has
    mediations ||--o{ mediation_fees : has
    mediations ||--o{ documents : stores
    mediations ||--o{ dgstat_reports : feeds
```
