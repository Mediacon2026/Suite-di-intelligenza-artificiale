@echo off
setlocal
set "ROOT=%~dp0.."
cd /d "%ROOT%"

set "VENV_DIR=backend\.venv"
set "VENV_PY=%VENV_DIR%\Scripts\python.exe"

if not exist "%VENV_PY%" (
  echo Ambiente virtuale non trovato. Creo %VENV_DIR%...
  py -3 -m venv "%VENV_DIR%"
)

call "%VENV_DIR%\Scripts\activate.bat"
python -m pip install --upgrade pip
python -m pip install -r backend\requirements.txt

if not exist "backend\.env" (
  if exist "backend\.env.example" (
    copy "backend\.env.example" "backend\.env" >nul
    echo Creato backend\.env da backend\.env.example
  ) else (
    echo File backend\.env.example non trovato. Creare backend\.env manualmente.
  )
)

cd backend
python -m uvicorn app.main:app --host 127.0.0.1 --port 8000 --reload
