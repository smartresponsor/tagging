# HTTP Wiring v2

Public-ready `host-minimal` wiring for the shipped Tag runtime surface.

## Routed endpoints

- `POST /tag`
- `GET /tag/{id}`
- `PATCH /tag/{id}`
- `DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `POST /tag/assignments/bulk`
- `POST /tag/assignments/bulk-to-entity`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`
- `GET /tag/_status`
- `GET /tag/_surface`

## Route truth

The current route truth is centralized in `tag.yaml` and projected into:

- `host-minimal/route.php`
- `config/tag_public_surface.php`
- `config/tag_runtime.php`
- route/surface/contract audits

## Request headers

- `X-Tenant-Id` required for routed business calls
- `X-Idempotency-Key` optional on write requests

## Current read/write guarantees

- search returns flat payloads without nested `result`
- search returns authoritative `total`
- suggest returns flat payloads without nested `result`
- unassign returns `404 tag_not_found` when the tag entity itself is absent
- bulk assignment routes are part of the shipped public shell

## Still out of scope for the shipped public shell

- synonym or redirect routes
- metrics endpoint
- unpublished internal webhook management routes
- HMAC / RBAC / quota middleware chain in `host-minimal`
