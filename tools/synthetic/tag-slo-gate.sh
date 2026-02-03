#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://localhost:8080}"
TENANT="${TENANT:-demo}"
Q="${Q:-demo}"
COUNT="${COUNT:-60}"             # requests
READ_P95_MAX="${READ_P95_MAX:-0.25}"   # seconds
ERROR_RATE_MAX="${ERROR_RATE_MAX:-0.005}"

tmp=$(mktemp)
err=0
ok=0
for i in $(seq 1 "$COUNT"); do
  t=$(curl -s -o /dev/null -w "%{time_total}" -H "X-Tenant-Id: $TENANT" "$BASE_URL/tag/search?q=$Q" || echo "ERR")
  if [[ "$t" == "ERR" ]]; then
    echo "ERR" >> "$tmp"
    err=$((err+1))
  else
    echo "$t" >> "$tmp"
    ok=$((ok+1))
  fi
done

# compute p95 among successes
p95=$(grep -v ERR "$tmp" | sort -n | awk -v n="$ok" 'BEGIN{p=0} {a[NR]=$1} END{if(n==0){print 999}else{idx=int(0.95*n); if(idx<1) idx=1; print a[idx]}}')
err_rate=0
if [[ "$((ok+err))" -gt 0 ]]; then
  err_rate=$(python3 - <<PY
ok=$ok; err=$err
print(err/float(ok+err))
PY
)
fi

echo "requests_total=$((ok+err))"
echo "ok=$ok"
echo "err=$err"
echo "p95=$p95"
echo "error_rate=$err_rate"

fail=0
awk -v p="$p95" -v max="$READ_P95_MAX" 'BEGIN{if (p>max) exit 1; else exit 0}' || fail=1
awk -v e="$err_rate" -v max="$ERROR_RATE_MAX" 'BEGIN{if (e>max) exit 1; else exit 0}' || fail=1

if [[ "$fail" -ne 0 ]]; then
  echo "::error title=SLO Gate Failed::p95=$p95>=$READ_P95_MAX or error_rate=$err_rate>=$ERROR_RATE_MAX"
  exit 1
fi
echo "SLO gate passed: p95=$p95, error_rate=$err_rate"
