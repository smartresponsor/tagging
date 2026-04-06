# Changelog

All notable changes to this repository should be recorded here.

## [Unreleased]

### RC hardening
- added release-facing assets: changelog, release notes, public index, RC checklist, and release workflow
- strengthened OpenAPI contract alignment for tenant headers, error semantics, CRUD statuses, and meta-route response headers
- added release asset and OpenAPI semantics audits so RC-facing documents stay aligned with the shipped runtime

## [0.2.8-rc1] - 2026-04-06

### Public runtime
- shipped CRUD, assign/unassign, bulk assignment routes, assignment reads, search, suggest, `_status`, and `_surface`
- search returns flat payloads with authoritative `total`
- unassign distinguishes missing tag entities from already-absent links

### Truth alignment
- route truth centralized in `tag.yaml`
- public surface, contract, docs, SDK, smoke, CI, repo-map, and readiness docs aligned with the shipped runtime
- legacy `fixtures/tag-demo.json` retired from active demo/seed truth

### Operations
- release preflight, smoke runtime, CI workflow self-audit, error catalog, and ops runbook all present
- release asset lane and prerelease workflow added for RC packaging
