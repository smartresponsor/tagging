#!/usr/bin/env bash
set -euo pipefail
php "$(dirname "$0")/tag-migrate.php"
echo "OK tag-migration-smoke"
