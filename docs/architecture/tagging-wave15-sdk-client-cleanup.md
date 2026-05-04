# Tagging Wave 15 — SDK Client Naming Cleanup

## Scope

Wave 15 canonicalizes published SDK client filenames and class names.

This wave does **not** change the component namespace. `App\Tagging\...` remains canonical.

## Renames

| Legacy path | Canonical path |
| --- | --- |
| `sdk/php/tag/Client.php` | `sdk/php/tag/TagClient.php` |
| `sdk/ts/tag/client.ts` | `sdk/ts/tag/tag-client.ts` |

The PHP SDK client now declares `TagClient`. The TypeScript SDK client now exposes `TagClient`.

## Safety

The apply script backs up deleted legacy files under `.patch-backup/`.
Only the two explicitly listed legacy files are deleted. No repository-wide cleanup and no cumulative snapshot are performed.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-sdk-client-wave15-audit.php
vendor/bin/phpunit tests/TagSdkClientWave15AuditTest.php
```
