# RC checklist

Use this checklist before tagging or publishing an RC candidate.

## Contract and runtime truth

- [ ] `composer run -n audit:surface`
- [ ] `composer run -n audit:contract`
- [ ] `composer run -n audit:route`
- [ ] `composer run -n audit:openapi-semantics`
- [ ] OpenAPI reflects current public shell and meta-route headers

## Delivery and release assets

- [ ] `composer run -n audit:release-assets`
- [ ] `CHANGELOG.md` updated
- [ ] `RELEASE_NOTES.md` updated
- [ ] `docs/public/index.md` points to current runtime docs
- [ ] `docs/ops/runbook.md` reflects current startup/smoke/rollback flow

## Runtime evidence

- [ ] `composer run -n smoke:runtime`
- [ ] `composer run -n test:unit`
- [ ] `composer run -n test:integration`
- [ ] demo seed path works
- [ ] `_status` and `_surface` verified against the shipped runtime

## Release posture

- [ ] RC tag naming chosen (for example `v0.2.8-rc1`)
- [ ] prerelease workflow ready to package release assets
- [ ] known non-goals / exclusions are stated in release notes
