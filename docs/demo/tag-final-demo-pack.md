# Tag final demo pack

This file is the compact final truth pack for demonstration, fixtures, and seed-driven verification.

## Truth order

1. `host-minimal/`
2. `config/`
3. `contracts/http/tag-openapi.yaml`
4. runtime-facing tests
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
6. Run search and assignment reads:
   - `GET /tag/search?q=elect`
   - `GET /tag/assignments?entityType=product&entityId=demo-product-1`

## Source-of-truth assets

- `fixtures/tag-demo-fixture.php`
- `fixtures/tag-demo-catalog.php`
- `seed/tag/tag-demo.ndjson`
- `seed/tag/tag-links-demo.ndjson`
- `docs/demo/tag-quick-demo.md`
- `docs/fixtures/demo.md`
- `docs/seed/tag-seed.md`

## Demo promise

The final demo pack documents only the currently runnable core surface. It must not promise routes, assets, or workflows that are absent from `host-minimal/`, `config/`, or the shipped HTTP contract.
