# Tagging Wave 13 — Admin Asset Naming Cleanup

## Scope

Wave 13 canonicalizes static admin UI asset names.

This wave does **not** change the component namespace. `App\Tagging\...` remains canonical.

## Renames

| Legacy path | Canonical path |
| --- | --- |
| `admin/app.js` | `admin/tag-admin.js` |
| `admin/style.css` | `admin/tag-admin.css` |

`admin/index.html` remains the browser entrypoint because this is the standard static UI filename, but it now references component-scoped assets.

## Safety

The apply script backs up deleted legacy files under `.patch-backup/`.
Only the two explicitly listed legacy files are deleted. No repository-wide cleanup and no cumulative snapshot are performed.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-admin-asset-wave13-audit.php
vendor/bin/phpunit tests/TagAdminAssetWave13AuditTest.php
```
