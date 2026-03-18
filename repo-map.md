REPO MAP
root: .
generated: 2026-02-28 13:45:00 -0600

TREE
.
admin/
config/
config/routes/
contracts/
contracts/http/
db/
db/postgres/
db/postgres/migrations/
docs/
docs/acceptance/
docs/admin/
docs/api/
docs/architecture/
docs/bulk/
docs/data/
docs/db/
docs/demo/
docs/deploy/
docs/fixtures/
docs/http/
docs/ops/
docs/policy/
docs/public/
docs/rc5/
docs/release/
docs/security/
docs/seed/
docs/tag/
fixtures/
host/
host-minimal/
ops/
ops/alerts/
ops/grafana/
public/
public/tag/
public/tag/demo/
public/tag/examples/
release/
release/tag-rc5/
sdk/
sdk/php/
sdk/ts/
src/
src/Application/
src/Cache/
src/Data/
src/Domain/
src/Http/
src/Infra/
src/Ops/
src/Service/
src/ServiceInterface/
tests/
tests/integration/
tools/
tools/audit/
tools/db/
tools/local/
tools/release/
tools/seed/
tools/smoke/
tools/synthetic/


## public consistency gate
- tools/audit/tag-surface-audit.php
- tools/audit/tag-contract-audit.php
- tools/audit/tag-config-audit.php
- tools/audit/tag-sdk-audit.php
- tools/audit/tag-version-audit.php

## public-ready reconcile
- `config/tag_public_surface.php` is the runtime discovery catalog.
- `tools/audit/tag-route-controller-audit.php` checks route-controller references.
- `tools/audit/tag-bootstrap-audit.php` checks `host-minimal/bootstrap.php` exported entries.
- `tools/audit/tag-version-audit.php` keeps catalog version aligned with `MANIFEST.json`.
- `tools/audit/tag-config-audit.php` checks semantic runtime config for public shell expectations.
- `tools/audit/tag-sdk-audit.php` keeps PHP/TS SDK methods aligned with the shipped host-minimal surface.
