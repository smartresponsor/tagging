#!/usr/bin/env bash
set -euo pipefail
BASE="${SR_API_BASE:-http://localhost:8080}"
HDR=(-H "Content-Type: application/json" -H "X-Actor-Id: smoke")

echo "[1] list tags"
curl -sS "${BASE}/tag?limit=1" >/dev/null || { echo "list FAIL"; exit 1; }
echo "[2] metrics"
curl -sS "${BASE}/tag/_metrics" | head -n 5
echo "OK"
