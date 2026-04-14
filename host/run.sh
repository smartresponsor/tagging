#!/usr/bin/env bash
set -euo pipefail
if [[ ! -f /app/public/index.php ]]; then
  echo "Missing /app/public/index.php (mount SRC_ROOT as /app)"
  ls -la /app/public || true
  exit 1
fi
php -S 0.0.0.0:8080 -t /app/public /app/public/index.php
