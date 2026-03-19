# Tag release-grade portrait

This document is the compact release-grade portrait for the current Tagging/Tag slice.

## Runnable core

The runnable core is limited to the shipped runtime and contract assets:

- `src/`
- `host-minimal/`
- `config/`
- `contracts/http/tag-openapi.yaml`
- `db/postgres/migrations/`

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

## Quality gates

Release-grade confidence for the current slice depends on these gates staying green:

- `composer test`
- `composer run -n audit:core-boundary`
- `composer run -n audit:demo-truth-pack`
- `composer run -n audit:release-grade-portrait`

## Demo and fixtures truth

The release portrait must stay aligned with the final runnable demo line:

- `docs/demo/tag-final-demo-pack.md`
- `fixtures/tag-demo-fixture.php`
- `seed/tag/tag-demo.ndjson`
- `GET /tag/_surface`
- `GET /tag/_status`

## Promise

This portrait must describe only the currently runnable and test-backed slice. It must not promise routes, assets, or operational guarantees that are absent from the shipped runtime and current tests.
