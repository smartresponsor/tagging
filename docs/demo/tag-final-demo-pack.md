# Tag final demo pack

This file is the compact final truth pack for demonstration, fixtures, SDK handoff, and seed-driven verification.

## Truth order

1. `host-minimal/`
2. `config/`
3. `contracts/http/tag-openapi.yaml`
4. runtime-facing tests and audits
5. docs and SDK examples

## Runnable demo flow

1. Validate fixtures:
   - `php tools/seed/tag-fixture-validate.php`
2. Apply migrations:
   - `php tools/db/tag-migrate.php`
3. Seed deterministic demo data:
   - `SEED_RESET=1 TENANT=demo php tools/seed/tag-seed.php`
4. Verify runtime discovery:
   - `GET /tag/_surface`
5. Verify runtime health:
   - `GET /tag/_status`
6. Verify truthful reads:
   - `GET /tag/search?q=elect`
   - `GET /tag/suggest?q=pre`
   - search returns flat payloads and authoritative `total`
7. Verify bulk write surface:
   - `POST /tag/assignments/bulk`
   - `POST /tag/assignments/bulk-to-entity`
8. Verify assignment reads and write-contract symmetry:
   - `GET /tag/assignments?entityType=product&entityId=demo-product-1`
   - `POST /tag/{id}/unassign` returns `404 tag_not_found` when the tag entity itself is absent

## SDK-aligned handoff

The shipped SDK surface is expected to match the current public runtime API, including:

- `status()` / `surface()`
- `create()` / `get()` / `patch()` / `delete()`
- `assign()` / `unassign()` / `assignments()`
- `bulkAssignments()`
- `assignBulkToEntity()`
- `search()` / `suggest()`

## Source-of-truth assets

- `fixtures/tag-demo-fixture.php`
- `fixtures/tag-demo-catalog.php`
- `seed/tag/tag-demo.ndjson`
- `seed/tag/tag-links-demo.ndjson`
- `docs/demo/tag-quick-demo.md`
- `docs/fixtures/demo.md`
- `docs/seed/tag-seed.md`
- `sdk/README.md`

## Demo promise

The final demo pack documents only the currently runnable core surface. It must not promise routes, assets, SDK methods, or workflows that are absent from `host-minimal/`, `config/`, the shipped SDK clients, or the HTTP contract.
