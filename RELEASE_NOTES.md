# Tagging 0.2.8-rc1 Release Notes

## Release type

- prerelease / RC candidate
- intended as a release-candidate baseline for the current shipped Tag runtime

## Included public shell

- `POST /tag`
- `GET|PATCH|DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `POST /tag/assignments/bulk`
- `POST /tag/assignments/bulk-to-entity`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`
- `GET /tag/_status`
- `GET /tag/_surface`

## Contract guarantees in this RC

- search returns flat payloads with authoritative `total`
- suggest returns flat payloads without nested `result`
- unassign returns `404 tag_not_found` when the tag entity itself is absent
- meta routes expose version/cache headers in the published contract
- tenant header requirements are documented across the public business shell

## Evidence checklist

Run before calling the candidate acceptable:

```bash
composer run -n docs:openapi:publish
composer run -n release:preflight
composer run -n smoke:runtime
composer run -n audit:release-assets
composer run -n audit:openapi-semantics
composer run -n audit:generated-openapi-surface
composer run -n audit:antora-surface
composer run -n test:unit
composer run -n test:integration
```

Integration evidence now explicitly includes:
- tenant isolation across search and assignment reads on shared entity coordinates
- write symmetry around assign / unassign / bulk flows, including idempotency and missing-tag behavior

## Documentation posture

- Antora producer surface is available under `docs/modules/ROOT/`
- generated Swagger/OpenAPI surface is published under `public/tag/openapi/`
- source contract remains `contracts/http/tag-openapi.yaml`

## Known non-goals for this RC

- no published `/tag/_metrics` route
- no synonym / redirect management in the public shell
- no Kubernetes/Helm deployment bundle in this repository yet
- no Nelmio-managed runtime documentation surface is shipped here
