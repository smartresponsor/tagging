#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DB_DSN="${DB_DSN:-pgsql:host=localhost;port=5432;dbname=app}"
DB_USER="${DB_USER:-app}"
DB_PASS="${DB_PASS:-app}"
if ! command -v psql >/dev/null 2>&1; then
  echo "psql is required" >&2
  exit 1
fi
if [[ "$DB_DSN" =~ host=([^;]+) ]]; then export PGHOST="${BASH_REMATCH[1]}"; fi
if [[ "$DB_DSN" =~ port=([^;]+) ]]; then export PGPORT="${BASH_REMATCH[1]}"; fi
if [[ "$DB_DSN" =~ dbname=([^;]+) ]]; then export PGDATABASE="${BASH_REMATCH[1]}"; fi
export PGUSER="$DB_USER"
export PGPASSWORD="$DB_PASS"
for f in "$ROOT"/db/postgres/migrations/*.sql; do
  psql -v ON_ERROR_STOP=1 -f "$f"
done
echo "migrate: ok"
