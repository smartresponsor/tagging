# Tagging Wave 4 — Legacy facade retirement and surface deduplication

## Namespace rule

The component namespace remains `App\Tagging\...`. This wave does not migrate Tagging to the plain Symfony `App\...` namespace.

## Purpose

Wave 4 removes post-renaming compatibility facades and duplicate HTTP/service surfaces that remained after the class-form and service-depth cleanup waves. The goal is to keep the repository readable from the filesystem without ambiguous duplicate classes that represent the same responsibility.

## Retired legacy files

- `src/Http/Api/Tag/MetricsController.php` — replaced by `src/Http/Api/Tag/TagMetricsController.php`.
- `src/Service/Authz/TagAuthorizer.php` — canonical implementation lives in `src/Service/Core/Authz/TagAuthorizer.php`.
- `src/Service/Slug/Tag/TagSlugPolicy.php` — canonical implementation lives in `src/Service/Core/Slug/TagSlugPolicy.php`.
- `src/Service/Slug/Tag/TagSlugifier.php` — canonical implementation lives in `src/Service/Core/Slug/TagSlugifier.php`.

## Canonical targets

- HTTP controllers must expose `Tag*Controller` class/file names.
- Core service implementations belong under `src/Service/Core/...` with `Tag` component prefix on the class/file name.
- Temporary compatibility facades are not kept before end-user compatibility requirements exist.

## Validation

Run:

```bash
php tools/audit/tag-legacy-facade-audit.php
vendor/bin/phpunit tests/TagLegacyFacadeWave4AuditTest.php
```
