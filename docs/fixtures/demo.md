# Demo fixtures

Runtime fixture set is deterministic and file-driven for authoring, then loaded into Postgres for the shipped host.

Authoritative assets:

- `fixtures/tag-demo-fixture.php` — authoritative demo payload
- `fixtures/tag-demo-catalog.php` — human/demo-oriented summary and suggested tour ids
- `seed/tag/tag-demo.ndjson` — seed-ready tag records
- `seed/tag/tag-links-demo.ndjson` — seed-ready assignment/link records

Support tools:

- `tools/seed/tag-fixture-validate.php` — structural validator
- `tools/seed/tag-seed.php` — idempotent loader for one tenant
- `tools/seed/tag-clear.php` — clear tenant runtime data before reseed

Minimal truthful flow:

```bash
php tools/seed/tag-fixture-validate.php
SEED_RESET=1 TENANT=demo php tools/seed/tag-seed.php
curl -H "X-Tenant-Id: demo" "http://127.0.0.1:8080/tag/_surface"
curl -H "X-Tenant-Id: demo" "http://127.0.0.1:8080/tag/search?q=elect"
```

See also:

- `docs/demo/tag-quick-demo.md`
- `docs/demo/tag-final-demo-pack.md`
- `docs/seed/tag-seed.md`
