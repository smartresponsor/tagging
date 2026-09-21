# HTTP Wiring v2

Hosted Symfony package wiring for the Tagging public contract.

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

Generic CRUD route grammar and dispatch are owned by the `cruding/crud` package.

- Tagging does not ship a local generic CRUD route file or generic CRUD controller.
- `contracts/http/tag-openapi.yaml` is the Tagging-owned public HTTP contract.
- `config/tag_runtime.php` derives hosted-package surface metadata from the OpenAPI paths.
- `config/routes.yaml` intentionally contains no generic CRUD declarations.

## Request headers

- `X-Tenant-Id` required for routed business calls
- `X-Idempotency-Key` optional on write requests

## Current read/write guarantees

- search returns flat payloads without nested `result`
- search returns authoritative `total`
- suggest returns flat payloads without nested `result`
- unassign returns `404 tag_not_found` when the tag entity itself is absent
- bulk write endpoints are part of the shipped public shell

## Still out of scope for the shipped public shell

- synonym or redirect routes
- metrics endpoint
- unpublished internal webhook management routes
- a component-local generic CRUD router or controller
