# Tag Admin Shell

Files:
- `admin/index.html`
- `admin/app.js`
- `admin/style.css`

Expected backend surface:
- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `GET /tag/{id}`
- `PATCH /tag/{id}`
- `DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

HTTP contract notes:
- Tenant header: `X-Tenant-Id` or `x-tenant-id`
- Meta/write endpoints send `Cache-Control: no-store`
- Create/get/patch payloads are entity-shaped JSON bodies; create may be consumed as top-level `id`
- Search payload shape: `{ ok, items, total, nextPageToken, cacheHit }`
- Suggest payload shape: `{ ok, items, cacheHit }`
- Assignment payload shape: `{ ok, code?, duplicated?, conflict?, not_found? }`

The shell is intentionally static. It is a demo/discovery helper, not an authoritative admin console.
