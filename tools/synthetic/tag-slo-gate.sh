#!/usr/bin/env bash
set -euo pipefail

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
TENANT="${TENANT:-demo}"
READ_P95_MAX="${READ_P95_MAX:-0.25}"
ERROR_RATE_MAX="${ERROR_RATE_MAX:-0.005}"
ITERATIONS="${ITERATIONS:-20}"
SEARCH_QUERY="${SEARCH_QUERY:-elect}"
results_file="$(mktemp)"
body_file="$(mktemp)"

cleanup() {
  rm -f "$results_file" "$body_file"
}

now() {
  python3 - <<'PY'
import time
print(time.time())
PY
}

elapsed_seconds() {
  local started="$1"
  local ended="$2"
  python3 - <<PY
start=${started}
end=${ended}
print(f"{end-start:.6f}")
PY
}

curl_code() {
  local path="$1"
  curl -sS -o "$body_file" -w '%{http_code}' -H "X-Tenant-Id: ${TENANT}" "${BASE_URL}${path}" || true
}

hit() {
  local path="$1"
  local started ended code elapsed
  started="$(now)"
  code="$(curl_code "$path")"
  ended="$(now)"
  elapsed="$(elapsed_seconds "$started" "$ended")"
  printf '%s %s\n' "$code" "$elapsed"
}

trap cleanup EXIT

for _ in $(seq 1 "$ITERATIONS"); do
  hit "/tag/_status" >> "$results_file"
  hit "/tag/search?q=${SEARCH_QUERY}&pageSize=10" >> "$results_file"
done

python3 - <<'PY' "$results_file" "$READ_P95_MAX" "$ERROR_RATE_MAX"
import json, math, sys
path, p95_max, err_max = sys.argv[1], float(sys.argv[2]), float(sys.argv[3])
rows=[]
with open(path,'r',encoding='utf-8') as fh:
    for line in fh:
        code_s, elapsed_s = line.strip().split()
        rows.append((int(code_s), float(elapsed_s)))
if not rows:
    print(json.dumps({'ok': False, 'code': 'no_samples'}))
    sys.exit(1)
lat=[r[1] for r in rows]
lat.sort()
idx=max(0, math.ceil(0.95*len(lat))-1)
p95=lat[idx]
errors=sum(1 for code,_ in rows if code < 200 or code >= 400)
error_rate=errors/len(rows)
out={'ok': p95 <= p95_max and error_rate <= err_max, 'samples': len(rows), 'p95': round(p95,6), 'read_p95_max': p95_max, 'error_rate': round(error_rate,6), 'error_rate_max': err_max}
print(json.dumps(out, ensure_ascii=False))
sys.exit(0 if out['ok'] else 1)
PY
