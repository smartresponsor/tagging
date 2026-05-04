# Tagging Wave 27 — CI Bridge Runner

## Scope

Wave 27 adds a CI-friendly bridge runner for the post-canonicalization verification surface.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added runner

- `tools/test/tag-ci-bridge-wave27.php`

## Execution chain

The runner executes:

1. `tools/test/tag-canon-status-wave26.php`
2. `tools/test/tag-post-canon-health-wave24.php`
3. `tools/test/tag-post-canon-all-wave22.php`

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up overwritten files.

## Verification

```bash
composer dump-autoload
php tools/test/tag-ci-bridge-wave27.php
vendor/bin/phpunit tests/TagCiBridgeWave27Test.php
```
