#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8080}"
TENANT="${TENANT:-demo}"
TEST_DB_STARTED=0

cleanup() {
  if [[ "$TEST_DB_STARTED" -eq 1 && "${KEEP_TEST_DB:-0}" != "1" ]]; then
    "$ROOT_DIR/tools/test-db-stop.sh" >/dev/null
  fi
}

echo "tag-serve-test-db: starting docker-backed test DB" >&2
eval "$("$ROOT_DIR/tools/test-db-start.sh")"
TEST_DB_STARTED=1
trap cleanup EXIT

"$ROOT_DIR/tools/db/tag-migration-smoke.sh"
TENANT="$TENANT" "$ROOT_DIR/tools/local/tag-serve.sh"
