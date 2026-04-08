#!/usr/bin/env bash
set -euo pipefail

if command -v chromium >/dev/null 2>&1; then
  export PANTHER_CHROME_BINARY
  PANTHER_CHROME_BINARY="$(command -v chromium)"
elif command -v chromium-browser >/dev/null 2>&1; then
  export PANTHER_CHROME_BINARY
  PANTHER_CHROME_BINARY="$(command -v chromium-browser)"
elif command -v google-chrome >/dev/null 2>&1; then
  export PANTHER_CHROME_BINARY
  PANTHER_CHROME_BINARY="$(command -v google-chrome)"
fi

export PANTHER_CHROME_ARGUMENTS="--headless=new --disable-dev-shm-usage --no-sandbox"
vendor/bin/phpunit --testsuite panther
