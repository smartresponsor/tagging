## Shipped audit and tooling

The shipped archive now includes runnable `tools/` scripts for audit, preflight, migrate, seed, clear and smoke.

Recommended publish gate:

- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:bootstrap`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n audit:version`
- `composer run -n release:preflight`

# Smartresponsor Tag (Tagging)

Canonical tagging for any object type (user/product/project/category/…): create and manage tags, attach/detach them to
entities, and expose a stable API for CRUD, assignment, search, and suggest.

This repository contains:

- a PHP library (PSR-4 `App\*`)
- a minimal runnable host (`host-minimal/`)
- database migrations (`db/postgres/migrations/`)
- HTTP contract (OpenAPI): `contracts/http/tag-openapi.yaml`
- ops assets (Grafana/alerts) under `ops/`

## What is actually runnable in the shipped archive

The current `host-minimal/` path exposes:

- tag CRUD
- assign / unassign
- assignment list by entity
- search / suggest
- `_status`
- `_surface`

The shipped archive does **not** currently provide a runnable public path for:

- webhook worker
- metrics endpoint
- runtime public surface catalog via `/tag/_surface`
- quota / RBAC / HMAC middleware chain in `host-minimal/`

Those capabilities should be treated as internal or future work until they are wired into the runnable host.

## Quickstart (host-minimal)

Prereqs:

- PHP 8.2+
- Postgres
- Composer

1) Install dependencies:

- `composer install`

2) Apply migrations:

- `php tools/db/tag-migrate.php`

3) Run:

- `php -S 127.0.0.1:8080 host-minimal/index.php`

Environment variables used by code:

- `DB_DSN`, `DB_USER`, `DB_PASS`
- `TENANT` (optional default tenant)
- `TAG_ALLOW_ORIGIN` (optional CORS origin pinning)

## Integration tests (Postgres harness)

1) Start Postgres (for example with the bundled compose file):

- `docker compose up -d db`

2) Export environment values and apply migrations:

- `export POSTGRES_DB=app POSTGRES_USER=app POSTGRES_PASSWORD=app DB_HOST=127.0.0.1 DB_PORT=5432`
- `for f in db/postgres/migrations/*.sql; do psql "postgresql://$POSTGRES_USER:$POSTGRES_PASSWORD@$DB_HOST:$DB_PORT/$POSTGRES_DB" -f "$f"; done`

3) Run integration suite:

- `vendor/bin/phpunit --testsuite integration`

The integration tests are deterministic only after the schema has been applied to the target database.

## Demo scenario

See: `docs/demo/tag-quick-demo.md`

The first discovery call in the shipped shell is `GET /tag/_surface`. Use it to verify the public runtime catalog before create/search flows.

Recommended publish gate:

- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:bootstrap`
- `composer run -n audit:version`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n release:preflight`

Recommended publish gate:

- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:version`
- `composer run -n release:preflight`
