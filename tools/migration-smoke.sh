#!/usr/bin/env bash
set -euo pipefail

# Migration smoke for Postgres.
# - By default starts docker compose db, migrates, verifies critical tables, then stops.
# - Use --no-start when DB is already available (e.g. CI postgres service).

NO_START="0"
if [[ "${1:-}" == "--no-start" ]]; then
  NO_START="1"
fi

POSTGRES_DB="${POSTGRES_DB:-app}"
POSTGRES_USER="${POSTGRES_USER:-app}"
POSTGRES_PASSWORD="${POSTGRES_PASSWORD:-app}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"

function psql_ping() {
  PGPASSWORD="$POSTGRES_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "select 1" >/dev/null 2>&1
}

if [[ "$NO_START" == "0" ]]; then
  echo "[migration-smoke] starting docker compose db"
  docker compose up -d db
fi

echo "[migration-smoke] waiting for Postgres at ${DB_HOST}:${DB_PORT}"
for i in $(seq 1 40); do
  if psql_ping; then
    break
  fi
  sleep 1
done

if ! psql_ping; then
  echo "[migration-smoke] Postgres not ready"
  exit 3
fi

echo "[migration-smoke] applying migrations from db/postgres/migrations"
mapfile -t files < <(find "db/postgres/migrations" -type f -name "*.sql" | sort)
if [[ ${#files[@]} -eq 0 ]]; then
  echo "[migration-smoke] no migrations found"
  exit 1
fi

for f in "${files[@]}"; do
  echo "[migration-smoke] apply $(basename "$f")"
  PGPASSWORD="$POSTGRES_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -v ON_ERROR_STOP=1 -f "$f" >/dev/null
done

echo "[migration-smoke] verifying critical tables"
required_tables=(
  "tag_entity"
  "tag_relation"
  "tag_policy"
  "tag_audit_log"
  "outbox_event"
  "idempotency_store"
)
for t in "${required_tables[@]}"; do
  PGPASSWORD="$POSTGRES_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -t -A -c "select count(*) from information_schema.tables where table_name='${t}'" | grep -q "^1$" || {
    echo "[migration-smoke] missing table: ${t}"
    exit 2
  }
done

echo "[migration-smoke] OK"

if [[ "$NO_START" == "0" ]]; then
  echo "[migration-smoke] stopping docker compose"
  docker compose down -v
fi
