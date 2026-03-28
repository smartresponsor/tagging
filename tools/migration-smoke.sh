#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ -z "${DB_DSN:-}" ]]; then
  echo "migration-smoke: using default DB_DSN from tools/db/tag-migrate.php; set DB_DSN/DB_USER/DB_PASS to target a different database" >&2
fi

exec "$ROOT_DIR/tools/db/tag-migration-smoke.sh" "$@"
