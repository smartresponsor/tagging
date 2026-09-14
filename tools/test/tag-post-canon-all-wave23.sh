#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
REPO_ROOT="${1:-$(cd -- "${SCRIPT_DIR}/../.." >/dev/null 2>&1 && pwd)}"
RUNNER="${REPO_ROOT}/tools/test/tag-post-canon-all-wave22.php"
COMPOSER_JSON="${REPO_ROOT}/composer.json"

if [[ ! -f "${RUNNER}" ]]; then
  echo "Missing complete post-canon runner: ${RUNNER}" >&2
  exit 1
fi

if [[ ! -f "${COMPOSER_JSON}" ]]; then
  echo "Missing composer.json: ${COMPOSER_JSON}" >&2
  exit 1
fi

if ! grep -q 'App\\\\Tagging\\\\' "${COMPOSER_JSON}"; then
  echo 'composer.json must keep App\Tagging\ as the component namespace.' >&2
  exit 1
fi

cd "${REPO_ROOT}"
php "${RUNNER}"
