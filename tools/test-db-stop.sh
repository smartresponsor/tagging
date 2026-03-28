#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
STATE_FILE="${TMPDIR:-/tmp}/smartresponsor-tag-test-db-port"

TEST_DB_PORT="${TEST_DB_PORT:-}"
if [[ -z "$TEST_DB_PORT" && -f "$STATE_FILE" ]]; then
  TEST_DB_PORT="$(cat "$STATE_FILE")"
fi

TEST_DB_PORT="${TEST_DB_PORT:-55432}" docker compose -f "$SCRIPT_DIR/test-db/docker-compose.yml" down -v
rm -f "$STATE_FILE"
