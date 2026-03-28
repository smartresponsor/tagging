#!/usr/bin/env bash
set -euo pipefail

HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8000}"

if ! bash tools/local/php-extension-doctor.sh >/dev/null 2>&1; then
  echo "host PHP is missing pdo_pgsql and/or pdo_sqlite; see 'composer run -n doctor:php-ext' for install commands" >&2
fi

if command -v symfony >/dev/null 2>&1; then
  exec symfony server:start \
    --allow-http \
    --no-tls \
    --port="${PORT}" \
    --dir=public
fi

echo "symfony CLI not found; falling back to php -S ${HOST}:${PORT}" >&2
exec php -S "${HOST}:${PORT}" -t public public/index.php
