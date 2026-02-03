#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${TAG_BASE_URL:-}"
if [[ -z "$BASE_URL" ]]; then echo "Set TAG_BASE_URL"; exit 1; fi
SLUG="demo-$RANDOM"
RES=$(curl -sS -XPOST "$BASE_URL/tag" -H 'Content-Type: application/json' -d "{"slug":"$SLUG","label":"Demo"}")
ID=$(echo "$RES" | grep -oE '"id":"[^"]+"' | head -1 | cut -d':' -f2 | tr -d '"')
test -n "$ID"
curl -sS "$BASE_URL/tag?query=demo&limit=5" >/dev/null
echo "OK"
