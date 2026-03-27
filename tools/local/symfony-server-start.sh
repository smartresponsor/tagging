#!/usr/bin/env bash
set -euo pipefail

HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8000}"

if command -v symfony >/dev/null 2>&1; then
  exec symfony server:start \
    --allow-http \
    --no-tls \
    --port="${PORT}" \
    --dir=public
fi

echo "symfony CLI not found; falling back to php -S ${HOST}:${PORT}" >&2
exec php -S "${HOST}:${PORT}" -t public public/index.php
