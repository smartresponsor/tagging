# Tagging Wave 10 — Tooling Surface Cleanup

## Scope

Wave 10 canonicalizes remaining generic tooling entrypoints outside `src/`.

This wave does not change the Tagging component namespace. `App\Tagging\...` remains canonical.

## Renames

| Legacy path | Canonical path |
| --- | --- |
| `tools/migration-smoke.ps1` | `tools/tag-migration-smoke.ps1` |
| `tools/migration-smoke.sh` | `tools/tag-migration-smoke.sh` |
| `tools/test-db-start.sh` | `tools/tag-test-db-start.sh` |
| `tools/test-db-stop.sh` | `tools/tag-test-db-stop.sh` |
| `tools/webhook_worker.php` | `tools/tag-webhook-worker.php` |
| `tools/db/migrate.sh` | `tools/db/tag-migrate.sh` |
| `tools/migration/apply-symfony-native-target.php` | `tools/migration/tag-apply-symfony-native-target.php` |
| `tools/slugify/slugify.php` | `tools/slugify/tag-slugify.php` |
| `tools/smoke/smoke.sh` | `tools/smoke/tag-smoke.sh` |
| `tools/synthetic/slo.sh` | `tools/synthetic/tag-slo.sh` |
| `tools/test-db/docker-compose.yml` | `tools/test-db/tag-compose.yaml` |


## Safety

The apply script backs up every deleted legacy path under `.patch-backup/` before removal.
Only explicitly enumerated legacy paths are deleted. No repository-wide cleanup and no cumulative
snapshot overwrite are performed.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-tooling-surface-wave10-audit.php
vendor/bin/phpunit tests/TagToolingSurfaceWave10AuditTest.php
```
