# Tag Runtime Ops Runbook

This runbook describes how to start, validate, and recover the shipped Tag runtime (`host-minimal`) in a predictable way.

---

## 1. Preconditions

- PHP 8.2+
- PostgreSQL 15+
- Composer installed

Environment variables (defaults shown):

```
DB_DSN=pgsql:host=127.0.0.1;port=5432;dbname=app
DB_USER=app
DB_PASS=app
TENANT=demo
BASE_URL=http://127.0.0.1:8080
```

---

## 2. Install

```
composer install --no-interaction --prefer-dist
```

---

## 3. Database migrate

```
composer run -n db:migrate
```

Expected:
- all migrations applied
- no SQL errors

---

## 4. Seed demo data (optional but recommended)

```
composer run -n demo:seed
```

Expected:
- deterministic tags and assignments created
- compatible with demo catalog and HTTP examples

---

## 5. Start runtime

```
php -S 127.0.0.1:8080 host-minimal/index.php
```

---

## 6. Basic health check

`GET /tag/_status`

```
curl http://127.0.0.1:8080/tag/_status
```

Expected:
```
HTTP 200
{"ok":true}
```

---

## 7. Surface check (VERY IMPORTANT)

`GET /tag/_surface`

```
curl http://127.0.0.1:8080/tag/_surface
```

Verify presence of:
- `/tag/assignments/bulk`
- `/tag/assignments/bulk-to-entity`
- `/tag/search`
- `/tag/suggest`

This confirms runtime matches expected public shell.

---

## 8. Smoke test

```
composer run -n smoke:runtime
```

Expected:
- exit code 0
- no transport/runtime errors

If failed:
- inspect `/tmp/tag-host.log` (CI)
- or local console output

---

## 9. Quick manual verification

### Search
```
GET /tag/search?q=elect
```

### Assign
```
POST /tag/{id}/assign
```

### Bulk
```
POST /tag/assignments/bulk
POST /tag/assignments/bulk-to-entity
```

### Missing tag contract
```
POST /tag/{missing}/unassign
→ 404 tag_not_found
```

---

## 10. Observability

- slowlog: `report/tag/slowlog.ndjson`
- middleware: `Observe`
- no `/tag/_metrics` endpoint (by design in current slice)

---

## 11. Common failure scenarios

### DB connection fails
- check DSN
- check postgres running

### Empty search results
- run `demo:seed`

### 400 invalid_tenant
- ensure `X-Tenant-Id` header is set

### 404 tag_not_found
- tag does not exist (expected behavior)

---

## 12. Rollback strategy

There is no automated rollback tool in current slice.

Manual approach:

1. reset DB
2. re-run migrations
3. re-run seed

```
dropdb app
createdb app
composer run -n db:migrate
composer run -n demo:seed
```

---

## 13. Evidence checklist (production readiness)

Before considering environment stable:

- `_status` OK
- `_surface` matches expected routes
- smoke passes
- demo flows work
- no unexpected 5xx

---

## 14. Notes

- This runbook reflects the current shipped runtime, not a future Symfony-based host.
- Bulk routes and flat payload semantics are part of the contract.
- Error semantics are defined in `docs/api/error-catalog.md`.
