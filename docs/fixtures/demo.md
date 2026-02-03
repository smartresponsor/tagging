# Demo Fixtures & Seed Kit (RC5-E8)

- `fixtures/tag-demo.json` — теги и начальные связи для tenant=demo.
- `tools/seed/tag-seed.php` — идемпотентный сидер: создаёт теги и ссылки, пропуская существующие.
- `public/tag/demo/requests.http` — smoke-запросы для проверки поиска/подсказок.

Usage:
```bash
TENANT=demo php tools/seed/tag-seed.php
# затем
curl -H "X-Tenant-Id: demo" "http://localhost:8080/tag/search?q=s"
```
Generated: 2025-10-27T20:57:49.353037
