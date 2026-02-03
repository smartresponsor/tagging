# Tag Backend Admin UI (minimal)

- Routes: /admin/tag, /admin/tag/show/{id}, /admin/tag/assign/{id}
- Uses API base URL from env: TAG_BASE_URL (default http://localhost:8080)
- Expects A1 HMAC in front if exposed publicly; for local smoke can be used without.

## Mount in host-minimal
Serve `public/` statics at `/admin/tag/*` and route `admin-router.php` via web server.

Generated: 2025-10-27T20:19:32.653655
