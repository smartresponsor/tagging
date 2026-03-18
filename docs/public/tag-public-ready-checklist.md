# Tag public-ready checklist

Runtime shell scope:
- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `GET|PATCH|DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

Gate before publishing:
- `composer run -n lint`
- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:version`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n release:preflight`

Must stay out of the public shell:
- bulk assignment routes
- synonym / redirect management
- metrics endpoints
- admin MVC or hidden router remnants
