# Tagging Wave 14 — Public HTTP Example Naming Cleanup

## Scope

Wave 14 canonicalizes public demo/example HTTP artifact names under `public/tag/**`.

This wave does **not** change the component namespace. `App\Tagging\...` remains canonical.

## Renames

| Legacy path | Canonical path |
| --- | --- |
| `public/tag/demo/requests.http` | `public/tag/demo/tag-demo-requests.http` |
| `public/tag/examples/http.http` | `public/tag/examples/tag-http-examples.http` |
| `public/tag/examples/seed.http` | `public/tag/examples/tag-seed-examples.http` |
| `public/tag/examples/tour.http` | `public/tag/examples/tag-tour-examples.http` |

## Safety

The apply script backs up deleted legacy files under `.patch-backup/`.
Only the four explicitly listed legacy files are deleted. No repository-wide cleanup and no cumulative snapshot are performed.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-public-example-wave14-audit.php
vendor/bin/phpunit tests/TagPublicExampleWave14AuditTest.php
```
