#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

DB_DSN="${DB_DSN:-pgsql:host=127.0.0.1;port=55432;dbname=tag_test}"
DB_USER="${DB_USER:-tag}"
DB_PASS="${DB_PASS:-tag}"

if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  docker compose -f "$SCRIPT_DIR/test-db/docker-compose.yml" up -d --wait
else
  echo "docker compose is required to start integration postgres" >&2
  exit 1
fi

echo "export DB_DSN='$DB_DSN'"
echo "export DB_USER='$DB_USER'"
echo "export DB_PASS='$DB_PASS'"
