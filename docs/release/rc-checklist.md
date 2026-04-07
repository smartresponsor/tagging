# RC checklist

Use this checklist before tagging or publishing an RC candidate.

## Contract and runtime truth

- [ ] `composer run -n audit:surface`
- [ ] `composer run -n audit:contract`
- [ ] `composer run -n audit:openapi-semantics`
- [ ] `composer run -n audit:generated-openapi-surface`
- [ ] `composer run -n audit:antora-surface`
- [ ] OpenAPI reflects current public shell and meta-route headers

## Delivery and release assets

- [ ] `composer run -n docs:openapi:publish`
- [ ] `composer run -n audit:release-assets`
- [ ] `CHANGELOG.md` updated
- [ ] `RELEASE_NOTES.md` updated
- [ ] `docs/public/index.md` points to current runtime docs
- [ ] `docs/ops/runbook.md` reflects current startup/smoke/rollback flow
- [ ] generated Swagger/OpenAPI surface is in sync with source contract
- [ ] Antora producer surface points to the current docs roles and reference surfaces

## Runtime evidence

- [ ] `composer run -n smoke:runtime`
- [ ] `composer run -n test:unit`
- [ ] `composer run -n test:integration`
- [ ] demo seed path works
- [ ] `_status` and `_surface` verified against the shipped runtime
- [ ] tenant isolation evidence passes on shared entity coordinates
- [ ] write symmetry evidence passes for assign / unassign / bulk paths
