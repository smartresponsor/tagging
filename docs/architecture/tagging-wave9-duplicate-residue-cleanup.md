# Tagging Wave 9 — Duplicate Residue Cleanup

## Scope

Wave 9 is a residue-purge wave. It does not change the component namespace and does not migrate
`App\Tagging\...` to plain `App\...`.

The wave removes explicitly retired duplicate files left behind by the previous cleanup sequence:

- legacy write DTO/use-case names without canonical `Tag*` class form;
- duplicate cache/controller/middleware/responder/runtime classes;
- duplicate persistence implementation names;
- duplicate service-depth wrappers under `src/Service/Core/Tag/...`;
- root/tooling residue already replaced by canonical `deploy/docker/**` or `tools/tag-*` entrypoints.

## Canon

`App\Tagging\...` remains the correct namespace for the Tagging component.

## Safety

The apply script backs up every deleted legacy file under `.patch-backup/` before removal.
Only explicitly enumerated legacy paths are deleted. No full repository overwrite and no
repository-wide cleanup is performed.

## Verification

Run:

```bash
composer dump-autoload
php tools/audit/tag-duplicate-residue-audit.php
vendor/bin/phpunit tests/TagDuplicateResidueWave9AuditTest.php
```
