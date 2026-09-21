# Purge hooks (Tag)

## Internal capability

`/tag/_purge` is not part of the shipped public HTTP contract. Purge behavior may be invoked by trusted host/application code and must not be exposed as a public Tagging route without an explicit contract change.

- Tenant context is required for `action=tenant`
- Body (JSON):
    - `{"action":"tenant"}` → purge caches for current tenant
    - `{"action":"tag_ids","tag_ids":["123","124"]}` → purge specific tags
    - `{"action":"all"}` → purge all caches

## Config

- `config/tag_purge.yaml`: roots and safety list of allowed directories.

## Hosted composition

The Symfony host may compose the purge service for trusted operational workflows. The host owns authorization and any external transport surface.

## Safety

- Service deletes only inside `allowed_roots`.
- Default is **idempotent** and safe: deletes files only, not directories.
- `policy.dry_run=true` for audit-only mode.
