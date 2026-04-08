# Documentation surface map

This repository is a **documentation producer**.
It is prepared to feed a central Antora-based documentation site, but it does not assemble that site locally.

## Documentation roles

### 1. GitHub-facing repository docs

These stay at repository root and remain optimized for repository visitors, release reading, and quick onboarding:

- `README.md`
- `CHANGELOG.md`
- `RELEASE_NOTES.md`
- `repo-map.md`

### 2. Hand-written narrative docs

These stay under `docs/` and are the main narrative source for the component:

- architecture and ADR material under `docs/architecture/`
- install / operations / release guidance under `docs/ops/`, `docs/release/`, `docs/public/`
- API narratives under `docs/api/` and `docs/http/`
- demos, fixtures, policy, and governance notes under their existing `docs/*` trees

### 3. Antora producer surface

The Antora entry surface lives under:

- `docs/antora.yml`
- `docs/modules/ROOT/nav.adoc`
- `docs/modules/ROOT/pages/`

These files are intentionally thin entry points and wrappers.
They exist so an external central documentation aggregator can discover this repository as an Antora content source without forcing a second copy of the narrative material.

### 4. Generated and reference surfaces

Generated and reference artifacts stay separate from hand-written narrative docs:

- OpenAPI source contract: `contracts/http/tag-openapi.yaml`
- generated static OpenAPI viewer: `public/tag/openapi/`
- SDK reference / usage surface: `sdk/README.md`
- release-frozen reference artifacts: `release/tag-rc5/docs/`

If a generated code-reference surface is added later, it should live under a clearly generated root such as `public/tag/api-reference/` rather than being mixed into the hand-written docs tree.

## Boundary rules

- This repository is a **producer only**.
- It does **not** define an Antora site playbook.
- It does **not** ship central UI or publishing logic.
- It keeps Symfony-oriented repository structure and does not introduce `/src/Domain/` or ports-and-adapters drift.
