#!/usr/bin/env bash
set -euo pipefail

cd /app

mkdir -p \
  build/phpstan \
  report/tag \
  report/webhook/spool \
  var/cache/nonce

git config --global --add safe.directory /app || true

if [[ ! -f vendor/autoload.php ]]; then
  composer install --no-interaction --prefer-dist
fi

if [[ -f package-lock.json ]]; then
  npm ci
elif [[ -f package.json ]]; then
  npm install
fi

if [[ -f package.json ]] && ! compgen -G "${PLAYWRIGHT_BROWSERS_PATH:-/ms-playwright}/chromium-*" >/dev/null; then
  npx playwright install chromium
fi

if [[ "${APP_AUTO_MIGRATE:-1}" == "1" ]]; then
  php tools/db/tag-migrate.php
fi

if [[ "${APP_AUTO_SEED:-1}" == "1" ]]; then
  php tools/seed/tag-seed.php
fi

exec php -S "${APP_HOST:-0.0.0.0}:${APP_PORT:-8080}" -t public public/index.php
