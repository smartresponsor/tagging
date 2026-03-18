# Tag quick demo

Assumptions:

- server runs on `http://127.0.0.1:8080`
- tenant header is `X-Tenant-Id: demo`
- migrations are already applied
- optional deterministic reset uses `SEED_RESET=1`

Discovery:

```bash
curl -sS 'http://127.0.0.1:8080/tag/_surface'
```

Create:

```bash
curl -sS http://127.0.0.1:8080/tag/_surface -H 'X-Tenant-Id: demo'
```

Create:

```bash
curl -sS 'http://127.0.0.1:8080/tag/_surface' \
  -H 'X-Tenant-Id: demo'
```

Create:

```bash
curl -sS -X POST http://127.0.0.1:8080/tag \
  -H 'Content-Type: application/json' \
  -H 'X-Tenant-Id: demo' \
  -H 'X-Idempotency-Key: demo-create-1' \
  -d '{"name":"Samsung","locale":"en","slug":"samsung"}'
```

Search:

```bash
curl -sS 'http://127.0.0.1:8080/tag/search?q=elect&pageSize=10' -H 'X-Tenant-Id: demo'
```

Suggest:

```bash
curl -sS 'http://127.0.0.1:8080/tag/suggest?q=pre&limit=5' -H 'X-Tenant-Id: demo'
```

List entity assignments:

```bash
curl -sS 'http://127.0.0.1:8080/tag/assignments?entityType=product&entityId=demo-product-1' -H 'X-Tenant-Id: demo'
```

Status:

```bash
curl -sS 'http://127.0.0.1:8080/tag/_status'
```
