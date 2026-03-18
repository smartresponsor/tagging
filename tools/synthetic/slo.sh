#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
TENANT="${TENANT:-demo}"
for i in $(seq 1 5); do
  curl -fsS "$BASE_URL/tag/_status" >/dev/null
  curl -fsS -H "X-Tenant-Id: $TENANT" "$BASE_URL/tag/search?q=s" >/dev/null
done
echo "synthetic-slo: ok"
