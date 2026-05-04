# Tagging Wave 12 — CLI and Bootstrap Tooling Cleanup

## Scope

Wave 12 canonicalizes the remaining generic tooling bootstrap/CLI entrypoints.

This wave does **not** change the component namespace. `App\Tagging\...` remains canonical.

## Renames

| Legacy path | Canonical path |
| --- | --- |
| `tools/_bootstrap.php` | `tools/tag-bootstrap.php` |
| `tools/cli/tag.php` | `tools/cli/tag-cli.php` |

## Reference updates

References in tools, docs, and tests now point to the canonical component-scoped paths.

## Safety

The apply script backs up deleted legacy files under `.patch-backup/`.
Only the two explicitly listed legacy files are deleted. No repository-wide cleanup and no cumulative snapshot are performed.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-cli-bootstrap-wave12-audit.php
vendor/bin/phpunit tests/TagCliBootstrapWave12AuditTest.php
```
