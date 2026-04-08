# Quality Atlas assessment surface

This repository exposes a lightweight Quality Atlas assessment surface for repeated owner-side and CI-driven review.

## Purpose

The assessment lane exists to make release posture, documentation wiring, and runnable contract evidence discoverable without introducing a second portal or a separate site assembly layer.

## Entry points

- workflow: `.github/workflows/quality-atlas.yml`
- assessment audit: `tools/audit/tag-assessment-surface-audit.php`
- public publication index: `docs/public/index.md`
- RC checklist: `docs/release/rc-checklist.md`
- release workflow: `.github/workflows/release-rc.yml`
- OpenAPI source contract: `contracts/http/tag-openapi.yaml`
- generated OpenAPI surface: `public/tag/openapi/`

## Assessment contract

The assessment lane should stay thin and evidence-oriented.
It should not redefine runtime truth and should not behave like a central documentation portal.

Expected published evidence includes:

- public docs entrypoint
- RC checklist
- release notes / changelog
- OpenAPI source and generated surfaces
- unit/static-analysis proof logs or artifacts
- assessment metadata bundle

## Discovery order

For external reviewers or automated assessment tooling, the recommended order is:

1. `README.md`
2. `docs/public/index.md`
3. `docs/release/rc-checklist.md`
4. `docs/ops/quality-atlas.md`
5. `contracts/http/tag-openapi.yaml`
6. `public/tag/openapi/`

## Boundary

This surface is repository-local and producer-only.
It does not define a central assessment registry, central UI, or global publishing logic.
