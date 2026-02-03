#!/usr/bin/env bash
set -euo pipefail
SRC_ROOT="${SRC_ROOT:-./bundle}"
echo "[migrate] scanning $SRC_ROOT/db/postgres/migrations"
mapfile -t files < <(find "$SRC_ROOT/db/postgres/migrations" -type f -name "*.sql" | sort)
if [[ ${#files[@]} -eq 0 ]]; then
  echo "No migrations found under $SRC_ROOT/db/postgres/migrations"
  exit 0
fi
for f in "${files[@]}"; do
  echo "[migrate] applying $(basename "$f")"
  docker compose exec -T db psql -U "${POSTGRES_USER:-app}" -d "${POSTGRES_DB:-app}" -f "/app$(realpath --relative-to="$(pwd)" "$f" | sed 's#^.#/#')" 2>/dev/null ||   docker compose exec -T db psql -U "${POSTGRES_USER:-app}" -d "${POSTGRES_DB:-app}" -f "-c" "\i '$(basename "$f")'"
done
echo "[migrate] done"
