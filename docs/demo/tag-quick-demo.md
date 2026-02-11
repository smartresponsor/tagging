# Tag quick demo (curl)

Assumptions:

- server is running on `http://127.0.0.1:8080`
- tenant id header is required (example: `demo`)
- request bodies follow `contracts/http/tag-openapi.yaml`

Set:

- `BASE=http://127.0.0.1:8080`
- `TENANT=demo`

Create a tag:

```bash
curl -sS -X POST "$BASE/tag" \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Id: $TENANT" \
  -d '{"name":"Samsung","locale":"en"}'
```

Assign tag to an entity:

```bash
curl -sS -X POST "$BASE/tag/{tagId}/assign" \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Id: $TENANT" \
  -d '{"entityType":"product","entityId":"p_123"}'
```

Search:

```bash
curl -sS "$BASE/tag/search?q=sam&limit=10" -H "X-Tenant-Id: $TENANT"
```

List entity assignments:

```bash
curl -sS "$BASE/tag/entity/product/p_123" -H "X-Tenant-Id: $TENANT"
```

Merge (redirect):

```bash
curl -sS -X POST "$BASE/tag/{tagId}/merge" \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Id: $TENANT" \
  -d '{"intoId":"{targetTagId}"}'
```

Resolve redirect:

```bash
curl -sS "$BASE/tag/redirect/{fromId}" -H "X-Tenant-Id: $TENANT"
```
