from __future__ import annotations

import argparse
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
MIGRATIONS_ROOT = PROJECT_ROOT / "database" / "migrations"
sys.path.insert(0, str(PROJECT_ROOT))

from backend.app.database import engine


def migration_files(only: str | None = None) -> list[Path]:
    files = sorted(MIGRATIONS_ROOT.glob("*.sql"))
    if only:
        files = [path for path in files if path.name == only]
        if not files:
            raise SystemExit(f"Migrazione non trovata: {only}")
    return files


def apply(only: str | None = None) -> None:
    raw = engine.raw_connection()
    try:
        with raw.cursor() as cursor:
            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS schema_migrations (
                    filename VARCHAR(255) PRIMARY KEY,
                    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
                """
            )
            raw.commit()
            for path in migration_files(only):
                cursor.execute("SELECT 1 FROM schema_migrations WHERE filename = %s", (path.name,))
                if cursor.fetchone():
                    print(f"SKIP {path.name}")
                    continue
                try:
                    cursor.execute(path.read_text(encoding="utf-8"))
                    cursor.execute("INSERT INTO schema_migrations (filename) VALUES (%s)", (path.name,))
                    raw.commit()
                    print(f"APPLIED {path.name}")
                except Exception:
                    raw.rollback()
                    raise
    finally:
        raw.close()


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Applica migrazioni SQL Nexus ERP in modo tracciato.")
    parser.add_argument("--only", help="Applica soltanto il file indicato.")
    args = parser.parse_args()
    apply(args.only)
