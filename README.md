# Smartresponsor Tag (Tagging)

Canonical tagging component for any object type: create and manage tags, attach/detach them to entities, and expose a stable API for CRUD, assignment, search, suggest, status, and surface discovery.

## Runnable core

The current shipped `host-minimal/` runtime is the source of truth for what is actually runnable now:

- tag CRUD
- assign / unassign
- assignment list by entity
- search / suggest
- `GET /tag/_status`
- `GET /tag/_surface`

Core runtime assets:

- PSR-4 library under `src/`
- minimal runnable host under `host-minimal/`
- database migrations under `db/postgres/migrations/`
- HTTP contract under `contracts/http/tag-openapi.yaml`
- config under `config/`
- fixtures / seed used by demo and validation

## Adjacent assets (not core runtime)

The following trees belong to delivery, demo, release, or operational support. They are valuable, but they are not the runtime core of the component:

- `admin/`
- `docs/`
- `ops/`
- `release/`
- `report/`
- `sdk/`
- `public/`
- helper scripts under `tools/`

These assets must not redefine the runtime contract. When they disagree with `host-minimal/`, `config/`, or `contracts/http/`, the runnable core wins.

## Quickstart (host-minimal)

Prereqs:

- PHP 8.2+
- Postgres
- Composer

1. Install dependencies:

- `composer install`

2. Apply migrations:

- `php tools/db/tag-migrate.php`

3. Run:

- `php -S 127.0.0.1:8080 host-minimal/index.php`

Environment variables used by code:

- `DB_DSN`, `DB_USER`, `DB_PASS`
- `TENANT` (optional default tenant)
- `TAG_ALLOW_ORIGIN` (optional CORS origin pinning)

## Integration tests (Postgres harness)

1. Start Postgres:

- `docker compose up -d db`

2. Apply migrations:

- `export POSTGRES_DB=app POSTGRES_USER=app POSTGRES_PASSWORD=app DB_HOST=127.0.0.1 DB_PORT=5432`
- `for f in db/postgres/migrations/*.sql; do psql "postgresql://$POSTGRES_USER:$POSTGRES_PASSWORD@$DB_HOST:$DB_PORT/$POSTGRES_DB" -f "$f"; done`

3. Run suites:

- `composer test`
- `composer run -n test:integration`
- `composer run -n test:all`

## Demo scenario

See `docs/demo/tag-quick-demo.md`.
Start with `GET /tag/_surface` to verify the public runtime catalog before create/search flows.

## Publish gate

- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:bootstrap`
- `composer run -n audit:bootstrap-runtime`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n audit:version`
- `composer run -n audit:core-boundary`
- `composer run -n audit:release-grade-portrait`
- `composer run -n release:preflight`

## Repository hygiene

Run `composer run -n audit:repo-hygiene` to verify that transport-only wave metadata is not kept in the repository root. Cumulative snapshots must not contain root transport artifacts such as `MANIFEST.wave-*.json`, `ZZ_*`, duplicate tag config files, or transient workspace directories.
