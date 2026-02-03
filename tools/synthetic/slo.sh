#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://localhost:8080}"
TENANT="${TENANT:-demo}"
READ_P95_MAX="${READ_P95_MAX:-0.25}"
ERROR_RATE_MAX="${ERROR_RATE_MAX:-0.005}"
if [[ -f tools/synthetic/tag-slo-gate.sh ]]; then
  BASE_URL="$BASE_URL" TENANT="$TENANT" READ_P95_MAX="$READ_P95_MAX" ERROR_RATE_MAX="$ERROR_RATE_MAX" bash tools/synthetic/tag-slo-gate.sh
else
  echo "Missing tools/synthetic/tag-slo-gate.sh (from E5). Skipping."
fi
