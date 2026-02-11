# Runbook (Tag component)

This runbook covers the operational basics for the Tag component host(s).

## Startup checklist

1) Configuration

- `DB_DSN`, `DB_USER`, `DB_PASS`
- `TENANT` (demo default only; production must pass tenant per request)

1) Database

- Ensure Postgres is reachable
- Run migrations:
    - `make migrate` (Docker Compose)
    - or `bash tools/migration-smoke.sh --no-start` (external DB)

1) Service health

- Confirm the host is reachable:
    - `GET /status`
    - `GET /metrics` (if enabled)
- Run smoke:
    - `make smoke`

## Degraded modes

- DB unavailable: fail fast with deterministic error codes.
- Read-only: allow search/suggest if write paths are degraded (if configured).

## Incident response

- Check DB health and locks.
- Check migration drift.
- Check outbox/idempotency tables for backlog.

## Backup and restore (Postgres)

- Backups must include core tag tables, outbox, and idempotency.
- After restore: run migration smoke and smoke tests.

## Rollback

- Prefer app rollback if schema is forward-compatible.
- Otherwise restore from backup.

## Release validation
