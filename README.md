# Smartresponsor Tag (Tagging)

Canonical tagging component for any object type: create and manage tags, attach/detach them to entities, and expose a stable API for CRUD, assignment, search, suggest, status, and surface discovery.

## Runnable core

The current shipped `host-minimal/` runtime is the source of truth for what is actually runnable now:

- tag CRUD
- assign / unassign
- bulk assignment operations
- assignment list by entity
- `GET /tag/search` and `GET /tag/suggest` with flat read payloads
- authoritative `total` and stable `nextPageToken` on search
- `GET /tag/_status`
- `GET /tag/_surface`

Runtime route truth is centralized in `tag.yaml`, then projected into the host router, public surface config, and route-controller audit.
Canonical public route paths are further projected into contract and surface audits from `config/tag_public_route_paths.php`, so audits do not keep stale hardcoded path lists.

## Current contract notes

- flat search/suggest payload shape without a nested `result` envelope
- 404 `tag_not_found` unassign contract when the tag entity itself is absent

Core runtime assets:

- PSR-4 library under `src/`
- minimal runnable host under `host-minimal/`
- canonical route catalog under `tag.yaml`
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

These assets must not redefine the runtime contract. When they disagree with `host-minimal/`, `config/`, `tag.yaml`, or `contracts/http/`, the runnable core wins.

Smoke/runtime coverage currently validates bulk assignment endpoints, missing-tag unassign semantics, flat read payloads, and authoritative search totals.

## Release and publication assets

For RC/release posture, use:

- `CHANGELOG.md`
- `RELEASE_NOTES.md`
- `docs/public/index.md`
- `docs/release/rc-checklist.md`
- `docs/ops/runbook.md`
- `.github/workflows/release-rc.yml`
- Antora producer surface under `docs/modules/ROOT/`
- generated Swagger/OpenAPI surface under `public/tag/openapi/`

## Quickstart (Docker)

Prereqs:

- Docker Desktop or Docker Engine with Compose

1. Start the stack:

- `docker compose up -d --build`

2. Verify the runtime:

- `curl http://127.0.0.1:8080/tag/_status`
- `curl http://127.0.0.1:8080/tag/_surface`

3. Run QA inside the app container:

- `docker compose exec app composer run -n test:unit`
- `docker compose exec app composer run -n test:integration`
- `docker compose exec app composer run -n phpstan`
- `docker compose exec app composer run -n cs:check`
- `docker compose exec app composer run -n fixture:dry-run`
- `docker compose exec app composer run -n test:panther`
- `docker compose exec app composer run -n test:e2e`

The app container auto-runs migrations and demo seeding on startup. Set `APP_AUTO_SEED=0` if you want a blank runtime.

## Quickstart (host-minimal)

Prereqs:

- PHP 8.2+
- `pdo_pgsql` for a DB-backed Postgres runtime
- `pdo_sqlite` for SQLite-backed local/test paths
- Composer

1. Install dependencies:

- `composer install`
- `composer run -n doctor:php-ext`

2. Apply migrations:

- `php tools/db/tag-migrate.php`
- or self-contained with Docker-backed test DB: `composer run -n db:smoke:self-contained`

3. Run:

- `php -S 127.0.0.1:8080 host-minimal/index.php`
- or self-contained host + Docker-backed test DB: `composer run -n host:test-db:serve`

Environment variables used by code:

- `DB_DSN`, `DB_USER`, `DB_PASS`
- `TENANT` (optional default tenant)
- `TAG_ALLOW_ORIGIN` (optional CORS origin pinning)

## QA commands

- `composer run -n lint`
- `composer run -n lint:admin`
- `composer run -n phpstan`
- `composer run -n cs:check`
- `composer run -n cs:fix`
- `composer run -n fixture:validate`
- `composer run -n fixture:dry-run`
- `composer run -n docs:openapi:publish`
- `composer run -n smoke:runtime`
- `composer run -n audit:release-assets`
- `composer run -n audit:openapi-semantics`
- `composer run -n audit:generated-openapi-surface`
- `composer run -n audit:antora-surface`

## Publish gate

- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:openapi-semantics`
- `composer run -n audit:generated-openapi-surface`
- `composer run -n audit:antora-surface`
- `composer run -n audit:route`
- `composer run -n audit:bootstrap`
- `composer run -n audit:bootstrap-runtime`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n audit:release-assets`
- `composer run -n audit:version`
- `composer run -n audit:core-boundary`
- `composer run -n audit:release-grade-portrait`
- `composer run -n release:preflight`
