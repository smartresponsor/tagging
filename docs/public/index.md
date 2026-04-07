# Tagging public index

This is the compact publication entrypoint for the current shipped Tagging component.

## Start here

- public shell checklist: `docs/public/tag-public-ready-checklist.md`
- quick demo: `docs/demo/tag-quick-demo.md`
- final demo pack: `docs/demo/tag-final-demo-pack.md`
- SDK usage: `sdk/README.md`
- ops runbook: `docs/ops/runbook.md`
- API error catalog: `docs/api/error-catalog.md`
- OpenAPI source contract: `contracts/http/tag-openapi.yaml`
- generated Swagger/OpenAPI surface: `public/tag/openapi/`
- RC checklist: `docs/release/rc-checklist.md`

## Current public shell

- CRUD
- assign / unassign
- bulk assignment routes
- assignment reads
- search / suggest
- `_status` / `_surface`

## Current guarantees

- search total is authoritative
- read payloads are flat
- missing tag unassign returns `404 tag_not_found`
- tenant header is part of the public business shell contract

## Publish gate

```bash
composer run -n release:preflight
composer run -n audit:release-assets
composer run -n audit:openapi-semantics
composer run -n audit:generated-openapi-surface
composer run -n smoke:runtime
```
