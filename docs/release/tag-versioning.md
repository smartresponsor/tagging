# Versioning policy — Tag

This component uses semantic versioning.

## Public surface
Public compatibility is defined by:
- HTTP contract: `contracts/http/tag-openapi.yaml`
- Database schema via migrations: `db/postgres/migrations/*`
- Event/webhook payloads (if consumed externally)

## Rules
- PATCH (X.Y.Z+1):
  - bug fixes
  - performance improvements with no behavior change
  - internal refactors
- MINOR (X.Y+1.0):
  - new endpoints or optional fields
  - new tables/columns with defaults / backward-compatible reads
  - new webhook event types (opt-in)
- MAJOR (X+1.0.0):
  - breaking API changes (remove/rename)
  - breaking DB changes without compatibility path
  - semantic changes that require client updates

## Deprecation
- Deprecations must be documented in release notes.
- Maintain at least one MINOR cycle before removal unless security requires immediate action.

## Migration discipline
- Migrations are the single source of truth for schema.
- Do not maintain parallel “DDL docs” as authoritative sources.
