# Release notes template — Tag

Release: `tag-vX.Y.Z`
Date: YYYY-MM-DD
Audience: Platform / Integrators / Ops

## Summary
- What changed, in one paragraph.

## Customer impact
- New capabilities:
- Behavior changes:
- Deprecations (if any):

## Compatibility
- API: OpenAPI `contracts/http/tag-openapi.yaml` version:
- Database migrations: list new migration files:
- SDK changes (if any):

## Upgrade steps
1) Apply migrations:
2) Deploy service:
3) Run smoke:
4) Validate metrics vs SLO:

## Risks and rollback
- Risks:
- Rollback plan:
  - revert deploy to previous image
  - DB rollback strategy (if applicable)

## Observability
- Dashboards:
- Alerts:
- Key SLOs:
  - read p95:
  - write p95:
  - error-rate:
  - redirect resolution latency:

## Changelog
- Added:
- Changed:
- Fixed:
- Removed:
