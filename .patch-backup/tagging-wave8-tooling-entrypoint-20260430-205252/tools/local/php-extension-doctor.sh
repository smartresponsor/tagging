#!/usr/bin/env bash
set -euo pipefail

have_pgsql=0
have_sqlite=0

if php -m | grep -qx 'pdo_pgsql'; then
  have_pgsql=1
fi

if php -m | grep -qx 'pdo_sqlite'; then
  have_sqlite=1
fi

echo "Host PHP extension status:"
echo "  pdo_pgsql: $([ "$have_pgsql" -eq 1 ] && echo yes || echo no)"
echo "  pdo_sqlite: $([ "$have_sqlite" -eq 1 ] && echo yes || echo no)"

if [ "$have_pgsql" -eq 1 ] && [ "$have_sqlite" -eq 1 ]; then
  exit 0
fi

cat <<'EOF'

Missing host PHP extensions were detected.

For Debian/Ubuntu with Ondrej PHP packages:
  sudo apt-get update
  sudo apt-get install -y php8.4-pgsql php8.4-sqlite3

For Homebrew PHP on macOS:
  brew reinstall php

Notes:
  - A DB-backed host runtime against Docker Postgres needs `pdo_pgsql`.
  - Some local/unit test paths also benefit from `pdo_sqlite`.
  - Docker already includes both extensions, so `docker compose up -d --build` remains the recommended path.
EOF

exit 1
