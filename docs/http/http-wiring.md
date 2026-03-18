# HTTP Wiring v1

Public-ready `host-minimal` wiring for the shipped Tag runtime surface.

## Routed endpoints

- `POST /tag`
- `GET /tag/{id}`
- `PATCH /tag/{id}`
- `DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`
- `GET /tag/_status`
- `GET /tag/_surface`

## Request headers

- `X-Tenant-Id` required for routed business calls
- `X-Idempotency-Key` optional on write requests

## Out of scope for the shipped public shell

- bulk assignment routes
- synonym or redirect routes
- metrics endpoint
- HMAC / RBAC / quota middleware chain in `host-minimal`
