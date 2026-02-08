# Migration policy (Tag component)

This repository contains multiple migration tracks. To prevent drift, this document defines the single source of truth.

## Source of truth

- Authoritative: `db/postgres/migrations/*.sql`
- Non-authoritative (deprecated): `data/migration/**`

Only the authoritative track is guaranteed to be applied in production and CI.

## Ordering

- Files under `db/postgres/migrations` are applied in lexicographic order.
- Migrations must be idempotent (safe to re-run).
- Migrations must be deterministic (no reliance on environment-specific defaults).

## How to run

Local (Docker Compose):

- `make up`
- `make migrate`
- `make smoke`

CI / automation:

- `bash tools/migration-smoke.sh`

## Deprecation plan for secondary tracks

- `data/migration/**` remains readable for historical reference only.
- No new changes should be added to `data/migration/**`.
- When feasible, port content into `db/postgres/migrations/*.sql` and then remove the deprecated track.

