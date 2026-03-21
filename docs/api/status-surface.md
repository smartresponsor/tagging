# Status and surface contract

The host-minimal public meta endpoints are:

- `GET /tag/_status`
- `GET /tag/_surface`

Contract hardening rules:

- status advertises runtime and discovery path in payload
- status and surface responses send `Cache-Control: no-store`
- status sends `X-Tag-Version`
- surface sends `X-Tag-Surface-Version`
- Symfony route config must reference current controller classes only
