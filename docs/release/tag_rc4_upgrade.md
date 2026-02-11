# Tag RC4 Upgrade Guide

## Scope

- E27 Assignment matrix v2
- E28 Synonyms & Redirects
- E29 Search & ETag
- E30 Tenancy & Quotas
- E31 Admin UI v4

## API Changes

- **Additive** vs RC3. New endpoints:
    - POST /tag/{id}/assign
    - POST /tag/{id}/unassign
    - POST /tag/assign-bulk
    - GET /tag/assignments
    - GET/POST/DELETE /tag/{id}/synonym
    - GET /tag/redirect/{fromId}
    - GET /tag/search
    - GET /entity/search

## DB Migrations (PostgreSQL)

Apply in order:

- `data/migration/V27__tag_assignment.sql`
- `data/migration/V28__tag_synonym_redirect.sql`
- `data/index/V29__tag_search.sql`
- `data/migration/V30__tenant_tag.sql`

## Config

- `config/tag_assignment.yaml`
- `config/tag_search.yaml`
- `config/tag_tenant.yaml`

## Backward Compatibility

- No breaking changes vs RC3.
- Tenancy is disabled if `config/tag_tenant.yaml: enforce=false` (set as needed).

## Next

- Run 48h canary with SLO gate (read p95 ≤ 250ms, write p95 ≤ 700ms, error ≤ 0.5%).

Generated: 2025-10-27T19:38:24.844157
