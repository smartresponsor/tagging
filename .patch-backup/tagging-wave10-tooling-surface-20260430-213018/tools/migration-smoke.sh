#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEST_DB_STARTED=0

cleanup() {
  if [[ "$TEST_DB_STARTED" -eq 1 && "${KEEP_TEST_DB:-0}" != "1" ]]; then
    "$ROOT_DIR/tools/test-db-stop.sh" >/dev/null
  fi
}

if [[ -z "${DB_DSN:-}" ]]; then
  eval "$("$ROOT_DIR/tools/test-db-start.sh")"
  echo "migration-smoke: using docker-backed test DB at ${DB_DSN}" >&2
  TEST_DB_STARTED=1
  trap cleanup EXIT
fi

"$ROOT_DIR/tools/db/tag-migration-smoke.sh" "$@"
