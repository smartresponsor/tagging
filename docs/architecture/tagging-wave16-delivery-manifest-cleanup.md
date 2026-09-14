# Tagging Wave 16 — Delivery Manifest Naming Cleanup

## Scope

Wave 16 canonicalizes the RC delivery manifest filename.

This wave does **not** change the component namespace. `App\Tagging\...` remains canonical.

## Rename

| Legacy path | Canonical path |
| --- | --- |
| `delivery/rc/manifest.yaml` | `delivery/rc/tag-rc-manifest.yaml` |

Framework-conventional files such as `config/routes.yaml`, `config/services.yaml`, `composer.json`, and static `index.html` entrypoints are intentionally not renamed in this wave.

## Safety

The apply script backs up the deleted legacy file under `.patch-backup/`.
Only the explicitly listed legacy file is deleted. No repository-wide cleanup and no cumulative snapshot are performed.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-delivery-manifest-wave16-audit.php
vendor/bin/phpunit tests/TagDeliveryManifestWave16AuditTest.php
```
