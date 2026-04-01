# Tag public-ready checklist

Runtime shell scope:
- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `GET|PATCH|DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `POST /tag/assignments/bulk`
- `POST /tag/assignments/bulk-to-entity`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

Public read guarantees:
- search returns flat payloads without nested `result`
- search returns authoritative `total`
- suggest returns flat payloads without nested `result`
- unassign returns `404 tag_not_found` when the tag entity itself is absent

Gate before publishing:
- `composer run -n lint`
- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:version`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n smoke:runtime`
- `composer run -n release:preflight`

Must stay out of the public shell:
- synonym / redirect management
- metrics endpoints
- admin MVC or hidden router remnants
- unpublished internal webhook management routes
