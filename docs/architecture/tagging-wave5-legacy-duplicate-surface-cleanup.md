# Tagging Wave 5 — legacy duplicate surface cleanup

Wave 5 removes the leftover duplicate class/file surface after the earlier class-form and service-depth waves.

The component namespace remains `App\Tagging\...`. This wave must not collapse Tagging into plain `App\...`.

## Scope

- Remove generic duplicate files once canonical `Tag*` files exist.
- Keep canonical Symfony-oriented layer folders.
- Keep service classes under `src/Service/Core` without the extra `Core/Tag` wrapper for canonical files.
- Add an executable audit that fails when duplicate legacy files reappear.

## Non-goals

- No cumulative snapshot.
- No repository-wide delete.
- No namespace migration.
- No runtime proof or full PHPUnit proof in this wave.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-legacy-duplicate-surface-audit.php
vendor/bin/phpunit tests/TagLegacyDuplicateSurfaceWave5AuditTest.php
```
