# Demo fixtures

Runtime fixture set is deterministic and file-driven for authoring, then loaded into Postgres for the shipped host.

Files:

- `fixtures/tag-demo-fixture.php` — authoritative demo payload
- `fixtures/tag-demo-catalog.php` — human/demo-oriented summary and suggested tour ids
- `tools/seed/tag-fixture-validate.php` — structural validator
- `tools/seed/tag-seed.php` — idempotent loader for one tenant
- `tools/seed/tag-clear.php` — clear tenant runtime data before reseed

Usage:

```bash
php tools/seed/tag-fixture-validate.php
SEED_RESET=1 TENANT=demo php tools/seed/tag-seed.php
curl -H "X-Tenant-Id: demo" "http://127.0.0.1:8080/tag/_surface"
curl -H "X-Tenant-Id: demo" "http://127.0.0.1:8080/tag/search?q=elect"
```

Optional:

```bash
FIXTURE_FILE=/absolute/path/custom-tag-fixture.php php tools/seed/tag-seed.php
```
