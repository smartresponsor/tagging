# Tagging Wave 8 — Tooling Entrypoint Cleanup

## Scope

Wave 8 canonizes repository tooling entrypoints without changing the component namespace. The Tagging component remains under `App\Tagging\...`.

## Canonicalization

The wave moves generic helper names to explicit Tagging-oriented entrypoints:

- `tools/lint.php` -> `tools/tag-lint.php`
- `tools/git/install-hooks.php` -> `tools/git/tag-install-hooks.php`
- `tools/local/panther-test.sh` -> `tools/local/tag-panther-test.sh`
- `tools/local/php-extension-doctor.sh` -> `tools/local/tag-php-extension-doctor.sh`

It also retires duplicate or legacy tooling surfaces that already have canonical Tagging replacements:

- `tools/db/migrate.php` is superseded by `tools/db/tag-migrate.php`
- `tools/smoke/tag_tag-smoke.sh` is superseded by `tools/smoke/tag-tag-smoke.sh`

## Guard

`tools/audit/tag-tooling-entrypoint-audit.php` checks that legacy entrypoints are absent and Composer scripts reference the canonical Tagging entrypoints.
