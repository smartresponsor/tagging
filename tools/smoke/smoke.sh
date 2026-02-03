#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://localhost:8080}"
TENANT="${TENANT:-demo}"

echo "[smoke] search"
curl -fsS -H "X-Tenant-Id: $TENANT" "$BASE_URL/tag/search?q=s" | jq . >/dev/null || true

echo "[smoke] create tag"
resp=$(curl -fsS -X POST -H "X-Tenant-Id: $TENANT" -H "Content-Type: application/json"   -d '{"name":"Smoke Tag","weight":5}' "$BASE_URL/tag")
id=$(echo "$resp" | jq -r .id)
echo "created id=$id"

echo "[smoke] assign+unassign"
curl -fsS -X POST -H "X-Tenant-Id: $TENANT" -H "X-Idempotency-Key: smoke-1" -H "Content-Type: application/json"   -d '{"entity_type":"product","entity_id":"p-42"}' "$BASE_URL/tag/$id/assign" >/dev/null
curl -fsS -X POST -H "X-Tenant-Id: $TENANT" -H "X-Idempotency-Key: smoke-2" -H "Content-Type: application/json"   -d '{"entity_type":"product","entity_id":"p-42"}' "$BASE_URL/tag/$id/unassign" >/dev/null

echo "[smoke] OK"
