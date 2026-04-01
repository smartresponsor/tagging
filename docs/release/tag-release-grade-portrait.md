# Tag release-grade portrait

This document is the compact release-grade portrait for the current Tagging/Tag slice.

## Runnable core

The runnable core is limited to the shipped runtime and contract assets:

- `src/`
- `host-minimal/`
- `config/`
- `tag.yaml`
- `contracts/http/tag-openapi.yaml`
- `db/postgres/migrations/`

## Current public shell

The current release-grade public shell includes:

- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `GET|PATCH|DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `POST /tag/assignments/bulk`
- `POST /tag/assignments/bulk-to-entity`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

## Adjacent cargo (not core runtime)

The following trees support delivery, demos, or operations, but they do not redefine runtime truth:

- `admin/`
- `docs/`
- `ops/`
- `release/`
- `report/`
- `sdk/`
- `public/`
- `tools/`

## Error visibility posture

Current runtime error visibility is expected to remain observable through lightweight sinks and explicit failure codes, especially for:

- `status.db_probe_failed`
- `quota.count_failed`
- `assign_failed`
- `unassign_failed`
- `tag_not_found`

## Read and write guarantees

Release-grade confidence depends on the current runtime keeping these guarantees true:

- search and suggest return flat payloads without nested `result`
- search returns authoritative `total`
- unassign distinguishes missing tag entities from missing links
- bulk assignment routes are part of the public shell, not hidden internal routes

## Quality gates

Release-grade confidence for the current slice depends on these gates staying green:

- `composer test`
- `composer run -n smoke:runtime`
- `composer run -n audit:core-boundary`
- `composer run -n audit:demo-truth-pack`
- `composer run -n audit:sdk`
- `composer run -n audit:release-grade-portrait`

## Demo and fixtures truth

The release portrait must stay aligned with the final runnable demo line:

- `docs/demo/tag-final-demo-pack.md`
- `docs/public/tag-public-ready-checklist.md`
- `docs/admin/user-guide.md`
- `fixtures/tag-demo-fixture.php`
- `seed/tag/tag-demo.ndjson`
- `GET /tag/_surface`
- `GET /tag/_status`

## Promise

This portrait must describe only the currently runnable and test-backed slice. It must not promise routes, assets, SDK methods, or operational guarantees that are absent from the shipped runtime, current audits, and current tests.
