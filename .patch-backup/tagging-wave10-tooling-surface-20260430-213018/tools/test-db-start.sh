#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="$SCRIPT_DIR/test-db/docker-compose.yml"
STATE_FILE="${TMPDIR:-/tmp}/smartresponsor-tag-test-db-port"

port_is_available() {
  local port="$1"
  python3 - <<'PY' "$port"
import socket, sys
port = int(sys.argv[1])
with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as sock:
    sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    sys.exit(0 if sock.connect_ex(("127.0.0.1", port)) != 0 else 1)
PY
}

pick_test_db_port() {
  local candidate

  for candidate in 55432 55433 55434 55435 55436 55437 55438 55439; do
    if port_is_available "$candidate"; then
      printf '%s\n' "$candidate"
      return 0
    fi
  done

  echo "could not find a free test DB port in 55432-55439" >&2
  exit 1
}

TEST_DB_PORT="${TEST_DB_PORT:-$(pick_test_db_port)}"

DB_DSN="${DB_DSN:-pgsql:host=127.0.0.1;port=${TEST_DB_PORT};dbname=tag_test}"
DB_USER="${DB_USER:-tag}"
DB_PASS="${DB_PASS:-tag}"

if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  TEST_DB_PORT="$TEST_DB_PORT" docker compose -f "$COMPOSE_FILE" up -d --wait
else
  echo "docker compose is required to start integration postgres" >&2
  exit 1
fi

printf '%s\n' "$TEST_DB_PORT" > "$STATE_FILE"

if [[ "$#" -gt 0 ]]; then
  export TEST_DB_PORT DB_DSN DB_USER DB_PASS
  exec "$@"
fi

echo "export TEST_DB_PORT='$TEST_DB_PORT'"
echo "export DB_DSN='$DB_DSN'"
echo "export DB_USER='$DB_USER'"
echo "export DB_PASS='$DB_PASS'"
