#!/usr/bin/env bash
set -euo pipefail
if [[ ! -f /app/host-minimal/index.php ]]; then
  echo "Missing /app/host-minimal/index.php (mount SRC_ROOT/host-minimal)"
  ls -la /app/host-minimal || true
  exit 1
fi
php -S 0.0.0.0:8080 -t /app/host-minimal
