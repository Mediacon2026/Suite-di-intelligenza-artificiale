# 11 - API Specification

## Auth
- POST /auth/login
- POST /auth/logout
- GET /auth/me

## Contacts
- GET /contacts
- POST /contacts
- GET /contacts/{id}
- PUT /contacts/{id}

## Mediations
- GET /mediations
- POST /mediations
- GET /mediations/{id}
- PUT /mediations/{id}
- POST /mediations/{id}/sessions
- GET /mediations/{id}/documents

## DGStat
- GET /reports/dgstat?year=&quarter=&office_id=
