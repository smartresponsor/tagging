#!/usr/bin/env bash
set -euo pipefail

cd /app

if [[ ! -d vendor ]]; then
  composer install --no-interaction --prefer-dist
fi

if [[ "${APP_AUTO_MIGRATE:-0}" == "1" ]]; then
  php tools/db/tag-migrate.php
fi

if [[ "${APP_AUTO_SEED:-0}" == "1" ]]; then
  php tools/seed/tag-seed.php || true
fi

HOST="${APP_HOST:-0.0.0.0}"
PORT="${APP_PORT:-8080}"

if [[ -f public/index.php ]]; then
  exec php -S "${HOST}:${PORT}" -t public
fi

if [[ -f migration/symfony-native-target/public/index.php ]]; then
  exec php -S "${HOST}:${PORT}" -t migration/symfony-native-target/public
fi

echo "No public/index.php runtime entrypoint found." >&2
exit 1
