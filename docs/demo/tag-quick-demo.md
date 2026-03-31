# Tag quick demo

This quick demo is the shortest truthful runnable flow for the current core.

Assumptions:

- server runs on `http://127.0.0.1:8080`
- tenant header is `X-Tenant-Id: demo`
- migrations are already applied
- deterministic seed uses `SEED_RESET=1`

## 1. Validate and seed

```bash
php tools/seed/tag-fixture-validate.php
SEED_RESET=1 TENANT=demo php tools/seed/tag-seed.php
```

## 2. Create

```bash
curl -sS -X POST 'http://127.0.0.1:8080/tag' \
  -H 'Content-Type: application/json' \
  -H 'X-Tenant-Id: demo' \
  -H 'X-Idempotency-Key: demo-create-1' \
  -d '{"name":"Samsung","locale":"en","slug":"samsung"}'
```

## 3. Discovery

```bash
curl -sS 'http://127.0.0.1:8080/tag/_surface'
```

## 4. Status

```bash
curl -sS 'http://127.0.0.1:8080/tag/_status'
```

## 5. Search

```bash
curl -sS 'http://127.0.0.1:8080/tag/search?q=elect&pageSize=10' \
  -H 'X-Tenant-Id: demo'
```

## 6. Suggest

```bash
curl -sS 'http://127.0.0.1:8080/tag/suggest?q=pre&limit=5' \
  -H 'X-Tenant-Id: demo'
```

## 7. Mixed bulk assignments

```bash
curl -sS -X POST 'http://127.0.0.1:8080/tag/assignments/bulk' \
  -H 'Content-Type: application/json' \
  -H 'X-Tenant-Id: demo' \
  -d '{"operations":[{"op":"assign","tagId":"01HTESTASSIGN00000000000000","entityType":"product","entityId":"demo-product-1","idem":"bulk-1"},{"op":"unassign","tagId":"01HTESTASSIGN00000000000000","entityType":"product","entityId":"demo-product-2","idem":"bulk-2"}]}'
```

## 8. Bulk assign many tags to one entity

```bash
curl -sS -X POST 'http://127.0.0.1:8080/tag/assignments/bulk-to-entity' \
  -H 'Content-Type: application/json' \
  -H 'X-Tenant-Id: demo' \
  -d '{"entityType":"product","entityId":"demo-product-1","tagIds":["01HTESTASSIGN00000000000000","01HTESTASSIGN00000000000001"]}'
```

## 9. Missing-tag unassign contract

```bash
curl -sS -X POST 'http://127.0.0.1:8080/tag/01HMISSINGTAG0000000000000/unassign' \
  -H 'Content-Type: application/json' \
  -H 'X-Tenant-Id: demo' \
  -d '{"entityType":"product","entityId":"demo-product-1"}'
```

Expect an HTTP `404` payload with `code=tag_not_found` when the tag entity itself does not exist.

## 10. List entity assignments

```bash
curl -sS 'http://127.0.0.1:8080/tag/assignments?entityType=product&entityId=demo-product-1' \
  -H 'X-Tenant-Id: demo'
```

For the final compact pack, also see `docs/demo/tag-final-demo-pack.md`.
