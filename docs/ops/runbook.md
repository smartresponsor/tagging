# Runbook (Tag component)

This runbook covers the shipped public-ready shell for the Tag component.

## Startup checklist

1) Configuration

- `DB_DSN`, `DB_USER`, `DB_PASS`
- `TENANT` (demo default only; production should pass tenant per request)

2) Database

- Ensure Postgres is reachable
- Run migrations:
  - `make migrate`
  - or `php tools/db/tag-migrate.php`
  - or `bash tools/db/tag-migration-smoke.sh`

3) Service health

- Confirm the host is reachable:
  - `GET /tag/_status`
  - `GET /tag/_surface`
- Run smoke:
  - `make smoke`
- Run preflight before publishing:
  - `make audit`
  - `make preflight`

## Degraded modes

- DB unavailable: `_status` stays reachable and reports `db.ok=false`.
- Public shell scope remains CRUD, assign/unassign, assignment read, search, suggest, status, and discovery.

## Incident response

- Check DB health and locks.
- Check migration drift.
- Check outbox and idempotency tables for backlog.
- Re-run `php tools/audit/tag-route-controller-audit.php` if route wiring changed.

## Backup and restore (Postgres)

- Backups must include core tag tables, outbox, and idempotency.
- After restore: re-run migration smoke and runtime smoke.

## Rollback

- Prefer app rollback if schema is forward-compatible.
- Otherwise restore from backup.

## Release validation

- `php tools/audit/tag-surface-audit.php`
- `php tools/audit/tag-contract-audit.php`
- `php tools/audit/tag-route-controller-audit.php`
- `php tools/release/tag-preflight.php`
