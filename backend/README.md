# Backend

API FastAPI per l'avvio locale del Mediacon Hub ERP.

## Avvio rapido Windows

Dal repository principale:

```powershell
scripts\start-backend.bat
```

Oppure manualmente:

```powershell
py -3 -m venv backend\.venv
backend\.venv\Scripts\activate
pip install -r backend\requirements.txt
copy backend\.env.example backend\.env
cd backend
uvicorn app.main:app --host 127.0.0.1 --port 8000 --reload
```

## Endpoint Sprint 1

- `GET /health`
- `GET /modules`
