# Tag core boundary

This component is intentionally small. Its value is a reliable tagging core, not a mini-platform where every adjacent asset becomes part of the runtime contract.

## Core runtime

Core runtime means the minimum set of files and behaviors that must stay truthful, runnable, and testable:

- `src/`
- `host-minimal/`
- `config/`
- `contracts/http/`
- `db/postgres/migrations/`
- runtime fixtures and seed data that support demo and validation

Core runtime responsibility:

- tag CRUD
- assign / unassign
- assignment read
- search / suggest
- runtime discovery via `/tag/_surface`
- runtime status via `/tag/_status`
- idempotency and basic policy enforcement inside the tagging flow

## Adjacent assets

Adjacent assets support delivery, demo, operations, or release packaging. They matter, but they must not silently redefine the runtime truth line.

Examples:

- `admin/`
- `docs/`
- `ops/`
- `release/`
- `report/`
- `sdk/`
- `public/`
- non-runtime helper scripts in `tools/`

## Truth order

When assets disagree, use this order:

1. `host-minimal/`
2. `config/`
3. `contracts/http/`
4. tests that verify runtime and public surface
5. docs, SDK, release notes, demos, and reports

## Practical rule

Do not move operational, release, or documentation debt into core runtime just because it lives in the same repository. Keep the component honest: small core first, cargo second.
