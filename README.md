# Smartresponsor Tag (Tagging)

Canonical tagging for any object type (user/product/project/category/…): create and manage tags, attach/detach them to
entities, keep redirects after merges, and expose a stable API for search/suggest.

This repository contains:

- a PHP library (PSR-4 `App\\*`)
- a minimal runnable host (`host-minimal/`)
- database migrations (`db/postgres/migrations/`)
- HTTP contract (OpenAPI): `contracts/http/tag-openapi.yaml`
- ops assets (Grafana/alerts) under `ops/`

## What “prod-ready” means here

- Idempotency-ready writes (request id / replay safety)
- HMAC signature support (request authenticity)
- Multi-tenant isolation (tenant id in every boundary)
- Redirects after tag merge (stable links)
- Bulk import jobs and status endpoints
- Webhooks for downstream sync
- Metrics endpoint for SLO gates

## Quickstart (host-minimal)

Prereqs:

- PHP 8.2+
- Postgres
- Composer

1) Install dependencies:

- `composer install`

1) Apply migrations (example using psql):

- `psql "$DB_DSN" -U "$DB_USER" -f db/postgres/migrations/2025_10_27_tag.sql`

1) Run:

- `php -S 127.0.0.1:8080 host-minimal/index.php`

Environment variables used by code:

- `DB_DSN`, `DB_USER`, `DB_PASS`
- `TENANT` (optional default tenant)
- `SR_HMAC_SECRET` (optional; enable signature verification)
- `TAG_BASE_URL` (optional; used for webhooks/redirect URLs)

## Integration tests (Postgres harness)

1) Start isolated Postgres test DB:

- `tools/test-db-start.sh`

1) Export environment values from the script output and run integration suite:

- `vendor/bin/phpunit --testsuite integration`

1) Stop and cleanup test DB:

- `tools/test-db-stop.sh`

The integration tests are deterministic: schema is bootstrapped in-test and tables are truncated before each test case.

## Demo scenario

See: `docs/demo/tag-quick-demo.md`

## Release notes and upgrades

- Release notes template: `docs/release/tag-release-notes-template.md`
- Versioning policy: `docs/release/tag-versioning.md`
- Upgrade guides: `docs/release/tag_rc*_upgrade.md`

## License

Proprietary. Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
