# HTTP Wiring vNext

Symfony-native wiring target for the Tag runtime surface.

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

The route truth remains centralized in `tag.yaml`, but runtime assembly is migrated away from `host-minimal` dispatch toward Symfony-native controller and service wiring.

Derived artifacts may continue to exist only when they project the Symfony-native runtime truth rather than competing with it.

## Request headers

- `X-Tenant-Id` required for routed business calls
- `X-Idempotency-Key` optional on write requests

## Current read/write guarantees

- search returns flat payloads without nested `result`
- search returns authoritative `total`
- suggest returns flat payloads without nested `result`
- unassign returns `404 tag_not_found` when the tag entity itself is absent
- bulk write endpoints remain part of the shipped public shell

## Runtime direction

- Symfony-native runtime path is the intended primary direction
- `host-minimal` is treated as transitional migration debt or historical context, not as the strategic runtime model

## Still out of scope until later hardening

- synonym or redirect routes
- metrics endpoint
- unpublished internal webhook management routes
- advanced middleware hardening beyond the baseline migration
