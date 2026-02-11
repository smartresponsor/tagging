# Tag Data Model (RC5-E2)

- Tables: `tag_entity`, `tag_link`
- Multi-tenant isolation via `(tenant, *)` composite keys.
- ULID stored as `CHAR(26)`.

## Indexes

- trigram GIN on `slug`, `name` (Postgres)
- `tag_link(tenant, entity_type, entity_id)` for entity→tags
- `tag_link(tenant, tag_id)` for tag→entities

Generated: 2025-10-27T20:27:58.493709
