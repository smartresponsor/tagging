# Tagging Wave 17 — Conventional Artifact Boundary

## Scope

Wave 17 does not rename source, namespace, or framework entrypoints. It documents and enforces the boundary between:

- framework/static convention files that should stay generic;
- generic residue that has already been retired by Waves 8–16 and must not return.

`App\Tagging\...` remains canonical.

## Allowed convention files

These files intentionally keep framework/static names:

- `admin/index.html`
- `public/tag/openapi/index.html`
- `migration/symfony-native-target/composer.json`
- `migration/symfony-native-target/public/index.php`
- `docs/README.md`
- `docs/admin/README.md`
- `sdk/README.md`
- Symfony configuration files under `config/` and `config/component/`

## Forbidden generic residue

The audit prevents return of retired artifacts such as:

- `admin/app.js`
- `admin/style.css`
- `delivery/rc/manifest.yaml`
- `public/tag/**/requests.http`, `http.http`, `seed.http`, `tour.http`
- `sdk/php/tag/Client.php`
- `sdk/ts/tag/client.ts`
- generic `tools/*` entrypoints retired in Waves 8, 10, and 12

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up any overwritten files.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-conventional-artifact-wave17-audit.php
vendor/bin/phpunit tests/TagConventionalArtifactWave17AuditTest.php
```
